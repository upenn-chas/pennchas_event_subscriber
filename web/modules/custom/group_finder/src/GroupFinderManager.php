<?php

declare(strict_types = 1);

namespace Drupal\group_finder;

use Drupal\Component\Utility\SortArray;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\group_finder\Annotation\GroupFinder;

/**
 * Plugin type manager for GroupFinder plugins.
 */
class GroupFinderManager extends DefaultPluginManager {

  /**
   * Constructs a new GroupFinderManager.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/GroupFinder', $namespaces, $module_handler, GroupFinderInterface::class, GroupFinder::class);
    $this->alterInfo('group_finder_info');
    $this->setCacheBackend($cache_backend, 'group_finder_plugins');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();
    uasort($definitions, [SortArray::class, 'sortByWeightElement']);
    return $definitions;
  }

}
