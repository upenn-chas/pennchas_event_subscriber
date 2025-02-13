<?php

declare(strict_types = 1);

namespace Drupal\group_media_library;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\group\Entity\GroupInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface;
use Drupal\media_library\MediaLibraryState;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contains various access control methods for the media library in group.
 *
 * @todo Revisit when https://www.drupal.org/project/drupal/issues/3327106 is
 *   solved.
 */
final class Access implements ContainerInjectionInterface {

  /**
   * The group relation type manager.
   *
   * @var \Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface
   */
  protected $groupRelationTypeManager;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Constructs for Access.
   *
   * @param \Drupal\group\Plugin\Group\Relation\GroupRelationTypeManagerInterface $group_relation_type_manager
   *   The group relation type manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack used to retrieve the current request.
   */
  public function __construct(GroupRelationTypeManagerInterface $group_relation_type_manager, EntityTypeManagerInterface $entity_type_manager, RequestStack $request_stack) {
    $this->groupRelationTypeManager = $group_relation_type_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('group_relation_type.manager'),
      $container->get('entity_type.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * Controls access to the "add media" form in the media library.
   *
   * For media library opener to display the "add media form", it checks the
   * access to create media.
   * Media access handler is not aware of the access granted by group module.
   * In order to make the media library obey the group's access, when
   * GroupMediaLibraryState exists, we check the access to create media in the
   * group of the GroupMediaLibraryState.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param \Drupal\media_library\MediaLibraryState $state
   *   The current state of the media library.
   * @param string $entity_bundle
   *   The media bundle.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result for the "add media" form.
   *
   * @see \Drupal\media_library\MediaLibraryUiBuilder::buildMediaTypeAddForm()
   */
  public function addFormAccess(AccountInterface $account, MediaLibraryState $state, string $entity_bundle): AccessResultInterface {
    $group_state = GroupMediaLibraryState::fromMediaLibraryState($state);

    // No opinion when there is no group media library state. Meaning we are not
    // in a group context.
    if (!$group_state || $entity_bundle !== $state->getSelectedTypeId()) {
      return AccessResult::neutral();
    }

    return $this->checkGroupContentCreateAccess($account, $group_state->getGroupMediaRelationPluginId(), $this->getGroup($group_state));
  }

  /**
   * Controls access to the media library opener.
   *
   * This method is called on the following routes:
   *
   * - media_library.ui
   * This route is used for the ajax call when a file is uploaded on new media
   * creation in the media library
   * - view.media_library.widget
   * - view.media_library.widget_table
   * These routes are used during the ajax call when switching between grid and
   * table view in the media library.
   *
   * This access check is needed because, the access check for these routes
   * do access check for create/update the entity that being created/updated.
   * Since core is not aware of the "create" access for content in groups, we
   * should apply the group's access too.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param array $context
   *   The entity create access hook context array.
   * @param string|null $entity_bundle
   *   The entity bundle or NULL.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result for the "add media" form.
   *
   * @see \Drupal\media_library\MediaLibraryFieldWidgetOpener::checkAccess()
   */
  public function openerAccess(AccountInterface $account, array $context, string $entity_bundle = NULL): AccessResultInterface {
    $group_state = GroupMediaLibraryState::fromRequest($this->requestStack->getCurrentRequest());

    // No opinion when there is no group media library state. Meaning we are not
    // in a group context.
    if (!$group_state) {
      return AccessResult::neutral();
    }

    return $this->checkGroupContentCreateAccess($account, $group_state->getGroupRelationPluginId(), $this->getGroup($group_state));
  }

  /**
   * Returns the group for the access check.
   *
   * @param \Drupal\group_media_library\GroupMediaLibraryState $group_state
   *   The group media library state.
   *
   * @return \Drupal\group\Entity\GroupInterface
   *   The group entity.
   */
  protected function getGroup(GroupMediaLibraryState $group_state): GroupInterface {
    /** @var \Drupal\group\Entity\Storage\GroupStorage $storage */
    $storage = $this->entityTypeManager->getStorage('group');

    return $group_state->getGroupId()
      ? $storage->load($group_state->getGroupId())
      : $storage->create(['type' => $group_state->getGroupTypeId()]);
  }

  /**
   * Checks group content create access for the account.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The user session for which to check access.
   * @param string|null $plugin_id
   *   The group relation plugin ID or NULL.
   * @param \Drupal\group\Entity\GroupInterface|null $group
   *   The group or NULL.
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   Access result for group content create access.
   */
  protected function checkGroupContentCreateAccess(AccountInterface $account, string $plugin_id = NULL, GroupInterface $group = NULL): AccessResultInterface {
    if (!$group || !$plugin_id) {
      // If group or plugin_id is not passed, no opinion.
      return AccessResult::neutral();
    }

    // Check create access in the group.
    // Return allow if the account has the access to create entity of the passed
    // plugin id, otherwise forbidden is returned.
    $access = $this->groupRelationTypeManager->getAccessControlHandler($plugin_id)
      ->entityCreateAccess($group, $account, TRUE);

    return $access instanceof AccessResultAllowed
      ? AccessResult::allowed()->inheritCacheability($access)
      : AccessResult::forbidden()->inheritCacheability($access);
  }

}
