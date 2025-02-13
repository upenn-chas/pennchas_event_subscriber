<?php

declare(strict_types = 1);

namespace Drupal\group_media_library_widget;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Plugin\Group\Relation\GroupRelationInterface;
use Drupal\group_finder\GroupFinderInterface;
use Drupal\group_finder\GroupFinderProviderInterface;
use Drupal\group_media_library\GroupMediaLibraryState;
use Drupal\group_media_library\GroupRelationTypeHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Contains various methods for the media library widget alter.
 *
 * @todo Could be refactored when
 *    https://www.drupal.org/project/drupal/issues/3258644 is in.
 */
class MediaLibraryFieldWidget implements ContainerInjectionInterface {

  /**
   * The group finder provider service.
   *
   * @var \Drupal\group_finder\GroupFinderProvider
   */
  protected $groupFinderProvider;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs for MediaLibraryFieldWidget.
   *
   * @param \Drupal\group_finder\GroupFinderProviderInterface $group_finder_provider
   *   The group finder provider service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(GroupFinderProviderInterface $group_finder_provider, ModuleHandlerInterface $module_handler) {
    $this->groupFinderProvider = $group_finder_provider;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('group_finder.provider'),
      $container->get('module_handler')
    );
  }

  /**
   * Form alter for the media library widget.
   *
   * Adds GroupMediaLibraryState to the media library opener parameters.
   */
  public function formAlter(&$element, FormStateInterface $form_state, $context) {
    $group_finder = $this->groupFinderProvider->get();

    // Early return when in context other than the required group contexts.
    $finders = [
      'create_group',
      'group_by_content',
      'group_route',
    ];
    $this->moduleHandler->alter('group_media_library_widget_group_finders', $finders);
    if (empty($group_finder) || empty($element['open_button']) || !in_array($group_finder->getPluginId(), $finders)) {
      return $element;
    }

    /** @var \Drupal\media_library\MediaLibraryState $state */
    $state = $element['open_button']['#media_library_state'];

    // Get group relation plugin if applicable.
    $group_relation_plugin = static::getGroupRelationPlugin($context['items'], $group_finder, $form_state);

    // Create the group media library state.
    // We initialize the optional parameter to '0' because of the hash.
    $group_state = GroupMediaLibraryState::create(
      $state,
      $group_finder->getPluginId(),
      $group_finder->getGroup()->getGroupType()->id(),
      static::getGroupUuid($group_finder, $form_state) ?? '0',
      $group_finder->getGroup()->id() ? $group_finder->Getgroup()->id() : '0',
      $group_relation_plugin ? $group_relation_plugin->getPluginId() : '0'
    );

    // Add group parameters to the media library opener parameters.
    $state->set('media_library_opener_parameters', array_merge($state->getOpenerParameters(), ['group_parameters' => $group_state->all()]));
    $state->set('hash', $state->getHash());

    $element['open_button']['#media_library_state'] = $state;

    return $element;
  }

  /**
   * Get group uuid.
   *
   * Having the group uuid in the state is useful when the group is not saved
   * yet.
   *
   * @param \Drupal\group_finder\GroupFinderInterface $group_finder
   *   The group finder.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return string|null
   *   The group uuid if found, null otherwise.
   */
  protected static function getGroupUuid(GroupFinderInterface $group_finder, FormStateInterface $form_state): ?string {
    if (($group = $group_finder->getGroup()) && $group->id()) {
      return $group->uuid();
    }
    else {
      // For new group, get the group uuid from the form.
      $form_object = $form_state->getFormObject();
      if ($form_object instanceof EntityFormInterface) {
        $form_entity = $form_object->getEntity();
        if ($form_entity->getEntityTypeId() === 'group') {
          return $form_entity->uuid();
        }
      }
    }

    return NULL;
  }

  /**
   * Get group relation plugin if applicable.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $items
   *   The field items.
   * @param \Drupal\group_finder\GroupFinderInterface $group_finder
   *   The group finder.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   *
   * @return \Drupal\group\Plugin\Group\Relation\GroupRelationInterface|null
   *   The group relation enabler if found, null otherwise.
   */
  protected static function getGroupRelationPlugin(FieldItemListInterface $items, GroupFinderInterface $group_finder, FormStateInterface $form_state): ?GroupRelationInterface {
    $form_object = $form_state->getFormObject();
    if ($form_object instanceof EntityFormInterface) {
      $form_entity = $form_object->getEntity();
    }

    if (!empty($form_entity)) {
      $entity = $items->getEntity();

      // When the entity is paragraph, we should get the plugin for the root
      // entity.
      $entity_type_id = $entity->getEntityTypeId() === 'paragraph'
        ? $form_entity->getEntityTypeId()
        : $entity->getEntityTypeId();

      return GroupRelationTypeHelper::getPluginByBundle($group_finder->getGroup()->getGroupType(), $entity_type_id, $form_entity->bundle());
    }

    return NULL;
  }

}
