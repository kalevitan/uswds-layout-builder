<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsHeroBlock;

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
 *   id = "uswds_hero_block",
 *   admin_label = @Translation("Hero block"),
 * )
 */
class UswdsHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

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
      'uswds_hero_heading' => '',
      'uswds_hero_body' => [
        'value' => NULL,
        'format' => static::DEFAULT_FORMAT,
      ],
      'uswds_hero_cta_title' => '',
      'uswds_hero_cta_url' => '',
      'uswds_hero_image' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $config = $this->getConfiguration();

    if (isset($config['uswds_hero_heading'])
      && !empty($config['uswds_hero_heading'])) {
      $build['#uswds_hero_heading'] = $config['uswds_hero_heading'];
    }

    if (isset($config['uswds_hero_body'])
      && !empty($config['uswds_hero_body'])) {
      $build['#uswds_hero_body']['#markup'] = $config['uswds_hero_body']['value'];
    }

    if (isset($config['uswds_hero_cta_title'])
      && !empty($config['uswds_hero_cta_title'])) {
      $build['#uswds_hero_cta_title'] = $config['uswds_hero_cta_title'];
    }

    if (isset($config['uswds_hero_cta_url'])
      && !empty($config['uswds_hero_cta_url'])) {
      $build['#uswds_hero_cta_url'] = $config['uswds_hero_cta_url'];
    }

    if (isset($config['uswds_hero_image'])
      && !empty($config['uswds_hero_image'])) {
      $fid = implode($config['uswds_hero_image']);
      if (!empty($fid)) {
        $file = $this->entityTypeManager->getStorage('file')->load($fid);
        if ($file instanceof FileInterface) {
          $file->setPermanent();
          $file->save();
          $this->fileUsage->add($file, 'uswds_layout_builder', 'file', $fid);

          $build['#uswds_hero_image'] = [
            '#theme' => 'image',
            '#uri' => $file->getFileUri(),
          ];
        }
      }
    }

    $build['#theme'] = 'uswds_hero_block';

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

    $form['uswds_hero_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#description' => $this->t('The hero heading text.'),
      '#default_value' => $config['uswds_hero_heading'] ?? '',
      '#maxlength' => 64,
      '#size' => 64,
      '#weight' => 1,
    ];

    $form['uswds_hero_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#description' => $this->t('The hero body.'),
      '#default_value' => $config['uswds_hero_body']['value'] ?? '',
      '#format' => $config['uswds_hero_body']['format'] ?? static::DEFAULT_FORMAT,
      '#rows' => '5',
      '#weight' => 2,
    ];

    $form['uswds_hero_cta_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Call to action link'),
      '#description' => $this->t('The hero CTA link.'),
      '#open' => TRUE,
      '#weight' => 3,
    ];

    $form['uswds_hero_cta_group']['uswds_hero_cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Title'),
      '#default_value' => $config['uswds_hero_cta_title'] ?? '',
      '#description' => $this->t("Link title."),
      '#maxlength' => 255,
    ];

    $form['uswds_hero_cta_group']['uswds_hero_cta_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config['uswds_hero_cta_url'] ?? '',
      '#description' => $this->t("Link URL."),
      '#maxlength' => 2048,
    ];

    $form['uswds_hero_image'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Image'),
      '#description' => $this->t('The hero image'),
      '#default_value' => $config['uswds_hero_image'] ?? '',
      '#upload_location' => 'public://uswds-layout-builder',
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_size' => [25600000],
      ],
      '#theme' => 'image_widget',
      '#preview_image_style' => 'thumbnail',
      '#weight' => 4,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['uswds_hero_heading'] = $values['uswds_hero_heading'];
    $this->configuration['uswds_hero_body'] = $values['uswds_hero_body'];
    $this->configuration['uswds_hero_cta_title'] = $values['uswds_hero_cta_group']['uswds_hero_cta_title'];
    $this->configuration['uswds_hero_cta_url'] = $values['uswds_hero_cta_group']['uswds_hero_cta_url'];
    $this->configuration['uswds_hero_image'] = $values['uswds_hero_image'];
  }

}
