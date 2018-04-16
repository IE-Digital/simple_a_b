<?php

namespace Drupal\simple_a_b;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * .
 *
 * Defines an interface for simple a/b test plugins.
 */
interface SimpleABTypeInterface extends PluginInspectionInterface {
  /**
   * .
   *
   * Return the id of entity type.
   *
   * @return string
   */

  /**
   *
   */
  public function getId();

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
  public function getName();

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
  public function getEntityType();

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
  public function getEntityDescription();

}
