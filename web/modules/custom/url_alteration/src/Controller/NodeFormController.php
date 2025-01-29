<?php
namespace Drupal\url_alteration\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\node\NodeForm;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class NodeFormController.
 */
class NodeFormController extends ControllerBase {

  /**
   * The form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a NodeFormController object.
   *
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * Render the node creation form for a specific content type (e.g., 'page').
   *
   * @return array
   *   A render array for the node creation form.
   */
  public function renderForm() {
    // Create a new node of content type 'page'.
    $node = Node::create(['type' => 'page']);
    // dd($node);
    // Render the node creation form.
    $form = \Drupal::formBuilder()->getForm('Drupal\node\Form\NodeForm','add');
    // $form = \Drupal::service('form_builder')->getForm($node);
    // $this->render($form);
    // dd($form);
    return $form;
  }

  /**
   * Create the controller as a service.
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

}
