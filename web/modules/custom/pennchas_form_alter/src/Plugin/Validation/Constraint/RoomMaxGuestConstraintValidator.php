<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class RoomMaxGuestConstraintValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint)
    {
        $roomId = (int) $this->context->getRoot()->get('field_room')->getString();
        $room = Node::load($roomId);
        $roomMaxOccupancy = (int) $room->get('field_maximum_occupancy')->getString();
        $guest = (int) $value->getString();

        if ($guest > $roomMaxOccupancy) {
            $this->context->addViolation($constraint->maxOccupancyExceed, [
                '%guestCount' => $roomMaxOccupancy
            ]);
        }
    }
}
