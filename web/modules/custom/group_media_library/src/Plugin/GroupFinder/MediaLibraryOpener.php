<?php

declare(strict_types = 1);

namespace Drupal\group_media_library\Plugin\GroupFinder;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupInterface;
use Drupal\group_finder\GroupFinderBase;
use Drupal\group_media_library\GroupMediaLibraryState;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Plugin finds group from media library opener.
 *
 * @GroupFinder(
 *   id = "media_library_opener",
 *   label = @Translation("Media library opener"),
 *   description = @Translation("When in context of media library opener."),
 *   weight = 40,
 * )
 */
class MediaLibraryOpener extends GroupFinderBase {

  /**
   * The group entity.
   *
   * @var \Drupal\group\Entity\GroupInterface
   */
  protected $group;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * GroupMediaLibraryFieldWidgetOpener constructor.
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match, RequestStack $request_stack) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $route_match);
    $this->requestStack = $request_stack;
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
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isApplicable(): bool {
    $route_names = [
      'media_library.ui',
      'view.media_library.widget',
      'view.media_library.widget_table',
    ];
    if (!in_array($this->routeMatch->getRouteName(), $route_names)) {
      return FALSE;
    }

    $request = $this->requestStack->getCurrentRequest();
    if ($state = GroupMediaLibraryState::fromRequest($request)) {
      $this->group = $state->getGroupId()
        ? Group::load($state->getGroupId())
        : NULL;
      return TRUE;
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
