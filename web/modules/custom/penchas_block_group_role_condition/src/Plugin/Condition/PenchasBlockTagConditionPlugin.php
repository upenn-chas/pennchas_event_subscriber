<?php

namespace Drupal\penchas_block_group_role_condition\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupRole;
use Drupal\group\Entity\GroupContent;

/**
 * This main class which add ability to determine device.
 *
 * @Condition(
 *   id = "penchas_block_group_role_condition_plugin",
 *   label = @Translation("Group Role Condition"),
 * )
 */
class PenchasBlockTagConditionPlugin extends ConditionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $group_roles = get_all_group_roles();
    $form['negate'] = [];
    $form['block_group_roles'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Block Display Condition with group roles'),
      '#default_value' => $this->configuration['block_group_roles'],
      '#options' => $group_roles,
      '#description' => $this->t('If you select no Block role, the condition will evaluate to TRUE for all Blocks.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'block_group_roles' => [],
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['block_group_roles'] = array_filter($form_state->getValue('block_group_roles'));
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    $block_group_roles = $this->configuration['block_group_roles'];

    if (count($block_group_roles) > 1) {
      $block_group_roles = implode(', ', $block_group_roles);
    }
    else {
      $block_group_roles = reset($block_group_roles);
    }

    if (!empty($this->configuration['negate'])) {
      return $this->t('The Tag is not @block_group_roles', ['@block_group_roles' => $block_group_roles]);
    }
    else {
      return $this->t('The Tag is @block_group_roles', ['@block_group_roles' => $block_group_roles]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {

    if (empty($this->configuration['block_group_roles']) && !$this->isNegated()) {
      return TRUE;
    }
    // $termIds = $this->configuration['block_group_roles'];
    // $request = \Drupal::request();
    // $request_attributes = $request->attributes;


    $configured_roles = $this->configuration['block_group_roles'];

    // Load the current user.
    $current_user = \Drupal::currentUser();
    $user_entity = \Drupal\user\Entity\User::load($current_user->id());

    if (!$user_entity) {
      return FALSE; // No user entity, so the condition fails.
    }

    // Get group memberships for the user.
    $memberships = \Drupal::service('group.membership_loader')->loadByUser($user_entity);

    $user_roles = [];
    foreach ($memberships as $membership) {
      $roles = $membership->getRoles();

      foreach ($roles as $role) {
        if ($role instanceof GroupRole) {
          $user_roles[] = $role->id();
        }
      }
    }

    $matching_roles = array_intersect($user_roles, $configured_roles);
    return $matching_roles;



    // if ($request_attributes->has('node') && $request_attributes->get('_route') === 'entity.node.canonical') {
    //   $node = $request_attributes->get('node');
    //   $node_id = $node->id();
    //   return _potatopro_block_tag_get_nodes_by_termIds($termIds,$node_id);
    // }
    // elseif ($request_attributes->has('taxonomy_term') && $request_attributes->get('_route') === 'entity.taxonomy_term.canonical') {
    //   $taxonomy_term = $request_attributes->get('taxonomy_term');
    //   $taxonomy_term = $taxonomy_term->id();
    //   return _potatopro_block_tag_get_terms_by_termIds($termIds,$taxonomy_term);
    // }
    // else{
    //   return FALSE;
    // }

  }

}
