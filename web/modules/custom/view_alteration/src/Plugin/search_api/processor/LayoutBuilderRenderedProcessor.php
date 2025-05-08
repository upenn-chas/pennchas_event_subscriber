<?php

namespace Drupal\view_alteration\Plugin\search_api\processor;

use Drupal\Core\Entity\EntityInterface;
use Drupal\search_api\Datasource\DatasourceInterface;
use Drupal\search_api\Processor\ProcessorPluginBase;
use Drupal\search_api\Item\FieldInterface;
use Drupal\search_api\Item\ItemInterface;
use Drupal\search_api\Processor\ProcessorProperty;

/**
 * Adds rendered Layout Builder content to the index.
 *
 * @SearchApiProcessor(
 *   id = "layout_builder_rendered",
 *   label = @Translation("Layout Builder Rendered Content"),
 *   description = @Translation("Adds the rendered layout builder content to the index."),
 *   stages = {
 *     "add_properties" = 0,
 *   },
 *   locked = true,
 *   hidden = true,
 * )
 */
class LayoutBuilderRenderedProcessor extends ProcessorPluginBase
{


  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions(?DatasourceInterface $datasource = null)
  {
    $properties = [];

    if (!$datasource) {
      $definition = [
        'label' => $this->t('Layout Builder Rendered Content'),
        'description' => $this->t('The full rendered content from Layout Builder.'),
        'type' => 'string',
        'is_list' => FALSE,
        'processor_id' => $this->getPluginId(),
      ];
      $properties['layout_builder_rendered'] = new ProcessorProperty($definition);
    }

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function addFieldValues(ItemInterface $item)
  {
    $original_object = $item->getOriginalObject();

    if ($original_object && $original_object->getValue() instanceof EntityInterface) {
      $entity = $original_object->getValue();

      // Get the view builder for the entity type.
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());

      // Render the entity in the default or custom view mode.
      $render_array = $view_builder->view($entity, 'default');

      // Render to plain text (no markup).
      $rendered_text = \Drupal::service('renderer')->renderPlain($render_array);

      // Strip HTML tags as a safety net.
      $plain_text = strip_tags($rendered_text);

      // Add the computed field dynamically.
      $fields = $item->getFields();
      if (isset($fields['layout_builder_rendered'])) {
        $fields['layout_builder_rendered']->addValue($plain_text);
      }
    }
  }
}
