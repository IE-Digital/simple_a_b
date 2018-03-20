<?php

namespace Drupal\simple_a_b\Form;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\simple_a_b\SimpleABTypeManger;

class SimpleABTestForm extends FormBase {

  protected $_fieldTestPrepend = 'test_field_';

  protected $_fieldDataPrepend = 'data_field_';

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
    $loaded_test = $this->loadTestData($tid);
    $edit_mode = isset($loaded_test['name']) ? TRUE : FALSE;

    // if we have a tid & the data returned is empty
    // we should stop the form and display an error message
    if ($tid !== NULL && empty($loaded_test)) {

      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('No test could be found'), 'error');

      return $form;
    }

    // test details
    $form['test'] = [
      '#type' => 'details',
      '#title' => t('Test information'),
      '#description' => t('Administrative information.'),
      '#open' => TRUE,
    ];

    // test name
    $form['test'][$this->_fieldTestPrepend . 'name'] = [
      '#type' => 'textfield',
      '#title' => t('Name'),
      '#description' => t('Administrative name'),
      '#default_value' => $this->_isset($loaded_test['name']),
      '#required' => TRUE,
    ];

    // test description
    $form['test'][$this->_fieldTestPrepend . 'description'] = [
      '#type' => 'textfield',
      '#title' => t('Description'),
      '#default_value' => $this->_isset($loaded_test['description']),
      '#description' => t('Administrative description'),
    ];

    // test type
    $form['test'][$this->_fieldTestPrepend . 'type'] = [
      '#type' => 'select',
      '#title' => t('Type'),
      '#default_value' => $this->_isset($loaded_test['type']),
      '#options' => $this->getTypes(),
      '#description' => t('What type of entity to test'),
      '#required' => TRUE,
      '#ajax' => [
        // Function to call when event on form element triggered.
        'callback' => '::loadCorrectEntityAutoComplete',
        // Effect when replacing content. Options: 'none' (default), 'slide', 'fade'.
        'effect' => 'fade',
        // Javascript event to trigger Ajax. Currently for: 'onchange'.
        'event' => 'click',
        'progress' => [
          // Graphic shown to indicate ajax. Options: 'throbber' (default), 'bar'.
          'type' => 'throbber',
          // Message to show along progress graphic. Default: 'Please wait...'.
          'message' => 'loading',
        ],
      ],
    ];

    // test entity id
    $form['test'][$this->_fieldTestPrepend . 'eid'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'block_content',
      '#description' => t('The entity to apply the tests too'),
      '#default_value' => BlockContent::load($this->_isset($loaded_test['eid'], 0)),
      '#required' => TRUE,
      '#attributes' => [
        'id' => ['edit-output'],
      ],
    ];

    // test enabled status
    $form['test'][$this->_fieldTestPrepend . 'enabled'] = [
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


    // data information
    $form['variations'] = [
      '#type' => 'details',
      '#title' => t('Variations'),
      '#description' => t('Each variation that will be tested against the original, minimum of 1 variation is required.'),
      '#open' => TRUE,
    ];

    $form['variations'][$this->_fieldDataPrepend . 'content'] = [
      '#type' => 'textarea',
      '#title' => t('Replacement content'),
      '#description' => t('This will be the content that replaces the original content'),
      '#default_value' => $this->_isset($loaded_test['content']),

    ];

    // place to hold the actions
    $form['actions'] = ['#type' => 'actions'];

    // submit button
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $edit_mode ? t('Update') : t('Add'),
      '#attributes' => ['class' => ['button--primary']],
    ];

    //    $form['actions']['preview'] = [
    //      '#type' => 'submit',
    //      '#value' => t('Preview'),
    //    ];

    // it edit mode enabled
    if ($edit_mode) {

      // if we are in edit mode show up the delete button
      $form['actions']['delete'] = [
        '#markup' => "<a href='/admin/config/user-interface/simple-a-b/" . $tid . "/delete' class='button button--danger'>" . t('Delete') . "</a>",
        '#allowed_tags' => ['a'],
      ];

      // hidden field for the tid
      // this should only be on edit otherwise the database
      // will try and set the auto_increment tid
      $form[$this->_fieldTestPrepend . 'tid'] = [
        '#type' => 'hidden',
        '#value' => $this->_isset($loaded_test['tid']),
      ];

      // hidden field for the did
      // this should only be on edit otherwise the database
      // will try and set the auto_increment did
      $form[$this->_fieldDataPrepend . 'did'] = [
        '#type' => 'hidden',
        '#value' => $this->_isset($loaded_test['did']),
      ];
    }

    // hidden flag to check of edit mode
    $form['edit_mode'] = [
      '#type' => 'hidden',
      '#value' => $edit_mode ? 'true' : 'false',
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
    $test_data = [];
    $data_data = [];
    $edit_mode = FALSE;

    // loop thought all the get values
    // pulling out only the ones that have been set as values in the form above
    foreach ($form_state->getValues() as $key => $value) {

      // find all the rest of the form data
      if (strpos($key, $this->_fieldTestPrepend) !== FALSE) {
        $key = str_replace($this->_fieldTestPrepend, '', $key);
        $test_data[$key] = $value;
      }

      if (strpos($key, $this->_fieldDataPrepend) !== FALSE) {
        $key = str_replace($this->_fieldDataPrepend, '', $key);
        $data_data[$key] = $value;
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
      $tid = \Drupal::service('simple_a_b.storage.test')
        ->create($test_data, $data_data);

      if ($tid === -1) {
        // if we don't get back a positive tid, display the error message
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('Error creating new test'), 'error');
      }
      else {
        // otherwise display positive message
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('New test "@name" has been created', ['@name' => $test_data['name']]), 'status');

        // and redirect back to viewing all tests
        $url = Url::fromRoute('simple_a_b.view_tests');
        $form_state->setRedirectUrl($url);
      }
    }
    else {
      // set tid and remove it from the $data array
      $tid = $test_data['tid'];
      unset($test_data['tid']);

      $did = $data_data['did'];
      $data_data['tid'] = $tid;
      unset($data_data['did']);

      // try to update the existing test
      $update = \Drupal::service('simple_a_b.storage.test')
        ->update($tid, $did, $test_data, $data_data);


      // if status is not true then error
      if ($update != TRUE) {
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('Error updating test'), 'error');
      }
      else {
        // otherwise display positive message
        $messenger = \Drupal::messenger();
        $messenger->addMessage(t('"@name" has been updated', ['@name' => $test_data['name']]), 'status');

        // and redirect back to viewing all tests
        $url = Url::fromRoute('simple_a_b.view_tests');
        $form_state->setRedirectUrl($url);
      }
    }
  }


  /**
   * Loads in the collect entity selector based upon the type selected
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   */
  public function loadCorrectEntityAutoComplete(array &$form, FormStateInterface $form_state) {
    $elem = [
      '#type' => 'textfield',
      '#size' => '60',
      '#disabled' => TRUE,
      '#value' => 'Hello, ' . $form_state->getValue($this->_fieldTestPrepend . 'type') . '!',
      '#attributes' => [
        'id' => ['edit-output'],
      ],
    ];
    $renderer = \Drupal::service('renderer');
    $response = new AjaxResponse();
    $response->addCommand(new ReplaceCommand('#edit-output', $renderer->render($elem)));
    return $response;
  }

  /**
   * load a tests information used for amending edits
   *
   * @param null $tid
   *
   * @return array
   */
  protected function loadTestData($tid = NULL) {
    $output = [];

    // if there is no tid
    // then simply return empty array
    if ($tid === NULL) {
      return $output;
    }

    // otherwise run a fetch looking up the test id
    $tests = \Drupal::service('simple_a_b.storage.data')->fetch($tid);

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

  /**
   * Using the plugin manger looks
   * for any test types
   *
   * @return array
   */
  protected function getTypes() {
    $output = [];
    // default of none
    $output['_none'] = t('- none -');

    $manager = \Drupal::service('plugin.manager.simpleab.type');
    $plugins = $manager->getDefinitions();

    // if we have some plugsin
    // lets loop though them to create a drop down list of items
    if (!empty($plugins)) {
      foreach ($plugins as $test) {
        $instance = $manager->createInstance($test['id']);
        $output[$instance->getId()] = $instance->getName();
      }
    }

    return $output;
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
