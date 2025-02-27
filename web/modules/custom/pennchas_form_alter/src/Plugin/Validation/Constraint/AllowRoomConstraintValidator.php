<?php

namespace Drupal\pennchas_form_alter\Plugin\Validation\Constraint;

use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates room reservation constraints.
 */
class AllowRoomConstraintValidator extends ConstraintValidator
{
    /**
     * Validates if the selected room can be booked by the current user.
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
        $selectRoom = $value->getValue();
        $roomId = $selectRoom[0]['target_id'];
        if (empty($roomId)) {
            return;
        }

        $room = Node::load($roomId);
        $currentUser = \Drupal::currentUser();

        // Allow administrators to book any room.
        if ($currentUser->hasRole('administrator')) {
            return;
        }

        if (!$this->isUserAutenticToBook($room, $currentUser)) {
            $this->context->addViolation($constraint->notAuthentic, ['%room' => $room->getTitle()]);
        }
    }

    /**
     * Determines if the current user is authorized to book the selected room.
     *
     * @param Node $room
     *   The room node.
     * @param AccountInterface $account
     *   The current user account.
     *
     * @return bool
     *   TRUE if the user is authorized, FALSE otherwise.
     */
    function isUserAutenticToBook(Node $room, AccountInterface $account)
    {

        $roomAvailableTo = $room->get('field_available_to')->getString();
        $state = \Drupal::state();

        /**
         * @var \Drupal\group\Entity\Group
         */
        $group = \Drupal::routeMatch()->getParameter('group');

        // Fetch user roles in the group, if applicable.
        if ($group && ($membership = $group->getMember($account))) {
            $roles = $membership->getRoles();
            $roles = array_keys($roles);
        } else {
            $roles = $account->getRoles();
        }

        // Check if any of the user's roles have permission for the selected room.
        foreach ($roles as $role) {
            $roleConfig = $state->get('custom_group_role_config_' . $role);
            if ($roleConfig && isset($roleConfig[$roomAvailableTo])) {
                return true;
            }
        }
        return false;
    }
}
