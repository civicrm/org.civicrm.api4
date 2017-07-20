<?php

namespace Civi\Test\Api4\Api;

use Civi\Api4\Api;
use Civi\Api4\ApiKernel;
use Civi\Api4\Handler\GetHandler;
use Civi\Api4\Request;
use Civi\Api4\Response;
use Civi\Test\Api4\UnitTestCase;
use Prophecy\Argument;

class ContactApiTest extends UnitTestCase {

  public function testGet() {
    $mockKernel = $this->prophesize(ApiKernel::class);
    $response = new Response(['some contact']);
    $mockKernel->run(Argument::type(Request::class))->willReturn($response);

    $contactApi = new Api($mockKernel->reveal(), 'Contact');
    $contactApi->addHandler(new GetHandler('Contact'));

    $this->assertNotEmpty($contactApi->request('get')->getArrayCopy());
  }
}
