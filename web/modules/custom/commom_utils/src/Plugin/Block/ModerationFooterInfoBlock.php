<?php

namespace Drupal\common_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\node\NodeInterface;

/**
 * Provides a 'Moderation Footer Info' block.
 *
 * @Block(
 *   id = "moderation_footer_info_block",
 *   admin_label = @Translation("Moderation Footer Info"),
 *   category = @Translation("Custom")
 * )
 */
class ModerationFooterInfoBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $node = \Drupal::routeMatch()->getParameter('node');
    $period = '3 days';
    if ($node && $node instanceof NodeInterface && $node->getType() === 'reserve_room') {
      $houseId = (int)$node->get('field_group')->getString();
      $group = \Drupal\group\Entity\Group::load($houseId);
      $waitingPeriod = $group->get('field_waiting_period')->getString();
      $waitingPeriod = $waitingPeriod ? (int)$waitingPeriod : 3;
      $period = $waitingPeriod > 1 ? $waitingPeriod . ' days' : '1 day';
    }
    return [
      '#markup' => '<div class="foot-info"><span><strong>Providing confirmation of this reservation can take up to ' . $period . '.</strong></span><br><span>Please check your email for your confirmation.</span></div>',
      '#allowed_tags' => ['span', 'strong', 'br', 'div', ]
    ];
  }
}
