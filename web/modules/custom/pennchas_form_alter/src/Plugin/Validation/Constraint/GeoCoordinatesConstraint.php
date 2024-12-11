<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * GEO coordinates validation constraint
 * 
 * @Constraint (
 *  id = "GeoCoordinatesConstraint",
 *  label = @Translation("Geo coordinates constraint"),
 *  type = "string"
 * )
 */
class GeoCoordinatesConstraint extends Constraint
{
    /**
     * Invalid GPS pin error message
     * 
     * @var string
     */
    public $invalidGpsPin = 'GPS pins is not valid or format is invalid.';
}
