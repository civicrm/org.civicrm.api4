<?php

namespace Civi\Api4\Generic;

use Civi\Api4\Utils\FormattingUtil;
use Civi\Api4\Utils\CoreUtil;

/**
 * Helper functions for working with custom values
 *
 * @package Civi\Api4\Generic
 */
trait CustomValueCRUD {

  /**
   * Custom Group name if this is a CustomValue pseudo-entity.
   *
   * @var string
   */
  private $customGroup;

  /**
   * @inheritDoc
   */
  public function getEntity() {
    return 'Custom_' . $this->getCustomGroup();
  }

  /**
   * @inheritDoc
   */
  protected function writeObjects($items) {
    $result = [];
    foreach ($items as $item) {
      FormattingUtil::formatWriteParams($item, $this->getEntity(), $this->getEntityFields());

      $result[] = \CRM_Core_BAO_CustomValueTable::setValues($item);
    }
    return $result;
  }

  /**
   * @inheritDoc
   */
  protected function deleteObjects($items) {
    $customTable = CoreUtil::getCustomTableByName($this->getCustomGroup());
    $ids = [];
    foreach ($items as $item) {
      \CRM_Utils_Hook::pre('delete', $this->getEntity(), $item['id'], \CRM_Core_DAO::$_nullArray);
      \CRM_Utils_SQL_Delete::from($customTable)
        ->where('id = #value')
        ->param('#value', $item['id'])
        ->execute();
      \CRM_Utils_Hook::post('delete', $this->getEntity(), $item['id'], \CRM_Core_DAO::$_nullArray);
      $ids[] = $item['id'];
    }
    return $ids;
  }

  /**
   * @inheritDoc
   */
  protected function fillDefaults(&$params) {
    foreach ($this->getEntityFields() as $name => $field) {
      if (empty($params[$name])) {
        $params[$name] = $field['default_value'];
      }
    }
  }

  /**
   * @param $customGroup
   * @return static
   */
  public function setCustomGroup($customGroup) {
    $this->customGroup = $customGroup;
    return $this;
  }

  /**
   * @return string
   */
  public function getCustomGroup() {
    return $this->customGroup;
  }

}
