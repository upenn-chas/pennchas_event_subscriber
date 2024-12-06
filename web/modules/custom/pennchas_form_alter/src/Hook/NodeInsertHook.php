<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

class NodeInsertHook
{
    function handle(NodeInterface $node)
    {
        $nodeType = $node->getType();
        if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        } else if ($nodeType === Constant::NODE_ROOM) {
            $this->handleRoom($node);
        }
    }

    protected function handleReserveRoom(Node $node)
    {
        $request = \Drupal::routeMatch();
        $group = $request->getParameter('group');
        $mailService = \Drupal::service('pennchas_form_alter.reserve_room_mail_service');
        $message = t('Your request has been submitted and there is a possible three day wait time for approval.');
        if ($node->get('moderation_state')->getString() === Constant::MOD_STATUS_PUBLISHED) {
            $message = t('Your request has been accepted.');
            $mailService->notifyCreated('et_room_reservation_approved', $node);
        } else {
            $mailService->notifyCreated('et_room_reservation_created', $node, $group, true);
        }
        \Drupal::messenger()->addStatus($message);
    }

    protected function handleRoom(Node $node)
    {
        $node->set('field_qr_code', $node->toUrl('canonical', ['absolute' => true])->toString());
        $node->setNewRevision(false);
        $node->save();
    }
}
