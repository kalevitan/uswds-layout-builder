<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsTaglineBlock;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "uswds_tagline_block",
 *   admin_label = @Translation("Tagline block"),
 * )
 */
class UswdsTaglineBlock extends BlockBase {

  protected const DEFAULT_FORMAT = 'full_html';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'uswds_tagline_heading' => '',
      'uswds_tagline_body' => [
        'value' => NULL,
        'format' => static::DEFAULT_FORMAT,
      ],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $config = $this->getConfiguration();

    if (isset($config['uswds_tagline_heading'])
      && !empty($config['uswds_tagline_heading'])) {
      $build['#uswds_tagline_heading'] = $config['uswds_tagline_heading'];
    }

    if (isset($config['uswds_tagline_body'])
      && !empty($config['uswds_tagline_body'])) {
      $build['#uswds_tagline_body'] = [
        '#type' => 'processed_text',
        '#format' => $config['uswds_tagline_body']['format'] ?? static::DEFAULT_FORMAT,
        '#text' => $config['uswds_tagline_body']['value'] ?? NULL,
      ];
    }

    $build['#theme'] = 'uswds_tagline_block';

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

    $form['uswds_tagline_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#description' => $this->t('The tagline heading.'),
      '#default_value' => $config['uswds_tagline_heading'] ?? '',
      '#maxlength' => 212,
      '#size' => 212,
      '#weight' => 1,
    ];

    $form['uswds_tagline_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#description' => $this->t('The tagline body.'),
      '#default_value' => $config['uswds_tagline_body']['value'] ?? '',
      '#format' => $config['uswds_tagline_body']['format'] ?? static::DEFAULT_FORMAT,
      '#rows' => '3',
      '#weight' => 2,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['uswds_tagline_heading'] = $values['uswds_tagline_heading'];
    $this->configuration['uswds_tagline_body'] = $values['uswds_tagline_body'];
  }

}
