<?php

namespace Civi\Test\Api4\Api;

use Civi\Api4\Api;
use Civi\Api4\ApiKernel;
use Civi\Api4\Handler\GetHandler;
use Civi\Api4\ApiRequest;
use Civi\Api4\Response;
use Civi\Test\Api4\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Argument\Token\TypeToken;

class ContactApiTest extends UnitTestCase {

  public function testGet() {
    $mockKernel = $this->prophesize(ApiKernel::class);
    $response = new Response(['some contact']);
    /** @var TypeToken|ApiRequest $argument */
    $argument = Argument::type(ApiRequest::class);
    $mockKernel->run($argument)->willReturn($response);

    $contactApi = new Api($mockKernel->reveal(), 'Contact');
    $contactApi->addHandler(new GetHandler());
    $result = $contactApi->request('get', NULL, FALSE);

    $this->assertNotEmpty($result->getArrayCopy());
  }
}
