<?php 
namespace Drupal\penchas_custom_configs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\Response;

class HeadingController extends ControllerBase {

  /**
   * Displays a custom heading page.
   *
   * @param string $heading_id
   *   The ID of the heading.
   *
   * @return \Drupal\Core\GeneratedUrl
   *   A render array to display the page.
   */
  public function headingPage($heading_id) {
    // Load the heading content or retrieve based on $heading_id
    // For this example, let's assume $heading_id is the text of the heading.

    $output = [
      '#type' => 'markup',
      '#markup' => $this->t('Custom Heading: @heading', ['@heading' => $heading_id]),
    ];

    return $output;
  }
}
