<?php

namespace Drupal\node_event_form_ext\Service;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use Exception;

class EventEmailService
{

    protected $mailer;
    protected $template_manager;
    protected $logger;

    public function __construct()
    {
        $this->mailer = \Drupal::service('easy_email.handler');
    }

    public function notify(Node $node)
    {
        $this->sendMail($node, 'et_new_event_host_notification', [$node->getOwnerId()], 'New event: ');
        $this->sendEmailToModerators($node);
    }

    public function notifyOwnerAboutModeration(Node $node, string $state)
    {
        if ($state === 'published') {
            $this->sendEventApprovalEmail($node);
        } else {
            $this->sendModerationMail($node, $state);
        }
    }

    protected function sendEmailToModerators(Node $node)
    {
        $globalModeratorsId = $this->getUsersIdWithModerationPermission();
        $groupModeratorsId = $this->getGroupModerators('house1', array_column($node->get('field_college_houses')->getValue(), 'target_id'));
        $moderatorsId = array_unique(array_merge($globalModeratorsId, $groupModeratorsId));
        if ($moderatorsId) {
            $this->sendMail($node, 'et_new_event_mod_notification', $moderatorsId, 'Moderation: ');
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
            if ($roleKey !== $groupTypeKey . '-admin' && $groupRole->hasPermission('use editorial transition publish')) {
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
            if ($key !== 'administrator' && $role->hasPermission('use editorial transition publish')) {
                $moderatorRoles[] = $key;
            }
        }
        return $moderatorRoles;
    }

    protected function sendMail(Node $node, $templateKey, $recipients, $labelPrefix)
    {
        try {
            $email = $this->mailer->createEmail([
                'type' => $templateKey,
                'label' => $labelPrefix . $node->getTitle()
            ]);
            if ($email) {
                $email->set('field_event', $node);
                $email->setRecipientIds($recipients);
                $this->mailer->sendEmail($email, [], true, true);
            }
        } catch (Exception $e) {
            \Drupal::logger('Node Event Form Ext')->error($e->getMessage(), $e->getTrace());
        }
    }

    protected function sendModerationMail(Node $node, $state)
    {
        try {
            $email = $this->mailer->createEmail([
                'type' => 'et_moderation_state_change_notif',
                'label' => $node->getTitle() . ' is ' . $state
            ]);
            if ($email) {
                $email->set('field_event', $node);
                $email->set('field_state', $state);
                $email->setRecipientIds([$node->getOwnerId()]);
                $this->mailer->sendEmail($email, [], true, true);
            }
        } catch (Exception $e) {
            \Drupal::logger('Node Event Form Ext')->error($e->getMessage(), $e->getTrace());
        }
    }

    protected function sendEventApprovalEmail(Node $node)
    {
        try {
            $email = $this->mailer->createEmail([
                'type' => 'et_event_approved',
                'label' => $node->getTitle() . ' is approved'
            ]);
            if ($email) {
                $email->set('field_event', $node);
                $qrCodeGenerator = \Drupal::service('node_event_form_ext.qr_code_generator');
                $qrCodePath = $qrCodeGenerator->generateQrCode($node->toUrl()->toString(). '/feedback');
                $email->set('field_qr_code', $qrCodePath);
                $email->setRecipientIds([$node->getOwnerId()]);
                $this->mailer->sendEmail($email, [], true, true);
            }
        } catch (Exception $e) {
            \Drupal::logger('Node Event Form Ext')->error($e->getMessage(), $e->getTrace());
        }
    }

    
}
