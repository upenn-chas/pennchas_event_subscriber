<?php

declare(strict_types = 1);

namespace Drupal\group_finder;

/**
 * Interface for group finder provider.
 */
interface GroupFinderProviderInterface {

  /**
   * Get applicable plugin.
   *
   * @return \Drupal\group_finder\GroupFinderInterface|null
   *   The applicable plugin.
   */
  public function get(): ?GroupFinderInterface;

}
