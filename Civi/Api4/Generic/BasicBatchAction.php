<?php

namespace Civi\Api4\Generic;

/**
 * Class BasicBatchAction
 *
 * Evaluate a query and execute some function on each matching item. Ex:
 *
 * $myAction = new BasicBatchAction('Entity', 'action', ['id', 'number'], function($item) {
 *   // Do something with $item['id'] and $item['number'].
 *   return ['id' => $item['id'], 'frobnication' => $item['number'] * $item['number']];
 * });
 *
 * @package Civi\Api4\Generic
 */
class BasicBatchAction extends AbstractBatchAction {

  /**
   * @var callable
   *
   * Function(array $item, BasicBatchAction $thisAction) => array
   */
  private $doer;

  /**
   * BasicBatchAction constructor.
   * @param string $entityName
   * @param string $actionName
   * @param string|array $select
   *   One or more fields to select from each matching item.
   *   Ex: 'id'
   * @param null $doer
   * Function(array $item, BasicBatchAction $thisAction) => array
   */
  public function __construct($entityName, $actionName, $select, $doer = NULL) {
    parent::__construct($entityName, $actionName, $select);
    $this->doer = $doer;
  }

  /**
   * We pass the setter function an array representing one object to update.
   * We expect to get the same format back.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $items = $this->getBatchRecords();
    foreach ($items as $item) {
      $result[] = $this->doTask($item);
    }
  }

  /**
   * This Basic Batch class can be used in one of two ways:
   *
   * 1. Use this class directly by passing a callable ($doer) to the constructor.
   * 2. Extend this class and override this function.
   *
   * Either way, this function should return an array with an output record
   * for the item.
   *
   * @param array $item
   * @return array
   */
  protected function doTask($item) {
    return call_user_func($this->doer, $item, $this);
  }

}
