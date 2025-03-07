<?php

namespace Drupal\view_alteration\Plugin\views\field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;
use Drupal\views\Attribute\ViewsField;
use Drupal\views\Plugin\views\field\EntityLink;
use Drupal\views\ResultRow;

/**
 * Provides a moderation link field.
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
        $entity = $this->getEntity($row);
        $template = $this->getModerationLinkTemplate($entity);
        if ($entity === NULL || $template === NULL) {
            return NULL;
        }
        $access = \Drupal::service('pennchas_common.moderator_access_check')->checkForEntity($entity);
        return $access ? Url::fromRoute($template, ['node' => $entity->id()]) : '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEntityLinkTemplate()
    {
        return NULL;
    }

    /**
     * Returns the entity link template name identifying the moderation page link route.
     * 
     *
     * @param \Drupal\Core\Entity\EntityInterface $entity
     *   The entity for which the moderation link is to generate.
     *
     * @return string|NULL
     *   The moderation route link name.
     */
    protected function getModerationLinkTemplate(EntityInterface $entity)
    {
        $type = $entity->bundle();
        return match ($type) {
            'chas_event' => 'view.event_moderation_view.event_moderate_page_view',
            'reserve_room' => 'view.reserve_room_moderation_view.page_1',
            default => NULL
        };
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
