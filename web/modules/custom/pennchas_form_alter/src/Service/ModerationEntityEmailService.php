<?php

namespace Drupal\pennchas_form_alter\Service;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\node\Entity\Node;
use Drupal\pennchas_form_alter\Util\Constant;
use Drupal\user\Entity\Role;
use Exception;

class ModerationEntityEmailService
{

    protected $mailer;

    public function __construct()
    {
        $this->mailer = \Drupal::service('easy_email.handler');
    }

    public function notifyAuthor(string $emailTemplatId, Node $node)
    {
        $this->sendMail($node, $emailTemplatId, [$node->getOwnerId()]);
    }

    public function notifyModerators(string $emailTemplatId, Node $node, Group|null $group)
    {
        $globalModeratorsId = $this->getUsersIdWithModerationPermission();
        $groupModeratorsId = $group ? $this->getGroupModerators('house1', [$group->id()]) : [];
        $moderatorsId = array_unique(array_merge($globalModeratorsId, $groupModeratorsId));
        if ($moderatorsId) {
            $this->sendMail($node, $emailTemplatId, $moderatorsId);
        }
    }

    protected function getUsersIdWithModerationPermission()
    {
        $roleWithModerationPermission = $this->getRolesWithModerationPermission();

        if ($roleWithModerationPermission) {
            $users = \Drupal::entityQuery('user')
                ->condition('status', 1)
                ->condition('roles', $roleWithModerationPermission, 'IN')
                ->accessCheck(false)
                ->execute();
            return $users ? array_keys($users) : [];
        }
        return [];
    }

    protected function getGroupModerators($groupTypeKey, $groupIds)
    {
        if (!$groupIds) {
            return [];
        }

        $groupType = GroupType::load($groupTypeKey);
        $groupRoles = $groupType->getRoles(false);
        $moderatorRoles = [];

        foreach ($groupRoles as $roleKey => $groupRole) {
            if ($roleKey !== $groupTypeKey . '-admin' && $groupRole->hasPermission(Constant::PERMISSION_MODERATION)) {
                $moderatorRoles[] = $roleKey;
            }
        }

        $groupModeratorsId = [];

        $groups = Group::loadMultiple($groupIds);
        foreach ($groups as $group) {
            $members = $group->getMembers($moderatorRoles);
            foreach ($members as $member) {
                $groupModeratorsId[] = $member->getUser()->id();
            }
        }
        return $groupModeratorsId;
    }

    protected function getRolesWithModerationPermission()
    {
        $roles = Role::loadMultiple();

        $moderatorRoles = [];
        foreach ($roles as $key => $role) {
            if ($role->hasPermission(Constant::PERMISSION_MODERATION)) {
                $moderatorRoles[] = $key;
            }
        }
        return $moderatorRoles;
    }

    protected function sendMail(Node $node, $templateKey, $recipients)
    {
        try {
            $email = $this->mailer->createEmail([
                'type' => $templateKey,
            ]);
            if ($email) {
                $nodeType = $node->getType();
                $email->set($nodeType === Constant::NODE_RESERVE_ROOM ? 'field_reserve_room' : 'field_event', $node);
                $email->setRecipientIds($recipients);
                $this->mailer->sendEmail($email, [], true, true);
            }
        } catch (Exception $e) {
            \Drupal::logger('pennchas_form_alter')->error($e->getMessage(), $e->getTrace());
        }
    }
}
