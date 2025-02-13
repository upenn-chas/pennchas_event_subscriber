<?php

declare(strict_types = 1);

namespace Drupal\group_finder\Plugin\GroupFinder;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group_finder\GroupFinderBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Plugin find group from group parameter in URL query.
 *
 * @GroupFinder(
 *   id = "group_url_query",
 *   label = @Translation("Group URL query"),
 *   description = @Translation("When in context of group in URL query"),
 *   weight = 30,
 * )
 */
class GroupUrlQuery extends GroupFinderBase {

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected RequestStack $requestStack;

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
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to retrieve the current request.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, RequestStack $request_stack, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_match);
    $this->requestStack = $request_stack;
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
      $container->get('request_stack'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    return !empty($this->requestStack->getCurrentRequest()->query->get('group'));
  }

  /**
   * {@inheritdoc}
   */
  public function getGroup(): ?GroupInterface {
    $group_id = $this->requestStack->getCurrentRequest()->query->get('group');
    return $this->entityTypeManager->getStorage('group')->load($group_id);
  }

}
