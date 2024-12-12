<?php

namespace Drupal\node_form_extension\Plugin;

use Drupal\group\Entity\Group;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

class RoomReservationHandler
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = \Drupal::service('easy_email.handler');
    }

    public function handlePreSave(NodeInterface $node)
    {
        $request = \Drupal::routeMatch();
        $group = $request->getParameter('group');
        if ($this->canByPassModeration($group)) {
            $node->setPublished(true);
            $node->set('moderation_state', 'published');
        } else {
            $node->setPublished(false);
            $node->set('moderation_state', 'draft');
        }
    }

    public function handleInsert(Node $node)
    {
        $request = \Drupal::routeMatch();
        $group = $request->getParameter('group');
        $mailService = \Drupal::service('node_form_extension.mail_service');
        $message = t('Your request has been submitted and there is a possible three day wait time for approval.');
        if ($this->canByPassModeration($group)) {
            $message = t('Your request has been accepted.');
            $mailService->notifyCreated('et_md_room_reservation_created', $node);
        } else {
            $mailService->notifyCreated('et_room_reservation_created', $node, $group, true);
        }
        \Drupal::messenger()->deleteAll();
        \Drupal::messenger()->addStatus($message);
    }

    protected function canByPassModeration(Group|null $group): bool
    {
        $currentUser = \Drupal::currentUser();
        return ($group) ? $group->hasPermission('use editorial transition publish', $currentUser) : $currentUser->hasPermission('use editorial transition publish');
    }
}
