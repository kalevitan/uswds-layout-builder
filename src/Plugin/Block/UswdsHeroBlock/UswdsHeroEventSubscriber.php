<?php

namespace Drupal\uswds_layout_builder\Plugin\Block\UswdsHeroBlock;

use Drupal\hook_event_dispatcher\HookEventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class UswdsHeroBlock.
 */
class UswdsHeroEventSubscriber implements EventSubscriberInterface {

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
      'uswds_hero_block' => [
        'template' => 'block--uswds-hero',
        'render element' => 'content',
        'variables' => [
          'uswds_hero_heading' => NULL,
          'uswds_hero_body' => NULL,
          'uswds_hero_cta_url' => NULL,
          'uswds_hero_cta_title' => NULL,
          'uswds_hero_image' => NULL,
        ],
        'path' => $modulePath . '/src/Plugin/Block/UswdsHeroBlock',
      ],
    ];

    $event->addNewThemes($newtheme);
  }

}
