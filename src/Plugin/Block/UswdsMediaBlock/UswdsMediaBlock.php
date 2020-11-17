<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsMediaBlock;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "uswds_media_block",
 *   admin_label = @Translation("Media block"),
 * )
 */
class UswdsMediaBlock extends BlockBase implements ContainerFactoryPluginInterface {

  protected const DEFAULT_FORMAT = 'full_html';

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  private $entityTypeManager;

  /**
   * The file usage service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  private $fileUsage;

  /**
   * UswdsMediaBlock constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\file\FileUsage\FileUsageInterface $fileUsage
   *   The file usage service.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entityTypeManager,
    FileUsageInterface $fileUsage
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entityTypeManager;
    $this->fileUsage = $fileUsage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'uswds_media' => [
        'uswds_media_variant' => '',
        'uswds_media_content' => [],
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $mediaBlocks = [];

    $config = $this->getConfiguration();

    $media = $config['uswds_media']['uswds_media_content'];

    if (isset($media)) {
      for ($i = 0; $i < count($media); $i++) {
        $mediaBlocks[$i] = $config['uswds_media']['uswds_media_content'][$i];

        if (isset($media[$i]['uswds_media_body'])
          && !empty($media[$i]['uswds_media_body'])) {
          $mediaBlocks[$i]['uswds_media_body'] = [
            '#type' => 'processed_text',
            '#text' => $media[$i]['uswds_media_body']['value'],
            '#format' => $media[$i]['uswds_media_body']['format'],
          ];
        }

        if (isset($media[$i]['uswds_media_image'])
          && !empty($media[$i]['uswds_media_image'])) {
          $fid = implode($media[$i]['uswds_media_image']);
          if (!empty($fid)) {
            $file = $this->entityTypeManager->getStorage('file')->load($fid);
            if ($file instanceof FileInterface) {
              $file->setPermanent();
              $file->save();
              $this->fileUsage->add($file, 'uswds_layout_builder', 'file', $fid);

              $original_file = $file->getFileUri();
              $style = $style = $this->entityTypeManager->getStorage('image_style')->load('uswds_graphic');
              $mediaBlocks[$i]['uswds_media_image'] = [
                '#theme' => 'image',
                '#style_name' => 'thumbnail',
                '#uri' => $style->buildUrl($original_file),
              ];
            }
          }
        }
      }
    }

    $build['#theme'] = 'uswds_media_block';
    $build['#media'] = $mediaBlocks;
    $build['#variant'] = $config['uswds_media']['uswds_media_variant'];

    return $build;

  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    return AccessResult::allowedIfHasPermission($account, 'access content');
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    // Create media field group component.
    $form['#prefix'] = '<div class="uswds-media--component">';
    $form['#tree'] = TRUE;

    $form['items_fieldset'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Media Blocks'),
      '#prefix' => '<div id="items-fieldset-wrapper">',
      '#suffix' => '</div>',
      '#weight' => 0,
    ];

    $form['uswds_media_variant'] = [
      '#type' => 'select',
      '#title' => $this->t('Background color'),
      '#description' => $this->t('Set background color variant.'),
      '#default_value' => $config['uswds_media']['uswds_media_variant'] ?? '',
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
      ],
      '#weight' => 1,
    ];

    if (!$form_state->has('items')) {
      $form_state->set('items', $config['uswds_media']['uswds_media_content']);
    }

    // Retrieve media blocks from state.
    $mediaBlocks = $form_state->get('items');
    $numBlocks = count($mediaBlocks);

    for ($i = 0; $i < $numBlocks; $i++) {
      $default_media_heading = 'Media Block ' . ($i + 1);

      $form['items_fieldset']['items'][$i]['uswds_media_heading'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Media block @index heading', ['@index' => $i + 1]),
        '#default_value' => $mediaBlocks[$i]['uswds_media_heading'] ?? $default_media_heading,
        '#maxlength' => 64,
        '#size' => 64,
        '#weight' => 2,
      ];

      $form['items_fieldset']['items'][$i]['uswds_media_body'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Body'),
        '#description' => $this->t('The media body.'),
        '#default_value' => $mediaBlocks[$i]['uswds_media_body']['value'] ?? '',
        '#format' => $mediaBlocks[$i]['uswds_media_body']['format'] ?? static::DEFAULT_FORMAT,
        '#rows' => '5',
        '#weight' => 3,
      ];

      $form['items_fieldset']['items'][$i]['uswds_media_image'] = [
        '#type' => 'managed_file',
        '#title' => $this->t('Image'),
        '#description' => $this->t('The media graphic'),
        '#default_value' => $mediaBlocks[$i]['uswds_media_image'] ?? '',
        '#upload_location' => 'public://uswds-layout-builder',
        '#upload_validators' => [
          'file_validate_extensions' => ['gif png jpg jpeg'],
          'file_validate_size' => [5120000],
        ],
        '#theme' => 'image_widget',
        '#preview_image_style' => 'thumbnail',
        '#weight' => 4,
      ];
    }

    // Remove last media block button.
    if ($numBlocks >= 1) {
      $form['items_fieldset']['items']['remove_item'] = [
        '#type' => 'submit',
        '#value' => $this->t('Remove last item'),
        '#submit' => [[$this, 'removeLastBlock']],
        '#ajax' => [
          'callback' => [$this, 'removeLastBlockCallback'],
          'wrapper' => 'items-fieldset-wrapper',
        ],
        '#weight' => 1,
      ];
    }

    $form['items_fieldset']['actions'] = [
      '#type' => 'actions',
    ];

    // Add media block button.
    $form['add_media_block'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add media block'),
      '#submit' => [[$this, 'addBlock']],
      '#ajax' => [
        'callback' => [$this, 'addBlockCallback'],
        'wrapper' => 'items-fieldset-wrapper',
      ],
      '#weight' => 2,
      '#prefix' => '<div class="uswds-media-buttons">',
      '#suffix' => '</div>',
    ];

    $form['#suffix'] = '</div>';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    // Set the media section background variant.
    $this->configuration['uswds_media']['uswds_media_variant'] = $form_state->getValue('uswds_media_variant');

    // Set to empty before save all again.
    $this->configuration['uswds_media']['uswds_media_content'] = [];

    // Set media block groups.
    foreach ($form_state->getValues() as $key => $value) {
      if ($key === 'items_fieldset') {
        if (isset($value['items'])) {
          $items = $value['items'];
          $blocks = $form_state->get('items');
          if (is_array($blocks)) {
            for ($i = 0; $i < count($blocks); $i++) {
              $this->configuration['uswds_media']['uswds_media_content'][$i]['uswds_media_heading'] = $items[$i]['uswds_media_heading'];
              $this->configuration['uswds_media']['uswds_media_content'][$i]['uswds_media_body'] = $items[$i]['uswds_media_body'];
              $this->configuration['uswds_media']['uswds_media_content'][$i]['uswds_media_image'] = $items[$i]['uswds_media_image'];
            }
          }
        }
      }
    }
  }

  /**
   * Remove last block.
   *
   * @param array $form
   *   The array form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface.
   */
  public function removeLastBlock(array &$form, FormStateInterface $form_state) {
    $items = [];
    if ($form_state->has('items')) {
      $items = $form_state->get('items');
      array_pop($items);
    }

    $form_state->set('items', $items);
    $form_state->setRebuild();
  }

  /**
   * Remove last block (callback).
   *
   * @param array $form
   *   The array form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface.
   *
   * @return mixed
   *   Return the form.
   */
  public function removeLastBlockCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['items_fieldset'];
  }

  /**
   * Add block.
   *
   * @param array $form
   *   The array form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface.
   */
  public function addBlock(array &$form, FormStateInterface $form_state) {
    $items = [];
    if ($form_state->has('items')) {
      $items = $form_state->get('items');
      $nextItem = count($items);
      $items[$nextItem]['type'] = 'block';
    }
    else {
      $nextItem = count($items);
      $items[$nextItem];
    }

    $form_state->set('items', $items);
    $form_state->setRebuild();
  }

  /**
   * Add block (callback).
   *
   * @param array $form
   *   The array form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The FormStateInterface.
   *
   * @return mixed
   *   Return the form.
   */
  public function addBlockCallback(array &$form, FormStateInterface $form_state) {
    return $form['settings']['items_fieldset'];
  }

}
