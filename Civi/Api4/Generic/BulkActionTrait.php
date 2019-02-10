<?php

namespace Civi\Api4\Generic;

/**
 * Helper functions for performing bulk actions (update, delete, replace)
 *
 * @package Civi\Api4\Generic
 */
trait BulkActionTrait {

  /**
   * Field by which objects are identified.
   *
   * @var string
   */
  private $idField = 'id';

  /**
   * @return string
   */
  protected function getIdField() {
    return $this->idField;
  }

  /**
   * @param string $idField
   */
  protected function setIdField($idField) {
    $this->idField = $idField;
  }

  public function setSelect($items) {
    throw new \API_Exception('Cannot set select for bulk actions');
  }

  public function addSelect($item) {
    throw new \API_Exception('Cannot set select for bulk actions');
  }

  /**
   * @inheritDoc
   */
  public function getParamInfo($param = NULL) {
    $info = parent::getParamInfo($param);
    if (!$param) {
      // Bulk actions don't actually let you select fields.
      unset($info['select']);
    }
    return $info;
  }

}
