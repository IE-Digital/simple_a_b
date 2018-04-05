<?php

namespace Drupal\simple_a_b\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Defines a form that configures devel settings.
 */
class SimpleABSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_a_b_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'simple_a_b.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $simple_a_b_config = $this->config('simple_a_b.settings');
    $form['enabled'] = [
      '#type' => 'radios',
      '#title' => $this->t('Enable Simple A/B'),
      '#options' => [1 => 'Enabled', 0 => 'Disabled'],
      '#default_value' => $simple_a_b_config->get('enabled'),
      '#description' => t('This toggle will stop all tests from running.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('simple_a_b.settings')
      ->set('enabled', $values['enabled'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
