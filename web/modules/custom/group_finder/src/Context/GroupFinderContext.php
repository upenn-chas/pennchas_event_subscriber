<?php

declare(strict_types = 1);

namespace Drupal\group_finder\Context;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Plugin\Context\EntityContext;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\group_finder\GroupFinderProviderInterface;

/**
 * Sets the group as a context if the group finder find a group.
 */
class GroupFinderContext implements ContextProviderInterface {

  use StringTranslationTrait;

  /**
   * The group finder provider.
   *
   * @var \Drupal\group_finder\GroupFinderProviderInterface
   */
  protected $groupFinderProvider;

  /**
   * Constructs a new GroupFinderContext.
   *
   * @param \Drupal\group_finder\GroupFinderProviderInterface $group_finder_provider
   *   The group finder provider service.
   */
  public function __construct(GroupFinderProviderInterface $group_finder_provider) {
    $this->groupFinderProvider = $group_finder_provider;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualified_context_ids): array {
    // Create an optional context definition for group entities.
    $context_definition = EntityContextDefinition::fromEntityTypeId('group')
      ->setRequired(FALSE);

    // Get applicable group finder plugin.
    $group_finder = $this->groupFinderProvider->get();

    // Cache this context per group on the route.
    $cacheability = new CacheableMetadata();

    // We need to use the general 'route' cache context. Using 'route.group' is
    // not enough for this context provider, as the group provider service
    // finds groups on routes that are not group routes (nodes, views).
    $cacheability->setCacheContexts(['route']);

    // Create a context from the definition and the group retrieved from group
    // finder.
    $context = new Context($context_definition, $group_finder?->getGroup());
    $context->addCacheableDependency($cacheability);

    return ['group' => $context];
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts(): array {
    return ['group' => EntityContext::fromEntityTypeId('group', $this->t('Group from group finder'))];
  }

}
