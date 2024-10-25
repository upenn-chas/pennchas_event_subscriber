<?php
namespace Drupal\find_your_house\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\group\Entity\GroupType;

class AdminFindYourHouse extends ConfigFormBase {

    /**
     *  {@inheritDoc}
     */
    public function getEditableConfigNames() {
        return ["find_your_house.settings"];
    }

    /**
     *  {@inheritDoc}
     */
    public function getFormId() {
        return "admin_find_your_house";
    }

    /**
     *  {@inheritDoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state) {
        $config = $this->config('find_your_house.settings');
        print_r($config->get());
        $selected_group_types = $config->get('group_types') ?: [];

        $form['go_button'] = [
            '#type' => 'textfield',
            '#id' => 'admin_find_your_house_go_button',
            '#title' => $this->t('Go Button Label'),
            '#description' => $this->t('Go button text.'),
            '#value' => $config->get('go_button'),
            '#required' => true
        ];

        $group_types = GroupType::loadMultiple();
        $options = [];
        foreach ($group_types as $group_type) {
            $options[$group_type->id()] = $group_type->label();
        }

        $form['group_types'] = [
            '#type' => 'checkboxes',
            '#title' => $this->t('Group Types'),
            '#description' => $this->t('Select the group types.'),
            '#options' => $options,
            '#default_value' => $selected_group_types,
            '#required' => true
        ];

        $form['link_text'] = [
            '#type' => 'textfield',
            '#id' => 'admin_find_your_house_link_text',
            '#title' => $this->t('Text'),
            '#description' => $this->t('Link text'),
            '#value' => $config->get('link_text'),
            '#required' => true
        ];

        $form['link_url'] = [
            '#type' => 'url',
            '#id' => 'admin_find_your_house_link_url',
            '#title' => $this->t('URL'),
            '#description' => $this->t('Link URL'),
            '#value' => $config->get('link_url'),
            '#required' => true
        ];

        // Added to disbale cache on form submit
        $form['#cache'] = ['max-age' => 0];
        // $form['#cache']['tags'][] = 'CACHE_MISS_IF_UNCACHEABLE_HTTP_METHOD:form';

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritDoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state) {
        // dpm($form_state->getValues());
        $this->config('find_your_house.settings')
        ->set('go_button', $form_state->getValue('go_button', 'Go'))
        ->set('group_types', $form_state->getValue('group_types'))
        ->set('link_text', $form_state->getValue('link_text', 'Discover all our houses'))
        ->set('link_url', $form_state->getValue('link_url', '/'))
        ->save();

        parent::submitForm($form, $form_state);
    }
}
