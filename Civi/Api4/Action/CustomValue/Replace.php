<?php

namespace Civi\Api4\Action\CustomValue;

/**
 * Given a set of records, will appropriately update the database.
 */
class Replace extends \Civi\Api4\Action\Replace {
  use \Civi\Api4\Generic\CustomValueCRUD;

  protected $select = ['id', 'entity_id'];

}
