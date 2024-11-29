<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted reservation is available.
 *
 * @Constraint(
 *   id = "RoomAvailableConstraint",
 *   label = @Translation("Room Available Constraint"),
 * )
 */
class RoomAvailableConstraint extends Constraint
{
    public $noSlotAvailble = 'Room is not available for the select date and time.';
}