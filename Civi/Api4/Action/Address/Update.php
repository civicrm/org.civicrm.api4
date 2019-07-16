<?php

namespace Civi\Api4\Action\Address;

use Civi\Api4\Generic\Result;

/**
 * @inheritDoc
 */
class Update extends \Civi\Api4\Generic\DAOUpdateAction {
  use AddressSaveTrait;
}
