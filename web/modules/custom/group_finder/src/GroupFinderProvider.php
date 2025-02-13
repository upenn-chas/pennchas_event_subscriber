<?php

declare(strict_types = 1);

namespace Drupal\group_finder;

/**
 * Class to find the applicable group finder.
 *
 * A simple class used for getting the applicable group finder plugin.
 */
class GroupFinderProvider implements GroupFinderProviderInterface {

  /**
   * The group finder plugin manager.
   *
   * @var \Drupal\group_finder\GroupFinderManager
   */
  protected $groupFinderManager;

  /**
   * GroupFinderProvider constructor.
   *
   * @param \Drupal\group_finder\GroupFinderManager $group_finder_manager
   *   Group finder plugin manager.
   */
  public function __construct(GroupFinderManager $group_finder_manager) {
    $this->groupFinderManager = $group_finder_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function get(): ?GroupFinderInterface {
    $plugins = $this->groupFinderManager->getDefinitions();

    foreach ($plugins as $plugin_id => $definition) {
      /** @var \Drupal\group_finder\GroupFinderInterface $pluginInstance */
      $pluginInstance = $this->groupFinderManager->createInstance($plugin_id);
      if ($pluginInstance && $pluginInstance->isApplicable()) {
        return $pluginInstance;
      }
    }

    return NULL;
  }

}
