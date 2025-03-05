<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks that the submitted reservation is available.
 *
 * @Constraint(
 *   id = "TextConstraint",
 *   label = @Translation("Text Constraint"),
 *   type = "entity_reference"
 * )
 */
class TextConstraint extends Constraint
{
    public $invalid = '%label is invalid.';
}
