<?php

declare(strict_types = 1);

namespace Drupal\group_media_library;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\group\Entity\GroupType;
use Drupal\media_library\MediaLibraryState;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\Request;

/**
 * A value object for the group media library state.
 *
 * When the group media library is opened it needs several parameters to work
 * properly. These parameters are normally extracted from the current URL, then
 * retrieved from and managed by the GroupMediaLibraryState value object.
 * The following parameters are required in order to open the media library:
 * - group_finder_plugin_id: The plugin id for the group finder which is
 *   applicable when initiating the GroupMediaLibraryState object.
 * - group_type_id: The id of the type of the group that found by the group
 *   finder.
 * - group_uuid: The uuid for the group. If the group exist, it is the uuid of
 *   the existing group. If the group is not exits (when in context of create
 *   new group), it is the uuid of the group entity from the form object.
 * The following parameters are optional and are set based on other factors e.g.
 * group finder and groupmedia module:
 * - group_id: When the group finder finds a saved group, the id of the group
 *   is added to the parameters.
 * - group_relation_plugin_id: This is set based on the entity being created or
 *   updated. When the entity has a group relation plugin installed by the group
 *   type, the id of that plugin is used e.g. node and comment. When the entity
 *   doesn't have a plugin, then the plugin of the root entity is being used
 *   e.g. paragraph. When the entity is a group, it keeps the initial value.
 * - group_media_relation_plugin_id: The id for the group media relation plugin
 *   (only if exists and installed in the group type). This parameter is set
 *   automatically based on the group_type_id parameter and the selected media
 *   type from the MediaLibraryState object.
 */
class GroupMediaLibraryState extends ParameterBag implements CacheableDependencyInterface {

  /**
   * Constructs a new GroupMediaLibraryState.
   *
   * @param \Drupal\media_library\MediaLibraryState $state
   *   The current state of the media library.
   * @param array $parameters
   *   The group media library parameters.
   */
  public function __construct(MediaLibraryState $state, array $parameters) {
    // Set media relation plugin id.
    if ($group_media_relation_plugin = GroupRelationTypeHelper::getPluginByBundle(GroupType::load($parameters['group_type_id']), 'media', $state->getSelectedTypeId())) {
      $parameters += [
        'group_media_relation_plugin_id' => $group_media_relation_plugin->getPluginId(),
      ];
    }

    $this->validateRequiredParameters($parameters);
    parent::__construct($parameters);
  }

  /**
   * Creates a new GroupMediaLibraryState object.
   *
   * @param \Drupal\media_library\MediaLibraryState $state
   *   The current state of the media library.
   * @param string $group_finder_plugin_id
   *   The group finder plugin id.
   * @param string $group_type_id
   *   The group type id.
   * @param string $group_uuid
   *   The group uuid.
   * @param string $group_id
   *   The group id.
   * @param string $group_relation_plugin_id
   *   The group relation plugin id.
   *
   * @return static
   *   A state object.
   */
  public static function create(
    MediaLibraryState $state,
    string $group_finder_plugin_id,
    string $group_type_id,
    string $group_uuid,
    string $group_id,
    string $group_relation_plugin_id): static {

    return new static($state, [
      'group_finder_plugin_id' => $group_finder_plugin_id,
      'group_type_id' => $group_type_id,
      'group_uuid' => $group_uuid,
      'group_id' => $group_id,
      'group_relation_plugin_id' => $group_relation_plugin_id,
    ]);
  }

  /**
   * Get the group media library state from a request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return static|null
   *   Group media library state object if found in the request, null otherwise.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the hash query parameter is invalid.
   */
  public static function fromRequest(Request $request): ?static {
    // First get the state from the media library state, to make sure the
    // media library required parameter are exits and the hash is valid.
    $media_library_state = MediaLibraryState::fromRequest($request);

    return static::fromMediaLibraryState($media_library_state);
  }

  /**
   * Get the group media library state from a media library state.
   *
   * @param \Drupal\media_library\MediaLibraryState $media_library_state
   *   A state of the media library.
   *
   * @return static|null
   *   Group media library state object if found in the state, null otherwise.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the hash query parameter is invalid.
   */
  public static function fromMediaLibraryState(MediaLibraryState $media_library_state): ?static {
    // Create a GroupMediaLibraryState object through the create method to make
    // sure all group media library validation runs.
    $opener_parameters = $media_library_state->getOpenerParameters();

    if (!isset($opener_parameters['group_parameters'])) {
      return NULL;
    }

    $group_media_library_parameters = $opener_parameters['group_parameters'];
    return static::create(
      $media_library_state,
      $group_media_library_parameters['group_finder_plugin_id'],
      $group_media_library_parameters['group_type_id'],
      $group_media_library_parameters['group_uuid'],
      $group_media_library_parameters['group_id'],
      $group_media_library_parameters['group_relation_plugin_id']
    );
  }

  /**
   * Validates the required parameters for a new GroupMediaLibraryState object.
   *
   * @param array $parameters
   *   The group media library parameters.
   *
   * @throws \InvalidArgumentException
   *   If one of the passed arguments is missing or does not pass the
   *   validation.
   *
   * @SuppressWarnings(PHPMD.CyclomaticComplexity)
   */
  protected function validateRequiredParameters(array $parameters): void {
    // The group finder plugin id must be a non-empty string.
    if (!isset($parameters['group_finder_plugin_id']) || empty(trim($parameters['group_finder_plugin_id']))) {
      throw new \InvalidArgumentException('The group finder plugin id parameter is required.');
    }

    // The group type must be a non-empty string.
    if (!isset($parameters['group_type_id']) || empty(trim($parameters['group_type_id']))) {
      throw new \InvalidArgumentException('The group type parameter is required.');
    }

    // The group uuid must be a non-empty string.
    if (!isset($parameters['group_uuid']) || empty(trim($parameters['group_uuid']))) {
      throw new \InvalidArgumentException('The group uuid parameter is required.');
    }

    // Make sure the group uuid is in uuid format.
    if (!Uuid::isValid($parameters['group_uuid'])) {
      throw new \InvalidArgumentException('The passed group uuid parameter is not in uuid format.');
    }
  }

  /**
   * Returns the ID of the group finder plugin id.
   *
   * @return string
   *   The group finder plugin id.
   */
  public function getGroupFinderPluginId(): string {
    return $this->get('group_finder_plugin_id');
  }

  /**
   * Returns the group type id.
   *
   * @return string
   *   The group type id.
   */
  public function getGroupTypeId(): string {
    return $this->get('group_type_id');
  }

  /**
   * Returns the group uuid.
   *
   * @return string
   *   The group uuid.
   */
  public function getGroupUuid(): string {
    return $this->get('group_uuid');
  }

  /**
   * Returns the group id.
   *
   * @return string
   *   The group id.
   */
  public function getGroupId(): string {
    return $this->get('group_id');
  }

  /**
   * Returns the group relation plugin id.
   *
   * @return string
   *   The group relation plugin id.
   */
  public function getGroupRelationPluginId(): string {
    return $this->get('group_relation_plugin_id');
  }

  /**
   * Returns the group media relation plugin id.
   *
   * @return string|null
   *   The group media relation plugin id if exists, null otherwise.
   */
  public function getGroupMediaRelationPluginId(): ?string {
    return $this->get('group_media_relation_plugin_id');
  }

  /**
   * Returns the parameters.
   *
   * @param string|null $key
   *   The name of the parameter to return or null to get them all.
   *
   * @return array
   *   An array of parameters.
   *
   * @todo Remove this when Symfony 4 is no longer supported.
   *   See https://www.drupal.org/node/3162981
   */
  public function all(string $key = NULL): array {
    if ($key === NULL) {
      return $this->parameters;
    }

    $value = $this->parameters[$key] ?? [];
    if (!is_array($value)) {
      throw new \UnexpectedValueException(sprintf('Unexpected value for parameter "%s": expecting "array", got "%s".', $key, get_debug_type($value)));
    }

    return $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts(): array {
    return ['url.query_args'];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    return Cache::PERMANENT;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags(): array {
    return [];
  }

}
