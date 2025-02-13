<?php

declare(strict_types = 1);

namespace Drupal\group_finder\Plugin\GroupFinder;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Entity\GroupRelationship;
use Drupal\group_finder\GroupFinderBase;
use Drupal\user\UserInterface;

/**
 * Plugin for getting group from content.
 *
 * @GroupFinder(
 *   id = "group_by_content",
 *   label = @Translation("Group by content"),
 *   description = @Translation("When in context of a content that has been added to a group"),
 *   weight = 20,
 * )
 */
class GroupByContent extends GroupFinderBase {

  /**
   * The group entity.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    if (($route = $this->routeMatch->getRouteObject()) && ($parameters = $route->getOption('parameters'))) {
      // Determine if the current route represents an entity.
      foreach ($parameters as $name => $options) {
        if (isset($options['type']) && str_contains($options['type'], 'entity:')) {
          $entity = $this->routeMatch->getParameter($name);
          // Exclude user entity type so that it's never handle by this
          // finder. If case you have a case where you want to get specific
          // group by current user, you could create your own custom group
          // finder plugin or override this plugin.
          if ($entity instanceof ContentEntityInterface
            && !$entity instanceof UserInterface
            && $entity->hasLinkTemplate('canonical')) {
            // Load all the group relationships for this entity.
            if ($group_relationships = GroupRelationship::loadByEntity($entity)) {
              if (count($group_relationships) >= 1) {
                // Use the first group assigned to the entity.
                // In case your website should have a different behaviour, e.g.
                // get last group, then you should create your own custom group
                // finder plugin or override this plugin.
                $group_relationship = reset($group_relationships);
                $this->group = $group_relationship->getGroup();
                return TRUE;
              }
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(): ?GroupInterface {
    return $this->group ?? NULL;
  }

}
