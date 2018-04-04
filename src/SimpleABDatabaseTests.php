<?php

namespace Drupal\simple_a_b;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SimpleABDatabaseTests implements SimpleABStorageInterface {

  protected $connection;

  protected $state;

  protected $requestStack;

  private $_table = 'simple_a_b_tests';

  private $_viewCache = 'config:views.view.simple_a_b_tests';

  /**
   * SimpleABDatabaseTests constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   */
  public function __construct(Connection $connection, StateInterface $state, RequestStack $request_stack) {
    $this->connection = $connection;
    $this->state = $state;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function create($test_data = [], $data_data = []) {

    $user = \Drupal::currentUser();
    $tid = -1;

    // add in the created/updated user & timestamp
    $test_data['created_by'] = $user->id();
    $test_data['created'] = \Drupal::time()->getRequestTime();
    $test_data['updated_by'] = $user->id();
    $test_data['updated'] = \Drupal::time()->getRequestTime();

    try {
      // try to add the data into the database
      $tid = $this->connection->insert($this->_table)
        ->fields($test_data)
        ->execute();

      // log that a new test has been created
      \Drupal::logger('simple_a_b')
        ->info('New test "@name" (@tid) has been created', [
          '@name' => $test_data['name'],
          '@tid' => $tid,
        ]);

      // invalidate the views cache
      // so that the view will show that something has been added
      Cache::invalidateTags([$this->_viewCache]);

      // set the tid
      $data_data['tid'] = $tid;

      // update the data from data table
      \Drupal::service('simple_a_b.storage.data')->create($data_data);

      // update the simple a/b config
      // this helps to keep track of all the enabled / disabled modules
      $this->updateConfig($test_data['type'], $tid, $test_data['eid'], $test_data['enabled']);

      // return the created tid
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
  public function update($tid, $did, $test_data = [], $data_data = []) {

    // get current user
    $user = \Drupal::currentUser();

    // set the updated user & timestamp
    $test_data['updated_by'] = $user->id();
    $test_data['updated'] = \Drupal::time()->getRequestTime();

    try {
      // try to update based upon the tid
      $update = $this->connection->update($this->_table)
        ->fields($test_data)
        ->condition('tid', $tid, "=")
        ->execute();

      // log that a new test has been updated
      \Drupal::logger('simple_a_b')
        ->info('Test "@name" (@tid) has been updated', [
          '@name' => $test_data['name'],
          '@tid' => $tid,
        ]);

      // invalidate the views cache
      // so that the view will show that something has been updated
      Cache::invalidateTags([$this->_viewCache]);

      // update the data from data table
      \Drupal::service('simple_a_b.storage.data')->update($did, $data_data);

      // update the simple a/b config
      // this helps to keep track of all the enabled / disabled modules
      $this->updateConfig($test_data['type'], $tid, $test_data['eid'], $test_data['enabled']);

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

      // fetch the data so we can update the config data
      // this helps to keep track of all the enabled / disabled modules
      // in this case it will always make sure we remove the deleted data from the config
      $test = $this->fetch($tid);
      $this->updateConfig($test->type, $tid, $test->eid, FALSE);

      // try to delete the test
      $status = $this->connection->delete($this->_table)
        ->condition('tid', $tid)
        ->execute();

      // log that we have deleted a test
      \Drupal::logger('simple_a_b')
        ->info('Test "@tid" has been removed', [
          '@tid' => $tid,
        ]);

      // invalidate the views cache
      // so that the view will show that something has been removed
      Cache::invalidateTags([$this->_viewCache]);

      // remove the data from data table
      \Drupal::service('simple_a_b.storage.data')->remove($tid);

      //      return the status
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

    $test = $this->connection->select($this->_table, 't')
      ->fields('t', ['tid', 'name', 'description', 'enabled', 'type', 'eid'])
      ->condition('t.tid', $tid, '=')
      ->range(0, 1)
      ->execute()->fetch();

    return $test;
  }

  /**
   * @param $name
   * @param $tid
   * @param $eid
   * @param $enabled
   */
  private function updateConfig($name, $tid, $eid, $enabled) {

    // create a new array with the new data
    $eids = [$tid => $eid];

    // try to load the config data
    $config = \Drupal::service('simple_a_b.storage.config')->fetch($name);

    // join the new eid to the old config
    // or create an new array with just the new eid
    $data = $config ? $config->data + $eids : $eids;


    // if the eid is not enabled
    // make sure we remove it from the list
    if (!$enabled) {
      unset($data[$tid]);
    }

    // if we don't have any config
    // we can assume one does not exist therefore we should create one
    // otherwise we can update the existing config
    if (!$config) {
      \Drupal::service('simple_a_b.storage.config')->create($name, $data);
    }
    else {
      \Drupal::service('simple_a_b.storage.config')->update($name, $data);
    }

  }
}
