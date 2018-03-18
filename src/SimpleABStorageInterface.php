<?php

namespace Drupal\simple_a_b;

interface SimpleABStorageInterface {

  /**
   * Add a new test to the database
   *
   * @param $data
   *
   * @return int
   */
  public function create($data);

  /**
   * Update an existing test in the database
   *
   * @param $tid
   * @param $data
   *
   * @return int
   */
  public function update($tid, $data);

  /**
   * Remove a test from the database
   *
   * @param $tid - test id
   *
   * @return mixed
   */
  public function remove($tid);

}
