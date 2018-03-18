<?php

namespace Drupal\simple_a_b;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\State\StateInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class SimpleABDatabaseStorage implements SimpleABStorageInterface {

  protected $connection;

  protected $state;

  protected $requestStack;

  private $_table = 'simple_a_b_tests';

  private $_viewCache = 'config:views.view.simple_a_b_tests';

  public function __construct(Connection $connection, StateInterface $state, RequestStack $request_stack) {
    $this->connection = $connection;
    $this->state = $state;
    $this->requestStack = $request_stack;
  }

  /**
   * {@inheritdoc}
   */
  public function create($data) {

    $user = \Drupal::currentUser();
    $tid = -1;

    // add in the created/updated user & timestamp
    $data['created_by'] = $user->id();
    $data['created'] = \Drupal::time()->getRequestTime();
    $data['updated_by'] = $user->id();
    $data['updated'] = \Drupal::time()->getRequestTime();

    try {
      // try to add the data into the database
      $tid = $this->connection->insert($this->_table)->fields($data)->execute();

      // log that a new test has been created
      \Drupal::logger('simple_a_b')
        ->info('New test "@name" (@tid) has been created', [
          '@name' => $data['name'],
          '@tid' => $tid,
        ]);

      // invalidate the views cache
      // so that the view will show that something has been added
      Cache::invalidateTags([$this->_viewCache]);

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
  public function update($tid, $data) {

    // get current user
    $user = \Drupal::currentUser();

    // set the updated user & timestamp
    $data['updated_by'] = $user->id();
    $data['updated'] = \Drupal::time()->getRequestTime();

    try {
      // try to update based upon the tid
      $update = $this->connection->update($this->_table)
        ->fields($data)
        ->condition('tid', $tid, "=")
        ->execute();

      // log that a new test has been updated
      \Drupal::logger('simple_a_b')
        ->info('Test "@name" (@tid) has been updated', [
          '@name' => $data['name'],
          '@tid' => $tid,
        ]);

      // invalidate the views cache
      // so that the view will show that something has been updated
      Cache::invalidateTags([$this->_viewCache]);

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

      // log that we have deleted a test
      \Drupal::logger('simple_a_b')
        ->info('Test "@tid" has been removed', [
          '@tid' => $tid,
        ]);

      // invalidate the views cache
      // so that the view will show that something has been removed
      Cache::invalidateTags([$this->_viewCache]);

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

    $test = $this->connection->select($this->_table, 't')
      ->fields('t', ['tid', 'name', 'description', 'enabled', 'type', 'eid'])
      ->condition('t.tid', $tid, '=')
      ->range(0, 1)
      ->execute()->fetchAll();

    return $test;
  }
}
