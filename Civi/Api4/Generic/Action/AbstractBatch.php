<?php

namespace Civi\Api4\Generic\Action;

/**
 * Base class for all batch actions (Update, Delete, Replace).
 *
 * This differs from the AbstractQuery class in that the "Where" clause is required.
 *
 * @package Civi\Api4\Generic
 */
abstract class AbstractBatch extends AbstractQuery {

  /**
   * Criteria for selecting items to process.
   *
   * @required
   * @var array
   */
  protected $where = [];

  /**
   * @var array
   */
  private $select;

  public function __construct($entity = NULL, $idField = 'id') {
    $this->select = (array) $idField;
    parent::__construct($entity);
  }

  /**
   * @return array
   */
  protected function getBatchRecords() {
    $params = [
      'checkPermissions' => $this->checkPermissions,
      'where' => $this->where,
      'orderBy' => $this->orderBy,
      'limit' => $this->limit,
      'offset' => $this->offset,
    ];
    if (empty($this->reload)) {
      $params['select'] = $this->select;
    }

    return (array) civicrm_api4($this->getEntity(), 'get', $params);
  }

  /**
   * @return array
   */
  protected function getSelect() {
    return $this->select;
  }

}
