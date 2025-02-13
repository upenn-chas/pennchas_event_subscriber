<?php

declare(strict_types = 1);

namespace Drupal\group_finder;

use Drupal\group\Entity\GroupInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for group finder plugin instances.
 */
interface GroupFinderInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Checks if the plugin can be applied.
   *
   * @return bool
   *   TRUE if the plugin is applicable, FALSE in other case.
   */
  public function isApplicable(): bool;

  /**
   * Get group found by the plugin.
   *
   * @return \Drupal\group\Entity\GroupInterface|null
   *   The group if exists, null otherwise.
   */
  public function getGroup(): ?GroupInterface;

}
