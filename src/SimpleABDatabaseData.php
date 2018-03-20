<?php

namespace Drupal\simple_a_b;

use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SimpleABDatabaseData implements SimpleABStorageInterface {


  protected $connection;

  protected $state;

  protected $requestStack;

  private $_table = 'simple_a_b_data';

  private $_tableJoin = 'simple_a_b_tests';

  private $_dontMove = ['tid'];


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
  public function create($data) {

    $tid = -1;

    try {
      $data = $this->formatDataForUpload($data);

      // try to add the data into the database
      $tid = $this->connection->insert($this->_table)->fields($data)->execute();

      //       return the created tid
      return $tid;
    } catch (\Exception $e) {

      // if error log the exception
      \Drupal::logger('simple_a_b')->error($e);

      // return -1 tid
      return $tid;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function update($did, $data) {

    try {
      // format the data for upload
      $data = $this->formatDataForUpload($data);

      // try to update based upon the tid
      $update = $this->connection->update($this->_table)
        ->fields($data)
        ->condition('did', $did, "=")
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
  public function remove($tid) {
    try {

      // try to delete the test
      $status = $this->connection->delete($this->_table)
        ->condition('tid', $tid)
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
  public function fetch($tid) {
    $query = $this->connection->select($this->_table, 'd');
    $query->fields('d', ['did', 'tid', 'data', 'conditions', 'settings']);
    $query->fields('t', [
      'tid',
      'name',
      'description',
      'enabled',
      'type',
      'eid',
    ]);
    $query->join($this->_tableJoin, 't', 'd.tid=t.tid');
    $query->condition('d.tid', $tid, '=');
    $query->range(0, 1);
    $data = $query->execute();
    $results = $data->fetchAll();

    $results = $this->formatDataForDownload($results);

    return $results;
  }

  /**
   * Format the data for upload to the database
   *
   * @param $data
   *
   * @return array
   */
  private function formatDataForUpload($data) {
    $output = [];
    $output['data'] = [];
    $output['settings'] = [];
    $output['conditions'] = [];

    // move all data from its keys
    // into data as a serialize data
    foreach ($data as $key => $item) {
      if (!in_array($key, $this->_dontMove)) {
        $output['data'][$key] = $item;
      }
    }

    $output['data'] = serialize($output['data']);
    $output['settings'] = serialize($output['settings']);
    $output['conditions'] = serialize($output['conditions']);


    return $output;
  }


  /**
   * Formats the data for use on forms
   *
   * @param $data
   *
   * @return mixed
   */
  private function formatDataForDownload($data) {

    foreach ($data as &$test) {

      // unserialize the content
      $test->data = unserialize($test->data);
      $test->settings = unserialize($test->settings);
      $test->conditions = unserialize($test->conditions);


      // loop thought all 'data' separating it all back out
      foreach ($test->data as $key => $value) {
        $test->{$key} = $value;
      }

      // unset the data ouput
      unset($test->data);

    }

    return $data;
  }

}
