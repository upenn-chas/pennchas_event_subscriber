<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks .
 *
 * @Constraint(
 *   id = "EventScheduleConstraint",
 *   label = @Translation("Event Schedule Constraint"),
 *   type = "entity_reference"
 * )
 */
class EventScheduleConstraint extends Constraint
{
    public $invalidStartDateTime = 'Start date and time must be in the future';

    public $invalidEndDateTime = 'End date and time must be in the future and later than the start date and time.';
    
}
