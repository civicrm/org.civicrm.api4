<?php

namespace Civi\API\V4\Action;

use Civi\Api\TableDropperTrait;
use Civi\API\V4\UnitTestCase;

abstract class BaseCustomValueTest extends UnitTestCase {

  use TableDropperTrait;

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    $cleanup_params = array(
      'tablesToTruncate' => array(
        'civicrm_custom_group',
        'civicrm_custom_field',
        'civicrm_contact',
        'civicrm_option_group',
        'civicrm_option_value'
      ),
    );

    $this->dropByPrefix('civicrm_value_mycontact');
    $this->cleanup($cleanup_params);
  }

}
