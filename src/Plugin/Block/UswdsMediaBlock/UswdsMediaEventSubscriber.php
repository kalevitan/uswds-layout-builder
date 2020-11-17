<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsMediaBlock;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UswdsMediaBlock.
 */
class UswdsMediaEventSubscriber implements EventSubscriberInterface {

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
      'uswds_media_block' => [
        'template' => 'block--uswds-media',
        'render element' => 'content',
        'variables' => [
          'media' => [],
          'variant' => NULL,
        ],
        'path' => $modulePath . '/src/Plugin/Block/UswdsMediaBlock',
      ],
    ];

    $event->addNewThemes($newtheme);
  }

}
