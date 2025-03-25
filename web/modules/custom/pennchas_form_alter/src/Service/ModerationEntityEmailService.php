<?php

namespace Drupal\pennchas_form_alter\Service;

use Drupal\node\Entity\Node;
use Drupal\pennchas_form_alter\Util\Constant;

/**
 * Handles email notifications for moderation entities.
 */
class ModerationEntityEmailService
{

    protected $mailer;

    public function __construct()
    {
        $this->mailer = \Drupal::service('easy_email.handler');
    }

    /**
     * Notifies the author about moderation.
     */
    public function notifyAuthor(string $emailTemplateId, Node $node, int|null $moderationWaitingDays = null)
    {
        $emailData = [
            'node' => $node
        ];
        if($moderationWaitingDays !== null) {
            $emailData['waitingDays'] = $moderationWaitingDays;
        }
        $this->sendMail($emailTemplateId, [$node->getOwnerId()], $emailData);
    }

    /**
     * Notifies moderators about a node update.
     */
    public function notifyModerators(string $emailTemplatId, Node $node, $groupIds = [])
    {
        $moderatorsId = \Drupal::service('pennchas_common.users')->usersWithPermission(Constant::PERMISSION_MODERATION, $groupIds);
        if ($moderatorsId) {
            $emailBatches = array_chunk($moderatorsId, 12);
            foreach ($emailBatches as $batch) {
                $this->sendMail($emailTemplatId, $batch, ['node' => $node]);
            }
        }
    }

    /**
     * Sends an email notification.
     */
    protected function sendMail($templateKey, $recipients, array $emailData)
    {
        try {
            $email = $this->mailer->createEmail(['type' => $templateKey]);
            if (!$email) {
                return;
            }
            $node = $emailData['node'];
            $email->set(
                $node->getType() === Constant::NODE_RESERVE_ROOM ? 'field_reserve_room' : 'field_event',
                $node
            );
            
            if ($templateKey === Constant::EVENT_EMAIL_MODERATOR_CREATED) {
                $feedbackUrl = \Drupal\Core\Url::fromRoute('event_feedback.page', [
                    'node' => $node->id(),
                ], ['absolute' => true])->toString();
                $email->set('field_url', $feedbackUrl);
                $qrCodePath = \Drupal::service('pennchas_form_alter.qr_code_generator')->generateQrCode($feedbackUrl);
                $email->set('field_image', $qrCodePath);
            } else if(isset($emailData['waitingDays'])) {
                $email->set('field_waiting_period', $emailData['waitingDays']);
            }
            $email->setRecipientIds($recipients);
            $this->mailer->sendEmail($email, [], true, true);
        } catch (\Exception $e) {
            \Drupal::logger('pennchas_form_alter')->error('Email sending failed: @message. Trace: @trace', [
                '@message' => $e->getMessage(),
                '@trace' => json_encode($e->getTraceAsString()),
            ]);
        }
    }
}
