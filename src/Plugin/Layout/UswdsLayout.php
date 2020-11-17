<?php

namespace Drupal\uswds_layout_builder\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Class UswdsLayout controls layout forms.
 *
 * @package Drupal\uswds_layout_builder\Plugin\Layout
 */
class UswdsLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return parent::defaultConfiguration() + [
      'contain' => '',
      'space_around' => '',
      'section_title' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $args = $this->getFormArgs($form_state);
    $bundle = $args->getContextValue('entity')->bundle();
    $configuration = $this->getConfiguration();

    $form['section_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Section title'),
      '#default_value' => $configuration['section_title'],
      '#description' => $this->t('Add an optional title to the section.'),
    ];

    $form['whitespace_settings'] = [
      '#type' => 'fieldset',
    ];

    // @todo Disable the default whitespace checkbox if both
    //   whitespace-specific checkboxes are checked. Configuring
    //   that state initially resulted in buggy behavior.
    $config = $configuration['whitespace_settings'];
    $form['whitespace_settings']['whitespace'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add whitespace'),
      '#default_value' => $config['whitespace'] ?? FALSE,
      '#description' => $this->t('Add whitespace to this section.'),
    ];

    $form['whitespace_settings']['whitespace__top'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add top whitespace'),
      '#default_value' => $config['whitespace__top'] ?? TRUE,
      '#description' => $this->t('Add whitespace above this section.'),
      '#states' => [
        'visible' => [
          'input[name="layout_settings[whitespace_settings][whitespace]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['whitespace_settings']['whitespace__bottom'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Add bottom whitespace'),
      '#default_value' => $config['whitespace__bottom'] ?? TRUE,
      '#description' => $this->t('Add whitespace below this section.'),
      '#states' => [
        'visible' => [
          'input[name="layout_settings[whitespace_settings][whitespace]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    if ($bundle === 'landing_page') {
      $form['contain'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Contain content'),
        '#default_value' => $configuration['contain'],
        '#description' => $this->t('Contain the content within the center of the page.'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Any additional form validation that is required.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['whitespace_settings'] = $form_state->getValue('whitespace_settings');
    $this->configuration['contain'] = $form_state->getValue('contain');
    $this->configuration['section_title'] = $form_state->getValue('section_title');
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);

    $configuration = $this->getConfiguration();

    $build['#section_title'] = $configuration['section_title'];

    $classes = [];

    if (isset($configuration['whitespace_settings'])) {
      $config = $configuration['whitespace_settings'];
      if ($config['whitespace']) {
        $classes[] = 'usa-section';
        if ($config['whitespace__top'] != TRUE) {
          $classes[] = 'padding-top-0';
        }
        if ($config['whitespace__bottom'] != TRUE) {
          $classes[] = 'padding-bottom-0';
        }
        if ($config['whitespace__top'] != TRUE && $config['whitespace__bottom'] != TRUE) {
          $classes = [];
        }
      }
    }

    if ($configuration['contain']) {
      $classes[] = 'grid-container';
    }

    $build['#attributes']['class'] = $classes;

    return $build;
  }

  /**
   * Get form arguments.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   State form.
   *
   * @return array|mixed
   *   List arguments.
   */
  public static function getFormArgs(FormStateInterface $form_state) {
    $args = [];

    $form_build_info = $form_state->getBuildInfo();
    if (!empty($form_build_info['args'])) {
      $args = array_shift($form_build_info['args']);
    }

    return $args;
  }

}
