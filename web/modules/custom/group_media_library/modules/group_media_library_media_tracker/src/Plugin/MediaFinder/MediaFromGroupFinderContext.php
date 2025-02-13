<?php

declare(strict_types = 1);

namespace Drupal\group_media_library_media_tracker\Plugin\MediaFinder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\group_finder\GroupFinderProviderInterface;
use Drupal\groupmedia\Plugin\MediaFinder\MediaFinderBase;
use Drupal\media\MediaInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for media created from a group finder context.
 *
 * @MediaFinder(
 *   id = "media_from_group_finder_context",
 *   label = @Translation("Media from group finder context"),
 *   description = @Translation("Tracks media created from group finder context."),
 * )
 */
class MediaFromGroupFinderContext extends MediaFinderBase {

  /**
   * The group finder provider service.
   *
   * @var \Drupal\group_finder\GroupFinderProvider
   */
  protected $groupFinderProvider;

  /**
   * MediaFromGroupContext constructor.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $plugin_id
   *   Plugin id.
   * @param mixed $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service for alter hooks.
   * @param \Drupal\group_finder\GroupFinderProviderInterface $group_finder_provider
   *   The group finder provider service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ModuleHandlerInterface $module_handler, GroupFinderProviderInterface $group_finder_provider) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $module_handler);
    $this->groupFinderProvider = $group_finder_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): self {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('module_handler'),
      $container->get('group_finder.provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(EntityInterface $entity): bool {
    if ($entity instanceof MediaInterface) {
      return $this->groupFinderProvider->get() !== NULL;
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function process(EntityInterface $entity): array {
    return [$entity];
  }

}
