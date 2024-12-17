<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\pennchas_form_alter\Util\Constant;

class EventFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $form['#attached']['library'][] = 'pennchas_form_alter/event_listeners';
        return $form;
    }
}
