<?php

namespace Civi\Api4\Action\CustomValue;

/**
 * Given a set of records, will appropriately update the database.
 */
class Replace extends \Civi\Api4\Generic\Action\Basic\Replace {
  use \Civi\Api4\Generic\Action\Traits\CustomValueTrait;

}
