<?php

namespace Drupal\pennchas_form_alter\Hook;

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

class NodeUpdateHook
{
    function handle(NodeInterface $node)
    {
        $nodeType = $node->getType();
        if ($nodeType === Constant::NODE_RESERVE_ROOM) {
            $this->handleReserveRoom($node);
        }
    }

    protected function handleReserveRoom(Node $node)
    {
        $oldModerationState = $node->original->get('moderation_state')->getString();
        $newModerationState = $node->get('moderation_state')->getString();

        if ($oldModerationState !== $newModerationState) {
            $mailService = \Drupal::service('pennchas_form_alter.reserve_room_mail_service');
            $mailService->notifyOwnerAboutModeration($node, $newModerationState);
        }
    }
}
