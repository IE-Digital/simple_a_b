<?php

namespace Drupal\simple_a_b\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SimpleABTestForm extends FormBase {

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'simple_a_b_test';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['test_name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#required' => TRUE,
    ];

    $form['test_description'] = [
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#description' => t('Administrative description'),
    ];

    $form['test_type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#default_value' => '',
      '#options' => $this->getTypes(),
      '#description' => t('The kind of test we are going to create.'),
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add'),
    ];

    return $form;
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($form_state->getValues() as $key => $value) {
      drupal_set_message($key . ': ' . $value);
    }
  }

  private function getTypes() {
    return [
      '_none' => t('- none -'),
      'block' => t('Block'),
    ];
  }
}
