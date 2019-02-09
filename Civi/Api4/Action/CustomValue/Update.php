<?php

namespace Civi\Api4\Action\CustomValue;

use Civi\Api4\Generic\Result;
use Civi\Api4\Utils\FormattingUtil;
use Civi\Api4\Action\Update as DefaultUpdate;

/**
 * Update one or more records with new values. Use the where clause to select them.
 *
 * @method $this setValues(array $values) Set all field values from an array of key => value pairs.
 * @method $this addValue($field, $value) Set field value to update.
 */
class Update extends DefaultUpdate {

  /**
   * @inheritDoc
   */
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
