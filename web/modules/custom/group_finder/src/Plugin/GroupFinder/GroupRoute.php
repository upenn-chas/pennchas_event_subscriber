<?php

declare(strict_types = 1);

namespace Drupal\group_finder\Plugin\GroupFinder;

use Drupal\group\Entity\GroupInterface;
use Drupal\group_finder\GroupFinderBase;

/**
 * Plugin find group from group route.
 *
 * @GroupFinder(
 *   id = "group_route",
 *   label = @Translation("Group route"),
 *   description = @Translation("When in context of group route"),
 *   weight = 10,
 * )
 */
class GroupRoute extends GroupFinderBase {

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    return $this->routeMatch->getParameter('group') instanceof GroupInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(): GroupInterface {
    return $this->routeMatch->getParameter('group');
  }

}
