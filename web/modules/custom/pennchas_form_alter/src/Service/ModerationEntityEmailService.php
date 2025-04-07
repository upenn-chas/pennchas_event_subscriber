<?php

namespace Drupal\pennchas_form_alter\Service;

use Drupal\group\Entity\Group;
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
    public function notifyAuthor(Node $node, string $emailTemplateId, Group|null $group)
    {
        $emailData = [
            'node' => $node
        ];
        if($group) {
            $emailData['waitingDays'] = $this->getHouseMaxModerationWaitingPeriod($group);
        }
        $this->sendMail($emailTemplateId, [$node->getOwnerId()], $emailData);
    }

    /**
     * Notifies moderators about a node update.
     */
    public function notifyModerators(Node $node, string $emailTemplatId, Group $group)
    {
        $moderatorEmails = $this->getModeratorEmails($group);
        if ($moderatorEmails) {
            foreach ($moderatorEmails as $batch) {
                $this->sendMail($emailTemplatId, $batch, ['node' => $node], FALSE);
            }
        }
    }

    private function getModeratorEmails(Group $group)
    {
        $emails = array_column($group->get('field_emails')->getValue(), 'value');
        return $emails ? array_chunk($emails, 12) : [];
    }

    private function getHouseMaxModerationWaitingPeriod(Group $group)
    {
        $houseMaxModerationWaitingPeriod = 3;
        $houseMaxModerationWaitingPeriod = $group->get('field_waiting_period')->getString();
        $houseMaxModerationWaitingPeriod = $houseMaxModerationWaitingPeriod ? (int) $houseMaxModerationWaitingPeriod : 3;

        return $houseMaxModerationWaitingPeriod;
    }


    /**
     * Sends an email notification.
     */
    private function sendMail(string $templateKey, array $recipients, array $emailData, bool $isRecipientIds = true)
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
            } else if (isset($emailData['waitingDays'])) {
                $email->set('field_waiting_period', $emailData['waitingDays']);
            }
            if ($isRecipientIds) {
                $email->setRecipientIds($recipients);
            } else {
                $email->setRecipientAddresses($recipients);
            }
            $this->mailer->sendEmail($email, [], true, true);
        } catch (\Exception $e) {
            \Drupal::logger('pennchas_form_alter')->error('Email sending failed: @message. Trace: @trace', [
                '@message' => $e->getMessage(),
                '@trace' => json_encode($e->getTraceAsString()),
            ]);
        }
    }
}
