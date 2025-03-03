<?php

namespace Drupal\penchas_block_group_role_condition\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;
use Drupal\Core\Translation\TranslatableMarkup;
use Drupal\Core\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;

/**
 * Provides a 'BlockCountForModerator' block.
 *
 * @Block(
 *   id = "moderator_node_count_block",
 *   admin_label = @Translation("Moderator Node Count Block"),
 *   category = @Translation("Custom")
 * )
 */
class BlockCountForModerator extends BlockBase {

  /**
   * The database connection service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new BlockCountForModerator instance.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerInterface $container
   *   The service container.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $count = $this->nodeCountForModerator('chas_event');
    if ($count) {
      return [
        '#markup' => $count,
      ];
    }
    return [
      '#markup' => '',
    ];
  }

  /**
   * Custom function to count nodes for moderators.
   */
  public function nodeCountForModerator(string $type) {
    // Get user groups with editorial transition permissions.
    $groups = \Drupal::service('pennchas_common.option_group')->getUserGroupsWithPermission('use editorial transition publish');

    if (!count($groups)) {
      return NULL;
    }

    // Query to get the count of nodes in draft or pending moderation states.
    $query = $this->database->select('content_moderation_state_field_data', 'ms');
    $query->fields('ms', ['content_entity_id']);
    $query->leftJoin('node__field_groups', 'nfg', 'nfg.entity_id = ms.content_entity_id');
    $query->condition('nfg.field_groups_target_id', array_keys($groups), 'IN');
    $query->condition('ms.moderation_state', ['draft', 'pending'], 'IN');
    $query->condition('nfg.bundle', $type, '=');
    $result = $query->distinct()->countQuery()->execute()->fetchCol();

    if ($result[0] && $result[0] > 0) {
      return new TranslatableMarkup('<div class="info-fields"><p>You have @count events for moderation. Go to <a href="@goToUrl">My Events</a>.</p></div>', [
        '@count' => $result[0],
        '@goToUrl' => Url::fromRoute('view.my_events.page_1')->toString(),
      ]);
    }
    return NULL;
  }
}
