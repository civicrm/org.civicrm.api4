<?php

namespace Civi\Api4\Action\CustomValue;

use Civi\Api4\Action\Replace as DefaultReplace;
use Civi\Api4\Utils\FormattingUtil;

/**
 * Given a set of records, will appropriately update the database.
 *
 * @method $this setRecords(array $records) Array of records.
 * @method $this addRecord($record) Add a record to update.
 */
class Replace extends DefaultReplace {

  protected $select = ['id', 'entity_id'];

  /**
   * @inheritDoc
   */
  public function getEntity() {
    return 'Custom_' . $this->getCustomGroup();
  }

  /**
   * @inheritDoc
   */
  protected function writeObject($params) {
    FormattingUtil::formatWriteParams($params, $this->getEntity(), $this->getEntityFields());
    return \CRM_Core_BAO_CustomValueTable::setValues($params);
  }

}
