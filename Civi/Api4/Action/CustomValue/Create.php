<?php

namespace Civi\Api4\Action\CustomValue;

use Civi\Api4\Utils\FormattingUtil;
use Civi\Api4\Generic\Result;
use Civi\Api4\Action\Create as DefaultCreate;

/**
 * @inheritDoc
 */
class Create extends DefaultCreate {

  /**
   * @inheritDoc
   */
  public function getEntity() {
    return 'Custom_' . $this->getCustomGroup();
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
   * @inheritDoc
   */
  protected function writeObject($params) {
    FormattingUtil::formatWriteParams($params, $this->getEntity(), $this->getEntityFields());

    return \CRM_Core_BAO_CustomValueTable::setValues($params);
  }

}
