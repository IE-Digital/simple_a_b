<?php

namespace Drupal\simple_a_b\Form;

use Drupal\block_content\Entity\BlockContent;
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
   * @param null $tid a tid used for edits
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL) {

    // try to load the tid
    // this is used for if we are using the form in edit mode
    $loaded_test = $this->loadData($tid);
    $edit_mode = isset($loaded_test['name']) ? TRUE : FALSE;

    $form['test'] = [
      '#type' => 'details',
      '#title' => t('Test information'),
      '#description' => t('Administrative information.'),
      '#open' => TRUE,
    ];

    $form['test'][$this->_fieldPrepend . 'name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#description' => t('Administrative name'),
      '#default_value' => $this->_isset($loaded_test['name']),
      '#required' => TRUE,
    ];

    $form['test'][$this->_fieldPrepend . 'description'] = [
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#default_value' => $this->_isset($loaded_test['description']),
      '#description' => t('Administrative description'),
    ];

    $form['test'][$this->_fieldPrepend . 'type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#default_value' => $this->_isset($loaded_test['type']),
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
      '#default_value' => BlockContent::load($this->_isset($loaded_test['eid'], 0)),
      '#required' => TRUE,
    ];

    $form['test'][$this->_fieldPrepend . 'enabled'] = [
      '#type' => 'radios',
      '#title' => t('Enabled'),
      '#description' => t('Enable or disable this test'),
      '#default_value' => $this->_isset($loaded_test['enabled'], 0),
      '#options' => [
        1 => t('Yes'),
        0 => t('No'),
      ],
      '#required' => TRUE,
    ];

    // hidden flag to check of edit mode
    $form['edit_mode'] = [
      '#type' => 'hidden',
      '#value' => $edit_mode ? 'true' : 'false',
    ];

    // if in edit mode set the tid to be hidden
    if ($edit_mode) {
      $form[$this->_fieldPrepend . 'tid'] = [
        '#type' => 'hidden',
        '#value' => $this->_isset($loaded_test['tid']),
      ];
    }

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $edit_mode ? t('Update') : t('Add'),
      '#attributes' => ['class' => ['button--primary']],
    ];

    $form['actions']['preview'] = [
      '#type' => 'submit',
      '#value' => t('Preview'),
    ];

    if ($edit_mode) {
      $form['actions']['delete'] = [
        '#markup' => "<a href='/admin/config/user-interface/simple-a-b/".$tid."/delete' class='button button--danger'>".t('Delete')."</a>",
        '#allowed_tags' => ['a'],
      ];
    }

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
    $edit_mode = FALSE;

    // loop thought all the get values
    // pulling out only the ones that have been set as values in the form above
    foreach ($form_state->getValues() as $key => $value) {

      // find all the rest of the form data
      if (strpos($key, $this->_fieldPrepend) !== FALSE) {
        $key = str_replace($this->_fieldPrepend, '', $key);
        $data[$key] = $value;
      }

      // setup edit mode
      if ($key === 'edit_mode') {
        $edit_mode = ($value === 'true') ? TRUE : FALSE;
      }
    }

    // if we are not trying to edit
    // we will try and create!
    if (!$edit_mode) {
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
    else {
      // set tid and remove it from the $data array
      $tid = $data['tid'];
      unset($data['tid']);

      // try to update the existing test
      $update = \Drupal::service('simple_a_b.storage.test')
        ->update($tid, $data);


      // if status is not true then error
      if ($update != TRUE) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('Error updating test'), 'error');
      }
      else {
        // otherwise display positive message
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('"@name" has been updated', ['@name' => $data['name']]), 'status');

        // and redirect back to viewing all tests
        $url = Url::fromRoute('simple_a_b.view_tests');
        $form_state->setRedirectUrl($url);
      }
    }
  }

  /**
   * load a tests information used for amending edits
   *
   * @param null $tid
   *
   * @return array
   */
  protected function loadData($tid = NULL) {
    $output = [];

    // if there is no tid
    // then simply return empty array
    if ($tid === NULL) {
      return $output;
    }

    // otherwise run a fetch looking up the test id
    $tests = \Drupal::service('simple_a_b.storage.test')->fetch($tid);

    // if we find any tests
    // set it to the output after converting it to an array
    // there should only be one found
    if (count($tests) > 0) {
      foreach ($tests as $test) {
        $output = (array) $test;
      }
    }

    // return the array
    return $output;
  }

  protected function getTypes() {
    return [
      '_none' => t('- none -'),
      'block' => t('Block'),
    ];
  }

  /**
   * A simple wrapper for isset to make it shorter to test
   *
   * @param $value
   * @param string $default_response
   *
   * @return string
   */
  private function _isset(&$value, $default_response = '') {
    return isset($value) ? $value : $default_response;
  }
}
