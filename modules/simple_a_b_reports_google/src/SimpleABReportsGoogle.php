<?php
/**
 * @file
 * Contains \Drupal\simple_a_b_reports_google\SimpleABReportsGoogle.
 */

namespace Drupal\simple_a_b_reports_google;

class SimpleABReportsGoogle {

  // constant for the session id
  private static $_sessionId = "simple_a_b_reports_google";

  // constant for the report key
  private static $_reportKey = "reports";


  /**
   * Adds an incoming report to the report array
   *
   * @param array $data
   *
   * @return mixed
   */
  public static function setReport(array $data = []) {

    // load up any already set reports
    // if we cannot find any create an empty array
    $array = self::getReport();
    if (!$array || !is_array($array)) {
      $array = [];
    }

    // add the data to the array
    $array[] = $data;

    // prepare the data for being saved into session
    $array = self::prepareData($array);

    // save the data into session
    $tempstore = \Drupal::service('user.private_tempstore')
      ->get(self::$_sessionId);
    $set = $tempstore->set(self::$_reportKey, $array);

    // return its response
    return $set;
  }

  /**
   * Returns all set reports
   *
   * @return mixed|string
   */
  public static function getReport() {

    // load the reports from the session
    $tempstore = \Drupal::service('user.private_tempstore')
      ->get(self::$_sessionId);
    $get = $tempstore->get(self::$_reportKey);

    // clean up the data so it can be read
    $data = self::prepareData($get);

    // return the data
    return $data;
  }

  /**
   * Sets the report data back to an empty array
   *
   * @return mixed
   */
  public static function removeAllReports() {
    // prepare the empty array of data
    $data = self::prepareData([]);

    // save the data into session
    $tempstore = \Drupal::service('user.private_tempstore')
      ->get(self::$_sessionId);
    $set = $tempstore->set(self::$_reportKey, $data);

    // return the result
    return $set;
  }

  /**
   * checks if the data is serialize or not and converts it correctly
   *
   * @param $data
   *
   * @return mixed|string
   */
  private static function prepareData($data) {
    // check of serialized data
    $is_serialize = (@unserialize($data) !== FALSE || $data == 'b:0;');

    // if it is serialized we should unserialize it
    // else serialize
    if ($is_serialize) {
      $data = unserialize($data);
    }
    else {
      $data = serialize($data);
    }

    // return the new data
    return $data;
  }
}
