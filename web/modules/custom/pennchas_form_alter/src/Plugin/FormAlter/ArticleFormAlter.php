<?php

namespace Drupal\pennchas_form_alter\Plugin\FormAlter;

use Drupal\Core\Form\FormStateInterface;

class ArticleFormAlter
{
    public function alter(array $form, FormStateInterface $formState)
    {
        $formId = $form['#form_id'];
        if($formId === 'node_article_form') {
            $group = \Drupal::routeMatch()->getParameter('group');
            if($group) {
                $form['field_location']['widget']['#default_value'] = [$group->id()];
                $form['field_location']['#access'] = FALSE;
            }
        }
        return $form;
    }
}
