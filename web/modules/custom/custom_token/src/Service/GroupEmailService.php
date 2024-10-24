<?php

namespace Drupal\custom_token\Service;

use Drupal\group\Entity\Group;
use Drupal\group\Entity\GroupType;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\Role;
use Exception;

class GroupEmailService
{

    protected $mailer;
    protected $template_manager;
    protected $logger;

    public function __construct()
    {
        $this->mailer = \Drupal::service('easy_email.handler');
    }

    public function notify($entity)
    {
        $this->sendMail($entity, 'group_update_notification', [$entity], 'Group Update: ');
    }

    protected function sendMail($entity, $templateKey, $recipients, $labelPrefix)
    {
      try {
          $email = $this->mailer->createEmail([
              'type' => $templateKey,
              'label' => $labelPrefix . $entity->label->value
          ]);

          if ($email) {
              $email->set('field_group_', $entity);
              $email->setRecipientIds('test.ckln@yopmail.com');
              $this->mailer->sendEmail($email, [], true, true);
          }
      } catch (Exception $e) {
          dd($e);
      }
    }
}
