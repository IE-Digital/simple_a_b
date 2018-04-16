<?php

namespace Drupal\simple_a_b;

use Drupal\Component\Plugin\PluginBase;

/**
 * .
 */
class SimpleABReportingBase extends PluginBase implements SimpleABReportingInterface {
  /**
   * .
   *
   * Return the name of the reporting type.
   *
   * @return string
   */

  /**
   *
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * .
   *
   * Return the name of the reporting type.
   *
   * @return string
   */

  /**
   *
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * .
   *
   * Returns the reporting method.
   *
   * @return mixed
   */

  /**
   *
   */
  public function getReportingMethod() {
    return $this->pluginDefinition['method'];
  }

}
