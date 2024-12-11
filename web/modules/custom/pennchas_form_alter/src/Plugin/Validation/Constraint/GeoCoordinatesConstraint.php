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
    public $invalidGpsPin = 'Please enter valid coordinates in the format "latitude,longitude" (e.g., 37.7749,-122.4194).';
}
