<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsAccordionBlock;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "uswds_accordion_block",
 *   admin_label = @Translation("Accordion block"),
 * )
 */
class UswdsAccordionBlock extends BlockBase {

  protected const DEFAULT_FORMAT = 'full_html';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'uswds_accordion_heading' => '',
      'uswds_accordion_body' => [
        'value' => NULL,
        'format' => static::DEFAULT_FORMAT,
      ],
      'uswds_accordion_expanded' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $config = $this->getConfiguration();

    if (isset($config['uswds_accordion_heading'])
      && !empty($config['uswds_accordion_heading'])) {
      $build['#uswds_accordion_heading'] = $config['uswds_accordion_heading'];
    }

    if (isset($config['uswds_accordion_body'])
      && !empty($config['uswds_accordion_body'])) {
      $build['#uswds_accordion_body']['#markup'] = $config['uswds_accordion_body']['value'];
    }

    if (isset($config['uswds_accordion_expanded'])
      && !empty($config['uswds_accordion_expanded'])) {
      $build['#uswds_accordion_expanded'] = $config['uswds_accordion_expanded'];
    }

    $build['#theme'] = 'uswds_accordion_block';

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

    $form['uswds_accordion_heading'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#description' => $this->t('The accordion heading.'),
      '#default_value' => $config['uswds_accordion_heading'] ?? '',
      '#maxlength' => 212,
      '#size' => 212,
      '#weight' => 1,
    ];

    $form['uswds_accordion_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#description' => $this->t('The accordion body.'),
      '#default_value' => $config['uswds_accordion_body']['value'] ?? '',
      '#format' => $config['uswds_accordion_body']['format'] ?? static::DEFAULT_FORMAT,
      '#rows' => '5',
      '#weight' => 2,
    ];

    $form['uswds_accordion_expanded'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Render Expanded'),
      '#description' => $this->t('Expand the accordion by default.'),
      '#default_value' => $config['uswds_accordion_expanded'] ?? '',
      '#weight' => 3,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['uswds_accordion_heading'] = $values['uswds_accordion_heading'];
    $this->configuration['uswds_accordion_body'] = $values['uswds_accordion_body'];
    $this->configuration['uswds_accordion_expanded'] = $values['uswds_accordion_expanded'];
  }

}
