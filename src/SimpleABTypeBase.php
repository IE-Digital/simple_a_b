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
   * returns the entity type
   *
   * @return mixed
   */
  public function getEntityType() {
    return $this->pluginDefinition['entityTargetType'];
  }

  /**
   * returns the entity description
   *
   * @return mixed
   */
  public function getEntityDescription() {
    return $this->pluginDefinition['entityDescription'];
  }
}
