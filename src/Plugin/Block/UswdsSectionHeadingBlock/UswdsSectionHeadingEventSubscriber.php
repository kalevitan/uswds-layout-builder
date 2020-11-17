<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsSectionHeadingBlock;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UswdsSectionHeadingBlock.
 */
class UswdsSectionHeadingEventSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [];

    $events[HookEventDispatcherInterface::THEME][] = ['themeCallback'];

    return $events;
  }

  /**
   * Custom Callback for theme event.
   *
   * @param object $event
   *   Theme hook event.
   */
  public function themeCallback($event) {
    $modulePath = drupal_get_path('module', 'uswds_layout_builder');

    $newtheme = [
      'uswds_section_heading_block' => [
        'template' => 'block--uswds-section-heading',
        'render element' => 'content',
        'variables' => [
          'uswds_section_heading_title' => NULL,
          'uswds_section_heading_body' => NULL,
          'uswds_section_heading_cta_url' => NULL,
          'uswds_section_heading_cta_title' => NULL,
        ],
        'path' => $modulePath . '/src/Plugin/Block/UswdsSectionHeadingBlock',
      ],
    ];

    $event->addNewThemes($newtheme);
  }

}
