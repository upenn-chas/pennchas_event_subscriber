<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Checks whether the submitted email data have unique emails for all collaborators.
 *
 * @Constraint(
 *   id = "UniqueCollaboratorsEmailConstraint",
 *   label = @Translation("Unique Collaborators Email Constraintt"),
 *   type = "entity_reference"
 * )
 */
class UniqueCollaboratorsEmailConstraint extends Constraint
{
    public $notUnique = 'The %label have one or more same email addreses.';
}

