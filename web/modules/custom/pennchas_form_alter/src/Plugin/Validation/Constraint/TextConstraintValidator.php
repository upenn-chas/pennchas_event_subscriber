<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates text constraints.
 */
class TextConstraintValidator extends ConstraintValidator
{
    /**
     * Validates if the value is valid or not.
     *
     * @param mixed $value
     *   The value being validated.
     * @param Constraint $constraint
     *   The constraint for the validation.
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        $definition = $value->getFieldDefinition();
        $text = $value->getString();
        if ($text && preg_replace('/^[^\p{L}\p{N}]+|[^\p{L}\p{N}]+$$/', '', $text) === '') {
            $this->context->addViolation($constraint->invalid, ['%label' => $definition->getLabel()]);
        }
    }
}
