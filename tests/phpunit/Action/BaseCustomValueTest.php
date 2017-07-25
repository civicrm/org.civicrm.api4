<?php

namespace Civi\Test\Api4\Action;

use Civi\Test\Api4\UnitTestCase;
use Civi\Test\Api4\Traits\TableDropperTrait;

abstract class BaseCustomValueTest extends UnitTestCase {

  use TableDropperTrait;

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    $this->dropByPrefix('civicrm_value_mycontact');
    $this->dropTables(array(
      'civicrm_custom_group',
      'civicrm_custom_field',
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value'
    ));
  }
}
