<?php

namespace Civi\Api4\Action\Address;

use Civi\Api4\Generic\Result;

/**
 * @inheritDoc
 */
class Update extends \Civi\Api4\Generic\DAOUpdateAction {

  /**
   * Optional param to indicate you want the street_address field parsed into individual params
   *
   * @var bool
   */
  protected $streetParsing = FALSE;

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
    if ($this->streetParsing && !empty($this->values['street_address'])) {
      $this->values = array_merge($this->values, \CRM_Core_BAO_Address::parseStreetAddress($this->values['street_address']));
    }
    $this->values['skip_geocode'] = $this->skipGeocode;
    parent::_run($result);
  }

}
