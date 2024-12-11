<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;


class GeoCoordinatesConstraintValidator extends ConstraintValidator
{

    /**
     * Valid GPS pin format
     * 
     * @var string
     */
    protected $regex = '/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/';

    public function validate(mixed $value, Constraint $constraint)
    {
        $gpsPin = $value->getString();
        if($gpsPin && !preg_match($this->regex, $gpsPin)) {
            $this->context->addViolation($constraint->invalidGpsPin);
        }
    }
}
