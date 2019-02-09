<?php

namespace Civi\Api4\Action\CustomValue;

use Civi\Api4\Generic\Result;
use Civi\Api4\Utils\CoreUtil;

/**
 * Delete one or more items, based on criteria specified in Where param.
 */
class Delete extends Get {

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    $defaults = $this->getParamDefaults();
    if ($defaults['where'] && !array_diff_key($this->where, $defaults['where'])) {
      throw new \API_Exception('Cannot delete with no "where" paramater specified');
    }
    // run the parent action (get) to get the list
    parent::_run($result);
    // Then act on the result
    $customTable = CoreUtil::getCustomTableByName($this->getCustomGroup());
    $ids = [];
    foreach ($result as $item) {
      \CRM_Utils_Hook::pre('delete', $this->getEntity(), $item['id'], \CRM_Core_DAO::$_nullArray);
      \CRM_Utils_SQL_Delete::from($customTable)
        ->where('id = #value')
        ->param('#value', $item['id'])
        ->execute();
      \CRM_Utils_Hook::post('delete', $this->getEntity(), $item['id'], \CRM_Core_DAO::$_nullArray);
      $ids[] = $item['id'];
    }

    $result->exchangeArray($ids);
    return $result;
  }

}
