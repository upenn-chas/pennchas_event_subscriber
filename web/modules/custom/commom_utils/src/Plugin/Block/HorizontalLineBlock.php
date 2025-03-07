<?php

namespace Drupal\common_utils\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Horizontal Line Block.
 *
 * @Block(
 *   id = "horizontal_line_block",
 *   admin_label = @Translation("Horizontal Line"),
 *   category = @Translation("Custom")
 * )
 */
class HorizontalLineBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<hr>',
      '#allowed_tags' => ['hr']
    ];
  }

}
