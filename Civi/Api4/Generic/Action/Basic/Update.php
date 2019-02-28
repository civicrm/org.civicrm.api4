<?php

namespace Civi\Api4\Generic\Action\Basic;

use Civi\Api4\Generic\Action\AbstractUpdate;
use Civi\Api4\Generic\Result;

/**
 * Update one or more records with new values.
 *
 * Use the where clause (required) to select them.
 */
class Update extends AbstractUpdate {

  /**
   * @var callable
   */
  private $setter;

  public function __construct($entity, $setter = NULL, $idField = 'id') {
    parent::__construct($entity, $idField);
    if ($setter) {
      $this->setter = $setter;
    }
    else {
      $this->setter = [$this, 'writeObject'];
    }
  }

  /**
   * We pass the setter function an array representing one object to update.
   * We expect to get the same format back.
   *
   * @param \Civi\Api4\Generic\Result $result
   */
  public function _run(Result $result) {
    $params = $this->getParams();
    unset($params['values']);
    $items = $this->getBatchItems();

    foreach ($items as $item) {
      $result[] = call_user_func($this->setter, $this->values + $item, $params);
    }
  }

}
