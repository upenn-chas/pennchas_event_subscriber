<?php

namespace Drupal\url_alteration\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Group Node routes.
 */
class RouteSubscriber extends RouteSubscriberBase
{

  /**
   * Alters existing routes to customize paths and behavior.
   * 
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection)
  {
    // Clone and modify the 'entity.group_relationship.create_page' route
    // to create a new route for group node creation.
    if ($route = $collection->get('entity.group_relationship.create_page')) {
      $copy = clone $route;
      $copy->setPath('group/{group}/node/create');
      $copy->setDefault('base_plugin_id', 'group_node');
      $collection->add('entity.group_relationship.group_node_create_page', $copy);
    }

    // Clone and modify the 'entity.group_relationship.add_page' route
    // to create a new route for adding nodes under the group context.
    if ($route = $collection->get('entity.group_relationship.add_page')) {
      $copy = clone $route;
      $copy->setPath('group/{group}/node/add');
      $copy->setDefault('base_plugin_id', 'group_node');
      $collection->add('entity.group_relationship.group_node_add_page', $copy);
    }

    // Override the default 'user.login' route to use a custom backend login path.
    if ($route = $collection->get('user.login')) {
      $route->setPath('/backend/login');
    }
  }
}
