<?php

namespace Civi\Api4\Action\Address;

use Civi\Api4\Generic\Result;
use Civi\Api4\Action\Create as DefaultCreate;

/**
 * @inheritDoc
 */
class Create extends DefaultCreate {

  /**
   * Optional param to indicate you want the street_address field parsed into individual params
   *
   * @var bool
   */
  protected $streetParsing = TRUE;

  /**
   * Optional param to indicate you want to skip geocoding (useful when importing a lot of addresses at once, the job Geocode and Parse Addresses can execute this task after the import)
   *
   * @var bool
   */
  protected $skipGeocode = FALSE;

  /**
   * When true, apply various fixes to the address before insert.
   *
   * @var bool
   */
  protected $fixAddress = TRUE;

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    foreach (['streetParsing', 'skipGeocode', 'fixAddress'] as $fieldName) {
      $this->values[_civicrm_api_get_entity_name_from_camel($fieldName)] = $this->$fieldName;
    }
    parent::_run($result);
  }


}
