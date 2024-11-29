<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Checks that the submitted reservation is available.
 *
 */
class AllowRoomConstraintValidator extends ConstraintValidator
{
    /**
     * Checks if the passed value is valid.
     *
     * @return void
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        $selectRoom = $value->getValue();
        $roomId = $selectRoom[0]['target_id'];
        $room = Node::load($roomId);
        $currentUser = \Drupal::currentUser();
        if ($currentUser->hasRole('administrator')) {
            return;
        }
        if(!$this->isUserAutenticToBook($room, $currentUser)) {
            $this->context->addViolation($constraint->notAuthentic, ['%room' => $room->getTitle()]);
        }

    }

    /**
     * Check if current user is authentic to book selected room.
     */
    function isUserAutenticToBook(Node $room, $currentUser)
    {

        $roomAvailableTo = $room->get('field_available_to')->getString();
        
        $group = \Drupal::routeMatch()->getParameter('group');
        $member = $group->getMember($currentUser);
        $roles = [];
        if ($member) {
            $roles = $member->getRoles();
        } else {
            $roles = Role::loadMultiple($currentUser->getRoles(true));
        }
        $isAuthentic = false;
        foreach ($roles as $role) {
            $umberaRoles = $role->getThirdPartySetting('umbera_roles', 'target_ids');
            if ($umberaRoles && isset($umbera_roles[$roomAvailableTo])) {
                $isAuthentic = true;
                break;
            }
        }
        return $isAuthentic;
    }
}
