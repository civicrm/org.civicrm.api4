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

}
