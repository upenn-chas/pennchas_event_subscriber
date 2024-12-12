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
        if($node instanceof NodeInterface) {
            $moderationState = $formState->getValue('new_state');
            $message = $formState->getValue('revision_log');
            $nodeType = $node->getType();
            if($nodeType === Constant::NODE_RESERVE_ROOM) {
                $template = Constant::RESERVER_ROOM_EMAIL_MODERATION;
                if ($moderationState === 'published') {
                    $template = Constant::RESERVER_ROOM_EMAIL_APPROVED;
                } 
                $this->sendMail($template, $node,  $moderationState, $message);
            }
        }
    }

    protected function sendMail(string $template, Node $node, string $state, string $message)
    {
        try {
            $email = $this->mailer->createEmail([
                'type' => $template,
                'label' => $node->getTitle() . ' is ' . $state
            ]);
            if ($email) {
                $email->set('field_reserve_room', $node);
                $email->set('field_message', $message);
                $email->set('field_state', $state);
                $email->setRecipientIds([$node->getOwnerId()]);
                $this->mailer->sendEmail($email, [], true, true);
            }
        } catch (\Exception $e) {
            \Drupal::logger('pennchas_form_alter')->error($e->getMessage(), $e->getTrace());
        }
    }
}
