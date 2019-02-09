<?php

namespace Civi\Api4\Action;

use Civi\Api4\Generic\Result;

/**
 * Delete one or more items, based on criteria specified in Where param.
 */
class Delete extends Get {

  use \Civi\Api4\Generic\BulkActionTrait;

  /**
   * Criteria for selecting items to delete.
   *
   * @required
   * @var array
   */
  protected $where = [];

  /**
   * Batch delete function
   */
  public function _run(Result $result) {
    $this->setSelect([$this->idField]);
    $defaults = $this->getParamDefaults();
    if ($defaults['where'] && !array_diff_key($this->where, $defaults['where'])) {
      throw new \API_Exception('Cannot delete with no "where" parameter specified');
    }

    $items = $this->getObjects();

    $ids = $this->deleteObjects($items);

    $result->exchangeArray($ids);
  }

  /**
   * @param $items
   * @return array
   * @throws \API_Exception
   */
  protected function deleteObjects($items) {
    $ids = [];
    $baoName = $this->getBaoName();
    if (method_exists($baoName, 'del')) {
      foreach ($items as $item) {
        $args = [$item[$this->idField]];
        $bao = call_user_func_array([$baoName, 'del'], $args);
        if ($bao !== FALSE) {
          $ids[] = $item[$this->idField];
        }
        else {
          throw new \API_Exception("Could not delete {$this->getEntity()} id {$item[$this->idField]}");
        }
      }
    }
    else {
      foreach ($items as $item) {
        $bao = new $baoName();
        $bao->id = $item[$this->idField];
        // delete it
        $action_result = $bao->delete();
        if ($action_result) {
          $ids[] = $item[$this->idField];
        }
        else {
          throw new \API_Exception("Could not delete {$this->getEntity()} id {$item[$this->idField]}");
        }
      }
    }
    return $ids;
  }

  /**
   * @inheritDoc
   */
  public function getParamInfo($param = NULL) {
    $info = parent::getParamInfo($param);
    if (!$param) {
      // Delete doesn't actually let you select fields.
      unset($info['select']);
    }
    return $info;
  }

}
