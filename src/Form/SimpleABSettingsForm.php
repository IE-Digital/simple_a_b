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


    // reporting methods
    $reportingMethods = static::getReportingMethods();
    $form['reporting'] = [
      '#type' => 'select',
      '#title' => $this->t('Reporting method'),
      '#options' => $reportingMethods['options'],
      '#default_value' => $simple_a_b_config->get('reporting'),
      '#description' => $reportingMethods['description'],
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
      ->set('reporting', $values['reporting'])
      ->save();

    parent::submitForm($form, $form_state);
  }


  /**
   * Looks up any reporting methods installed
   *
   * @return array
   */
  protected static function getReportingMethods() {
    $output = [];
    $options = [];
    // default of none
    $options['_none'] = t('- none -');

    $manager = \Drupal::service('plugin.manager.simpleab.report');
    $plugins = $manager->getDefinitions();

    // if we have some plugsin
    // lets loop though them to create a drop down list of items
    if (!empty($plugins)) {
      foreach ($plugins as $reporter) {
        $instance = $manager->createInstance($reporter['id']);
        $options[$instance->getId()] = $instance->getName();
      }
    }

    // add the options to the array
    $output['options'] = $options;

    // check the number of options
    // this will change the text of description
    // to try and encourage enabling another module
    if (count($options) > 1) {
      $output['description'] = t('Where should the results be reported to?');
    }
    else {
      $module_path = '/admin/modules';
      $output['description'] = t('No reporting methods could be found. Please <a href="@simple-ab-modules">enable</a> at least one.', ['@simple-ab-modules' => $module_path]);
    }


    return $output;
  }
}
