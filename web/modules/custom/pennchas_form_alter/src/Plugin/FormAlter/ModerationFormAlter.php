<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

class ModerationFormAlter
{
    protected $mailer;

    public function __construct()
    {
        $this->mailer = \Drupal::service('easy_email.handler');
    }

    public function alter(array $form, FormStateInterface $formState)
    {
        if ($form['current']['#markup'] === 'Pending') {
            $form['new_state']['#default_value'] = 'pending';
            $form['new_state']['#options']['pending'] = 'Pending';
        }
        $form['#submit'][] = [$this, 'moderationFormSubmit'];

        return $form;
    }

    public function moderationFormSubmit(array $form, FormStateInterface $formState)
    {
        $node = $formState->get('entity');
        if ($node instanceof NodeInterface) {
            $moderationState = $formState->getValue('new_state');
            $message = $formState->getValue('revision_log');
            $nodeType = $node->getType();
            if ($nodeType === Constant::NODE_RESERVE_ROOM) {
                $template = Constant::RESERVER_ROOM_EMAIL_MODERATION;
                if ($moderationState === Constant::MOD_STATUS_PUBLISHED) {
                    $template = Constant::RESERVER_ROOM_EMAIL_APPROVED;
                }
                $this->sendMail($node, $template, [
                    'field_reserve_room' => $node,
                    'field_message' => $message,
                    'field_state' => $moderationState
                ]);
            } else if ($nodeType === Constant::NODE_EVENT) {
                $emailFields = [
                    'field_event' => $node,
                    'field_message' => $message,
                    'field_state' => $moderationState
                ];
                $template = Constant::EVENT_EMAIL_MODERATION;
                if ($moderationState === Constant::MOD_STATUS_PUBLISHED) {
                    $template = Constant::EVENT_EMAIL_APPROVED;
                    $feedbackUrl = \Drupal\Core\Url::fromRoute('event_feedback.page', [
                        'node' => $node->id(),
                    ], ['absolute' => true])->toString();
                    $emailFields['field_url'] = $feedbackUrl;
                    $qrCodePath = \Drupal::service('pennchas_form_alter.qr_code_generator')->generateQrCode($feedbackUrl);
                    $emailFields['field_image'] = $qrCodePath;
                }
                $this->sendMail($node, $template, $emailFields);
            }
        }
    }

    protected function sendMail(Node $node, string $template, array $attributes)
    {
        try {
            $email = $this->mailer->createEmail([
                'type' => $template,
            ]);
            if ($email) {
                if ($attributes) {
                    foreach ($attributes as $key => $value) {
                        $email->set($key, $value);
                    }
                }
                $email->setRecipientIds([$node->getOwnerId()]);
                $this->mailer->sendEmail($email, [], true, true);
            }
        } catch (\Exception $e) {
            \Drupal::logger('pennchas_form_alter')->error($e->getMessage(), $e->getTrace());
        }
    }
}
