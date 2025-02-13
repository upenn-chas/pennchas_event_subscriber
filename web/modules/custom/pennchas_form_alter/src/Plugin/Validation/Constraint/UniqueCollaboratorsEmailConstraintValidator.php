<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\Role;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks the submiited collaborators have unique email address.
 *
 */
class UniqueCollaboratorsEmailConstraintValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        $collaboratorsEmailIds = $value->getValue();
        $temp = [];
        $unique = true;
        foreach($collaboratorsEmailIds as $collaboratorEmailId) {
            $email = trim($collaboratorEmailId['value']);
            if(isset($temp[$email])) {
                $unique = false;
                break;
            }
            $temp[$email] = 1;
        }
        if(!$unique) {
            $this->context->addViolation($constraint->notUnique, ['%label' => $value->getFieldDefinition()->label()]);
        }

    }
}
