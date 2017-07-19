<?php

namespace Civi\Test\Api4\Api;

use Civi\API\Event\AuthorizeEvent;
use Civi\Api4\Api;
use Civi\Api4\ApiKernel;
use Civi\Api4\Handler\GetHandler;
use Civi\Test\Api4\UnitTestCase;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ContactApiTest extends UnitTestCase {

  public function testGet() {
    // create mock dispatcher
    $dispatcher = $this->prophesize(EventDispatcherInterface::class);
    $dispatcher->dispatch(Argument::any(), Argument::any())->will(function ($args) {
      $args[1] instanceof AuthorizeEvent ? $args[1]->authorize() : NULL;
    });

    // todo this test depends on contact data being in the database
    $contactApi = new Api(new ApiKernel($dispatcher->reveal()), 'Contact');
    $contactApi->addHandler(new GetHandler('Contact'));

    $this->assertNotEmpty($contactApi->request('get')->getArrayCopy());
  }
}
