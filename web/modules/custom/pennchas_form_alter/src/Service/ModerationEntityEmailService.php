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

    public function notifyModerators(string $emailTemplatId, Node $node, $groupIds = [])
    {
        $moderatorsId = \Drupal::service('pennchas_common.users')->usersWithPermission(Constant::PERMISSION_MODERATION, $groupIds);
        if ($moderatorsId) {
            $emailBatches = array_chunk($moderatorsId, 12);
            foreach ($emailBatches as $batch) {
                $this->sendMail($node, $emailTemplatId, $batch);
            }
        }
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
