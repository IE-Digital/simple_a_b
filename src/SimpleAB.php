<?php

namespace Drupal\simple_a_b;

class SimpleAB {

  /**
   * Calculates the experience for this user or session
   * currently this just uses a random number gen this will at some point
   * be updated to be smarter and more useful
   * TODO: Make this more smart and useful
   *
   * @param $test_obj
   *
   * @return bool
   */
  public static function calculateExperience($test_obj) {
    $num = rand(1, 100);
    $response = $num > 49 ? TRUE : FALSE;

    // send the data over to the reporter
    self::report($test_obj, $response);

    return $response;
  }

  /**
   * Function design to start the process of sending data over to reporting
   * modules this could go to internal or external reporters
   *
   * @param $test_obj
   * @param $response
   */
  public static function report($test_obj, $response) {
    // load the simple a/b settings
    $simple_a_b_config = \Drupal::config('simple_a_b.settings');
    // get the status for reporting methods
    $reportMethod = $simple_a_b_config->get('reporting');

    // if we have a reporting method
    if ($reportMethod) {
      // load up the plugin manger
      $manager = \Drupal::service('plugin.manager.simpleab.report');
      // load the instance for the selected plugin
      $instance = $manager->createInstance($reportMethod);
      // grab its reporting method
      $method = $instance->getReportingMethod();

      // call its reporting method
      // passing in the object data and the response status
      $method($test_obj, $response);
    }
  }

}
