<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Room maximun allowed guests constraint
 * 
 * @Constraint (
 *  id = "RoomMaxGuestConstraint",
 *  label = @Translation("Room max guest constraint", context = "Validation"),
 *  type = "string"
 * )
 */
class RoomMaxGuestConstraint extends Constraint
{
    /**
     * Max guest limit exceed error message
     * 
     * @var string
     */
    public $maxOccupancyExceed = 'Maximum occupancy exceeded. Selected room allows a max. of %guestCount guests.';
}
