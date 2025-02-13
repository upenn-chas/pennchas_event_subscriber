<?php

declare(strict_types = 1);

namespace Drupal\group_finder\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a group_finder annotation object.
 *
 * @see plugin_api
 *
 * @Annotation
 */
class GroupFinder extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the group finder plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the group finder.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description = '';

  /**
   * The plugin weight.
   *
   * The weight of the plugin, the higher the number, the later the founder
   * is being called.
   *
   * @var int
   */
  public $weight = 0;

}
