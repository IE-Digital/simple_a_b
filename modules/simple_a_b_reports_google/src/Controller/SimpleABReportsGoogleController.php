<?php
/**
 * @file
 * Contains \Drupal\simple_a_b_reports_google\Controller\SimpleABReportsGoogleController
 */

namespace Drupal\simple_a_b_reports_google\Controller;

use Drupal\simple_a_b_reports_google\SimpleABReportsGoogle;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

class SimpleABReportsGoogleController extends ControllerBase {


  /**
   * Creates a json response that returns any reports that need to be sent
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function getReports() {
    $output = [];

    // load up the reports
    $reports = SimpleABReportsGoogle::getReport();
    $output['reports'] = $reports; // add to an array

    // clear out all the old reports
    SimpleABReportsGoogle::removeAllReports();

    // return the json response
    return new JsonResponse($output);
  }

}
