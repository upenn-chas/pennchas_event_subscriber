<?php

namespace Drupal\common_utils\Access;

use Drupal\node\NodeInterface;

/**
 * Checks whether the event is ready for evaluation or not. 
 */
class EvaluationCheck
{

     /**
     * Checks whether the chas event can evaluate or not.
     *
     * @param Drupal\node\NodeInterface $node
     *   The node to check for.
     *
     * @return boolean
     *  
     */
    public function checkForEntity(NodeInterface $node)
    {
        if ($node->getType() !== 'chas_event' || !$node->get('status')->value) {
            return false;
        }
        $current_time = time();
        $event_schedule = (int) $node->get('field_event_ends_on')->getString();
        return $event_schedule < $current_time;
    }
}
