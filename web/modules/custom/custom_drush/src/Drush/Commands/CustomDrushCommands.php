<?php

namespace Drupal\custom_drush\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
final class CustomDrushCommands extends DrushCommands
{

  use AutowireTrait;

  /**
   * Constructs a CustomDrushCommands object.
   */
  public function __construct(
    private readonly Token $token,
  ) {
    parent::__construct();
  }

  /**
   * Command description here.
   */
  #[CLI\Command(name: 'custom_drush:update', aliases: ['cd-upd'])]
  // #[CLI\Argument(name: 'arg1', description: 'Argument description.')]
  // #[CLI\Option(name: 'option-name', description: 'Option description')]
  #[CLI\Usage(name: 'custom_drush:update foo', description: 'Usage description')]
  public function commandName($type)
  {
    $nids = \Drupal::entityQuery('node')
      ->accessCheck(FALSE)
      ->condition('type', $type)
      ->execute();
    $this->logger()->success('Found ' . count($nids) . ' nodes of type ' . $type);
    $nodes = Node::loadMultiple($nids);
    $count = 0;

    foreach ($nodes as $node) {
      $parentRef = $node->get('field_group_ref')->getString();
      $path = $node->get('path')->first();
      // Only update if missing or currently disabled.
      if (!$path || !$path->get('pathauto')->getValue() || !$parentRef) {
        $node->set('path', ['pathauto' => TRUE]);
        $node->setRevisionLogMessage('Updated pathauto flag for URL alias to autogenrate.');
        $node->setRevisionUserId(1);
        $node->save();
        $count++;
      }
    }
    $this->logger()->success('Updated ' . $count . ' nodes of type ' . $type);
  }
}
