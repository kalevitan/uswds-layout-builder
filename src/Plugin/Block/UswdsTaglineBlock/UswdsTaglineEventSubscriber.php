<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsTaglineBlock;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UswdsTaglineBlock.
 */
class UswdsTaglineEventSubscriber implements EventSubscriberInterface {

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
      'uswds_tagline_block' => [
        'template' => 'block--uswds-tagline',
        'render element' => 'content',
        'variables' => [
          'uswds_tagline_heading' => NULL,
          'uswds_tagline_body' => NULL,
        ],
        'path' => $modulePath . '/src/Plugin/Block/UswdsTaglineBlock',
      ],
    ];

    $event->addNewThemes($newtheme);
  }

}
