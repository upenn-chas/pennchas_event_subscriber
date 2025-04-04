<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks the submiited date and time .
 *
 */
class EventScheduleConstraintValidator extends ConstraintValidator
{

    public function validate(mixed $value, Constraint $constraint)
    {
        $entity = $this->context->getRoot()->getEntity();
        if($entity->hasField('field_event_ends_on')) {
            $eventEndsOn = (int) $entity->get('field_event_ends_on')->getString();
            if($eventEndsOn < time()) {
                return;
            }
        }
        $schedules = $value->getValue();
        $currentTime = time();

        foreach ($schedules as $schedule) {
            if ($currentTime >= $schedule['value']) {
                $this->context->addViolation($constraint->invalidStartDateTime);
            }

            if ($currentTime >= $schedule['end_value'] || $schedule['end_value'] <= $schedule['value']) {
                $this->context->addViolation($constraint->invalidEndDateTime);
            }
        }
    }
}
