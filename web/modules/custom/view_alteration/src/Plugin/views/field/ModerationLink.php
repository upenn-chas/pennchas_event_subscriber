<?php

namespace Drupal\view_alteration\Plugin\views\field;

use Drupal\Core\Url;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\EntityLink;
use Drupal\views\ResultRow;

/**
 * Provides a moderate event link field.
 *
 * @ingroup views_field_handlers
 * 
 * @ViewsField("moderate_content")
 * 
 */
class ModerationLink extends EntityLink
{

    /**
     * {@inheritdoc}
     */
    protected function getUrlInfo(ResultRow $row)
    {
        $template = $this->getEntityLinkTemplate();
        $entity = $this->getEntity($row);
        if ($entity === NULL) {
            return NULL;
        }
        $access = \Drupal::service('pennchas_common.moderator_access_check')->checkForEntity($entity);
        return $access? Url::fromRoute($template, ['node' => $entity->id()]) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityLinkTemplate()
    {
        return 'view.event_moderation_view.event_moderate_page_view';
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
        return $this->t('Moderate');
    }

    /**
     * {@inheritdoc}
     */
    protected function defineOptions()
    {
        $options = parent::defineOptions();
        $options['category'] = ['default' => 'Custom'];
        return $options;
    }
}
