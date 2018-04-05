<?php

namespace Drupal\simple_a_b;

class SimpleAB {

  /**
   * Calculates the experience for this user or session
   * currently this just uses a random number gen this will at some point
   * be updated to be smarter and more useful
   * TODO: Make this more smart and useful
   *
   * @return bool
   */
  public static function calculateExperience() {
    $num = rand(1, 100);
    return $num > 49 ? TRUE : FALSE;
  }

}
