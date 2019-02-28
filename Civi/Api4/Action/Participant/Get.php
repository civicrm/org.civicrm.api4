<?php

namespace Civi\Api4\Action\Participant;

use Civi\Api4\Generic\Action\DAO\Get as DefaultGet;

/**
 * @inheritDoc
 */
class Get extends DefaultGet {

  /**
   * @inheritDoc
   * $example->addWhere('contact_id.contact_type', 'IN', array('Individual', 'Household'))
   */
  protected $where = [
    ['is_test', '=', 0],
  ];

}
