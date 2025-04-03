<?php

namespace Drupal\view_alteration\Plugin\views\field;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\EntityLink;
use Drupal\views\ResultRow;

/**
 * Provides a evaluate event link field.
 *
 * @ingroup views_field_handlers
 * 
 * @ViewsField("evaluate_content")
 * 
 */
class EvaluationLink extends EntityLink
{

    /**
     * {@inheritdoc}
     */
    protected function getUrlInfo(ResultRow $row)
    {
        $entity = $this->getEntity($row);
        $template = $this->getEntityLinkTemplate();
        if ($entity === NULL) {
            return NULL;
        }
        $access = $this->checkEvaluationAccess($entity);
        return $access ? Url::fromRoute($template, ['node' => $entity->id()]) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityLinkTemplate()
    {
        return 'view.event_evaluation_view.event_evaluate_page_view';
    }

    /**
     * {@inheritdoc}
     */
    protected function renderLink(ResultRow $row)
    {
        $this->options['alter']['query'] = $this->getDestinationArray();
        return parent::renderLink($row);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultLabel()
    {
        return $this->t('Evaluate');
    }

    /**
     * Checks whether the logged-in user has permission to evaluate and if the event is ready for evaluation.
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity to check.
     *
     * @return bool
     *   TRUE if the user has permission to evaluate and the event is ready, FALSE otherwise.
     */
    private function checkEvaluationAccess(EntityInterface $entity)
    {
        if(!$entity->isPublished()) {
            return AccessResult::forbidden();
        }
        return \Drupal::service('pennchas_common.evaluation_check')->checkForEntity($entity);
    }
}