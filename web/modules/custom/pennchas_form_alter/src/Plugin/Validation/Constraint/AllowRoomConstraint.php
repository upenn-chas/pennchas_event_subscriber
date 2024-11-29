<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted reservation is available.
 *
 * @Constraint(
 *   id = "AllowRoomConstraint",
 *   label = @Translation("Allow Room Constraint"),
 *   type = "entity_reference"
 * )
 */
class AllowRoomConstraint extends Constraint
{
    public $notAuthentic = 'You are not allowed to book "%room" room.';
}
