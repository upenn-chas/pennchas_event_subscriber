<?php

namespace Drupal\houses_custom_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'Find House Custom Block' block.
 *
 * @Block(
 *   id = "houses_custom_blocks",
 *   admin_label = @Translation("Find House Custom Block"),
 *   category = @Translation("Custom")
 * )
 */
class FindHouseCustomBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => $this->t('Hello, this is my custom block!'),
    ];
  }

  /**
   * {@inheritdoc}
   * Block configuration form
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Add a custom setting to the block configuration form.
    $config = $this->getConfiguration();
    $form['custom_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Custom Message'),
      '#default_value' => isset($config['custom_message']) ? $config['custom_message'] : $this->t('Default Message'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   * Save block configuration
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->setConfigurationValue('custom_message', $form_state->getValue('custom_message'));
  }

  /**
   * {@inheritdoc}
   */
  // public function build() {
  //   // Get block configuration values.
  //   $config = $this->getConfiguration();
  //   return [
  //     '#markup' => $this->t($config['custom_message']),
  //   ];
  // }

}
