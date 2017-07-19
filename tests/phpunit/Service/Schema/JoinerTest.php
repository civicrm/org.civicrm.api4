<?php

namespace Civi\Test\Api4\Service\Schema;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Api4\Service\Schema\Joiner;
use Civi\Test\Api4\UnitTestCase;

class JoinerTest extends UnitTestCase {

  public function testMaxJoinLimit() {
    $this->setExpectedException(
      \API_Exception::class,
      'Cannot join more than 5 levels'
    );

    $joiner = new Joiner(\Civi::service('schema_map'));
    $query = new Api4SelectQuery('Activity', FALSE);
    $joiner->join($query,'activity_contacts.contact.addresses.country.address_format');
  }
}