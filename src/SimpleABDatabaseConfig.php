<?php

namespace Drupal\simple_a_b;

use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SimpleABDatabaseConfig implements SimpleABStorageInterface {


  protected $connection;

  protected $state;

  protected $requestStack;

  private $_table = 'simple_a_b_config';


  /**
   * SimpleABDatabaseData constructor.
   *
   * @param \Connection $connection
   * @param \StateInterface $state
   * @param \RequestStack $request_stack
   */
  public function __construct(Connection $connection, StateInterface $state, RequestStack $request_stack) {
    $this->connection = $connection;
    $this->state = $state;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function create($name = "", $data = []) {
    $key = "";
    $input = [];
    $input['name'] = $name;
    $input['data'] = serialize($data);

    // try to add the data into the database
    try {
      $key = $this->connection->insert($this->_table)
        ->fields($input)
        ->execute();
      return $key;
    } catch (\Exception $e) {

      // if error log the exception
      \Drupal::logger('simple_a_b')->error($e);

      // return -1 tid
      return $key;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function update($name, $data) {

    try {
      $input = [];
      $input['data'] = serialize($data);

      // try to update based upon the name
      $update = $this->connection->update($this->_table)
        ->fields($input)
        ->condition('name', $name, "=")
        ->execute();

      // return the status
      return $update;
    } catch (\Exception $e) {
      // if error log the exception
      \Drupal::logger('simple_a_b')->error($e);

      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function remove($name) {
    try {
      // try to delete the config data
      $status = $this->connection->delete($this->_table)
        ->condition('name', $name)
        ->execute();

      // return the status
      return $status;
    } catch (\Exception $e) {
      // if error log the exception
      \Drupal::logger('simple_a_b')->error($e);

      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function fetch($name) {
    $query = $this->connection->select($this->_table, 'c');
    $query->fields('c', ['name', 'data']);
    $query->condition('c.name', $name, '=');
    $query->range(0, 1);

    $data = $query->execute();
    $results = $data->fetch();

    $results = $this->formatDataForDownload($results);

    return $results;
  }

  /**
   * Formats the data for use on forms
   *
   * @param $data
   *
   * @return mixed
   */
  private function formatDataForDownload($data) {

    if (isset($data->data)) {
      $data->data = unserialize($data->data);
    }

    return $data;
  }
}
