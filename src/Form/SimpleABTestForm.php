<?php

namespace Drupal\simple_a_b\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class SimpleABTestForm extends FormBase {

  protected $_fieldPrepend = 'test_field_';

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

    $form['test'] = [
      '#type' => 'details',
      '#title' => t('Test information'),
      '#description' => t('The basic information used on the test.'),
      '#open' => TRUE,
    ];

    $form['test'][$this->_fieldPrepend . 'name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#description' => t('Administrative name'),
      '#required' => TRUE,
    ];

    $form['test'][$this->_fieldPrepend . 'description'] = [
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#description' => t('Administrative description'),
    ];

    $form['test'][$this->_fieldPrepend . 'type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#default_value' => '',
      '#options' => $this->getTypes(),
      '#description' => t('What type of entity to test'),
      '#required' => TRUE,
      //      '#ajax' => [
      //        // Function to call when event on form element triggered.
      //        'callback' => '::enable_event_trigger',
      //        // Effect when replacing content. Options: 'none' (default), 'slide', 'fade'.
      //        'effect' => 'fade',
      //        // Javascript event to trigger Ajax. Currently for: 'onchange'.
      //        'event' => 'click',
      //        'progress' => [
      //          // Graphic shown to indicate ajax. Options: 'throbber' (default), 'bar'.
      //          'type' => 'throbber',
      //          // Message to show along progress graphic. Default: 'Please wait...'.
      //          'message' => 'loading',
      //        ],
      //      ],
    ];

    $form['test'][$this->_fieldPrepend . 'eid'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'block_content',
      '#description' => t('The entity to apply the tests too'),
      '#default_value' => '',
      '#required' => TRUE,
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Add'),
      '#attributes' => ['class' => ['button--primary']],
    ];

    $form['actions']['preview'] = [
      '#type' => 'submit',
      '#value' => t('Preview'),
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
    $data = [];

    // loop thought all the get values
    // pulling out only the ones that have been set as values in the form above
    foreach ($form_state->getValues() as $key => $value) {
      if (strpos($key, $this->_fieldPrepend) !== FALSE) {
        $key = str_replace($this->_fieldPrepend, '', $key);
        $data[$key] = $value;
      }
    }

    // try to create a new test in the database
    $tid = \Drupal::service('simple_a_b.storage.test')->create($data);

    if ($tid === -1) {
      // if we don't get back a positive tid, display the error message
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('Error creating new test'), 'error');
    }
    else {
      // otherwise display positive message
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('New test "@name" has been created', ['@name' => $data['name']]), 'status');

      // and redirect back to viewing all tests
      $url = Url::fromRoute('simple_a_b.view_tests');
      $form_state->setRedirectUrl($url);
    }
  }

  protected function getTypes() {
    return [
      '_none' => t('- none -'),
      'block' => t('Block'),
    ];
  }
}
