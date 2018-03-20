<?php
/**
 * @file
 * Provides Drupal\simple_a_b\FlavorBase.
 */

namespace Drupal\simple_a_b;

use Drupal\Component\Plugin\PluginBase;

class SimpleABTypeBase extends PluginBase implements SimpleABTypeInterface {

  /**
   * Return the name of the ice cream flavor.
   *
   * @return string
   */
  public function getId() {
    return $this->pluginDefinition['id'];
  }

  /**
   * Return the name of the ice cream flavor.
   *
   * @return string
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * Loads the correct select options
   *
   * @return mixed
   */
  public function loadSelectOptions() {
    // TODO: Implement loadSelectOptions() method.
  }
}
