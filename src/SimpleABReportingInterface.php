<?php
/**
 * @file
 * Provides Drupal\simple_a_b\SimpleABReportingInterface
 */

namespace Drupal\simple_a_b;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for simple a/b test plugins.
 */
interface SimpleABReportingInterface extends PluginInspectionInterface {

  /**
   * Return the id of the report.
   *
   * @return string
   */
  public function getId();

  /**
   * Return the name report.
   *
   * @return string
   */
  public function getName();

  /**
   * returns the reporting method
   *
   * @return mixed
   */
  public function getReportingMethod();

}
