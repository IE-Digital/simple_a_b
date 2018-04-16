<?php

namespace Drupal\simple_a_b;

use Drupal\Component\Plugin\PluginBase;

/**
 * .
 */
class SimpleABTypeBase extends PluginBase implements SimpleABTypeInterface {
  /**
   * .
   *
   * Return the name of the entity type.
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
   * Return the name of the entity type.
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
   * Returns the entity type.
   *
   * @return mixed
   */

  /**
   *
   */
  public function getEntityType() {
    return $this->pluginDefinition['entityTargetType'];
  }

  /**
   * .
   *
   * Returns the entity description.
   *
   * @return mixed
   */

  /**
   *
   */
  public function getEntityDescription() {
    return $this->pluginDefinition['entityDescription'];
  }

}
