<?php

declare(strict_types = 1);

namespace Drupal\group_media_library;

use Drupal\group\Entity\GroupTypeInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationInterface;

/**
 * Helper class for group relation type.
 */
class GroupRelationTypeHelper {

  /**
   * Get the bundle ids for the enabled relations filtered by group type.
   *
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The group type object.
   * @param string $entity_type
   *   The entity type for which to return the bundle ids.
   *
   * @return array
   *   By default, it returns the bundle ids for the enabled plugins. When
   *   passing the entity types, it will return the bundles of the passed types.
   */
  public static function getBundleIdsByGroupType(GroupTypeInterface $group_type, string $entity_type): array {
    $bundles = [];
    /** @var \Drupal\group\Plugin\Group\Relation\GroupRelationInterface $plugin */
    foreach ($group_type->getInstalledPlugins() as $plugin) {
      if ($plugin->getRelationType()->getEntityTypeId() === $entity_type && ($bundle = $plugin->getRelationType()->getEntityBundle())) {
        $bundles[] = $bundle;
      }
    }
    return $bundles;
  }

  /**
   * Get the relation plugin by bundle for a given group type.
   *
   * @param \Drupal\group\Entity\GroupTypeInterface $group_type
   *   The group type object.
   * @param string $entity_type
   *   The entity type for which to return the plugin.
   * @param string $bundle
   *   The bundle for which to return the plugin.
   *
   * @return \Drupal\group\Plugin\Group\Relation\GroupRelationInterface|null
   *   The plugin, if found, null otherwise.
   */
  public static function getPluginByBundle(GroupTypeInterface $group_type, string $entity_type, string $bundle): ?GroupRelationInterface {
    /** @var \Drupal\group\Plugin\Group\Relation\GroupRelationInterface $plugin */
    foreach ($group_type->getInstalledPlugins() as $plugin) {
      if ($plugin->getRelationType()->getEntityTypeId() === $entity_type && $plugin->getRelationType()->getEntityBundle() === $bundle) {
        return $plugin;
      }
    }

    return NULL;
  }

}
