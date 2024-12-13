<?php

namespace Drupal\penchas_block_group_role_condition\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Block\BlockPluginInterface;

/**
 * Provides a custom block with a view and pagination.
 *
 * @Block(
 *   id = "custom_view_block",
 *   admin_label = @Translation("Custom View Block with Pagination"),
 *   category = @Translation("Custom")
 * )
 */
class CustomViewBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Load the view programmatically.
    $view = Views::getView('my_events'); // Replace with your actual view machine name.

    if ($view) {
      // Get the current page from the URL.
      $current_page = \Drupal::request()->query->get('page', 0); // Default to 0 if not set.

      // Set the page for the view to ensure pagination works.
      $view->setExposedInput(['page' => $current_page]);

      // Set the display for the view (default display).
      $view->setDisplay('default'); // Ensure the correct display is used.
      $view_output = $view->render();

      // Add the pagination to the rendered view.
      $pagination = $view->getPager()->getPagination();

      return [
        '#markup' => $view_output['#markup'] . $pagination,
      ];
    }

    return [];
  }

}
