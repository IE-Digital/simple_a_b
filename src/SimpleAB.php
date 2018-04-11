<?php

namespace Drupal\simple_a_b;

class SimpleAB {

  /**
   * Uses the test object data to calculate what version we should be showing
   * and if cookies are set we make sure we only show the same one for the set amount of time
   *
   * @param $test_obj
   *
   * @return bool
   */
  public static function calculateExperience($test_obj) {
    // get the remember state
    $getRemember = self::getRemember($test_obj);

    // if we have a remember value
    // and the value is "true" or "false"
    // convert to bool & set as the response
    if ($getRemember && ($getRemember === "true" || $getRemember === "false")) {
      $response = $getRemember === "true" ? TRUE : FALSE;
    }
    else {
      // otherwise calculate the response data
      $response = self::calculateVariation($test_obj);

      // set a new remember state
      self::setRemember($test_obj, $response);
    }

    // send the data over to the reporter
    self::report($test_obj, $response);

    return $response;
  }

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
  public static function calculateVariation($test_obj) {
    // otherwise calculate the response data
    $num = rand(1, 100);
    $response = $num > 49 ? TRUE : FALSE;

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
    // and that reporting method is not "_none"
    if ($reportMethod && $reportMethod !== "_none") {
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

  public static function setRemember($obj, $value) {
    // load the simple a/b settings
    $simple_a_b_config = \Drupal::config('simple_a_b.settings');
    // get the status for remember method
    $rememberMethod = $simple_a_b_config->get('remember');

    // if $rememberMethod is set and is not "_none"
    if ($rememberMethod && $rememberMethod !== "_none") {
      // get the prefix
      $prefix = $simple_a_b_config->get('remember_prefix');
      // create slug key
      $key = self::slugify($prefix . "-" . $obj->name . "-" . $obj->tid);
      // get lifetime value
      $lifetime = (int) $simple_a_b_config->get('remember_lifetime');
      // get current request time
      $request_time = \Drupal::time()->getRequestTime();

      $value = ($value) ? 'true' : 'false';

      // switch case on the method
      switch ($rememberMethod) {

        // if is a cookie
        // create a new cookie setting the value and lifetime
        case 'cookie':
          return setcookie($key, $value, $request_time + $lifetime);
          break;
      }
    }

    // if we failed return -1
    return -1;
  }

  public static function getRemember($obj) {
    // load the simple a/b settings
    $simple_a_b_config = \Drupal::config('simple_a_b.settings');
    // get the status for remember method
    $rememberMethod = $simple_a_b_config->get('remember');

    // if $rememberMethod is set and is not "_none"
    if ($rememberMethod && $rememberMethod !== "_none") {
      // get the prefix
      $prefix = $simple_a_b_config->get('remember_prefix');
      // create slug key
      $key = self::slugify($prefix . "-" . $obj->name . "-" . $obj->tid);

      // switch case on the method
      switch ($rememberMethod) {

        // if is a cookie
        // try and get then return the value
        case 'cookie':
          return $_COOKIE[$key];
          break;
      }
    }

    // if we failed return -1
    return -1;
  }

  /**
   * Slugify method
   * Taken from:
   * https://stackoverflow.com/questions/2955251/php-function-to-make-slug-url-string
   *
   * @param $string
   *
   * @return string
   */
  public static function slugify($string) {
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '-');
    $string = preg_replace('~-+~', '-', $string);
    $string = strtolower($string);

    if (empty($string)) {
      return 'n-a';
    }
    return $string;
  }

}
