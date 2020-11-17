<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsSectionHeadingBlock;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a block with a simple text.
 *
 * @Block(
 *   id = "uswds_section_heading_block",
 *   admin_label = @Translation("Section heading block"),
 * )
 */
class UswdsSectionHeadingBlock extends BlockBase {

  protected const DEFAULT_FORMAT = 'full_html';

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'label_display' => FALSE,
      'uswds_section_heading_title' => '',
      'uswds_section_heading_body' => [
        'value' => NULL,
        'format' => static::DEFAULT_FORMAT,
      ],
      'uswds_section_heading_cta_title' => '',
      'uswds_section_heading_cta_url' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $config = $this->getConfiguration();

    if (isset($config['uswds_section_heading_title'])
      && !empty($config['uswds_section_heading_title'])) {
      $build['#uswds_section_heading_title'] = $config['uswds_section_heading_title'];
    }

    if (isset($config['uswds_section_heading_body'])
      && !empty($config['uswds_section_heading_body'])) {
      $processed_value = preg_replace('/<p>/', '<p class="usa-intro">', $config['uswds_section_heading_body']['value']);
      $build['#uswds_section_heading_body'] = [
        '#markup' => $processed_value,
      ];
    }

    if (isset($config['uswds_section_heading_cta_title'])
      && !empty($config['uswds_section_heading_cta_title'])) {
      $build['#uswds_section_heading_cta_title'] = $config['uswds_section_heading_cta_title'];
    }

    if (isset($config['uswds_section_heading_cta_url'])
      && !empty($config['uswds_section_heading_cta_url'])) {
      $build['#uswds_section_heading_cta_url'] = $config['uswds_section_heading_cta_url'];
    }

    $build['#theme'] = 'uswds_section_heading_block';

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

    $form['uswds_section_heading_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Heading'),
      '#description' => $this->t('The section heading.'),
      '#default_value' => $config['uswds_section_heading_title'] ?? '',
      '#maxlength' => 212,
      '#size' => 212,
      '#weight' => 1,
    ];

    $form['uswds_section_heading_body'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Body'),
      '#description' => $this->t('The section body.'),
      '#default_value' => $config['uswds_section_heading_body']['value'] ?? '',
      '#format' => $config['uswds_section_heading_body']['format'] ?? static::DEFAULT_FORMAT,
      '#rows' => '5',
      '#weight' => 2,
    ];

    $form['uswds_section_heading_cta_group'] = [
      '#type' => 'details',
      '#title' => $this->t('Call to action link'),
      '#description' => $this->t('The hero CTA link.'),
      '#open' => TRUE,
      '#weight' => 3,
    ];

    $form['uswds_section_heading_cta_group']['uswds_section_heading_cta_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link Title'),
      '#default_value' => $config['uswds_section_heading_cta_title'] ?? '',
      '#description' => $this->t("Link title."),
      '#maxlength' => 255,
    ];

    $form['uswds_section_heading_cta_group']['uswds_section_heading_cta_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => $config['uswds_section_heading_cta_url'] ?? '',
      '#description' => $this->t("Link URL."),
      '#maxlength' => 2048,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['uswds_section_heading_title'] = $values['uswds_section_heading_title'];
    $this->configuration['uswds_section_heading_body'] = $values['uswds_section_heading_body'];
    $this->configuration['uswds_section_heading_cta_title'] = $values['uswds_section_heading_cta_group']['uswds_section_heading_cta_title'];
    $this->configuration['uswds_section_heading_cta_url'] = $values['uswds_section_heading_cta_group']['uswds_section_heading_cta_url'];
  }

}
