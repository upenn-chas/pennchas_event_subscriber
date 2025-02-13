<?php

declare(strict_types = 1);

namespace Drupal\group_finder\Plugin\GroupFinder;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group_finder\GroupFinderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for create new group.
 *
 * @GroupFinder(
 *   id = "create_group",
 *   label = @Translation("Create group"),
 *   description = @Translation("When in context of creating new group"),
 *   weight = 0,
 * )
 */
class CreateGroup extends GroupFinderBase {

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The class constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_match);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    $route = $this->routeMatch->getRouteObject();
    return $route
      && $route->getOption('_group_operation_route')
      && isset($route->getDefaults()['_entity_form'])
      && $route->getDefaults()['_entity_form'] === 'group.add';
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(): ?GroupInterface {
    return $this->entityTypeManager->getStorage('group')->create(
      ['type' => $this->routeMatch->getParameter('group_type')->id()]
    );
  }

}
