<?php

namespace Drupal\simple_a_b\Form;


use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

class SimpleABDeleteForm extends ConfirmFormBase {

  protected $tid;

  protected $loaded_data;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'simple_a_b_test_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $message = t('You cannot delete, which you cannot find...');

    if (!empty($this->loaded_data)) {
      $message = t('Are you sure you want to delete "@name" test?', ['@name' => $this->loaded_data['name']]);
    }

    return $message;
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('simple_a_b.view_tests');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Please make sure this is the test you want to delete. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   */
  public function buildForm(array $form, FormStateInterface $form_state, $tid = NULL) {
    $this->tid = $tid;

    $this->loaded_data = $this->loadData($this->tid);

    if (empty($this->loaded_data)) {
      $form = [];

      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('Error the test could not be found'), 'error');

      return $form;
    }
    else {
      return parent::buildForm($form, $form_state);
    }
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
    // try to update the existing test
    $remove = \Drupal::service('simple_a_b.storage.test')->remove($this->tid);

    if ($remove != TRUE) {
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('Error deleting test'), 'error');
    }
    else {
      // otherwise display message
      $messenger = \Drupal::messenger();
      $messenger->addMessage(t('"@name" has been removed', ['@name' => $this->loaded_data['name']]), 'status');

      // and redirect back to viewing all tests
      $url = Url::fromRoute('simple_a_b.view_tests');
      $form_state->setRedirectUrl($url);
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
}
