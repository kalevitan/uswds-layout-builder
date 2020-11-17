<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsAccordionBlock;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UswdsAccordionBlock.
 */
class UswdsAccordionEventSubscriber implements EventSubscriberInterface {

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
      'uswds_accordion_block' => [
        'template' => 'block--uswds-accordion',
        'render element' => 'content',
        'variables' => [
          'uswds_accordion_heading' => NULL,
          'uswds_accordion_body' => NULL,
          'uswds_accordion_expanded' => NULL,
        ],
        'path' => $modulePath . '/src/Plugin/Block/UswdsAccordionBlock',
      ],
    ];

    $event->addNewThemes($newtheme);
  }

}
