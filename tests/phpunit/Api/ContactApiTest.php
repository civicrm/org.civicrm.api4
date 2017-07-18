<?php

namespace Civi\Test\Api4\Api;

use Civi\API\Event\AuthorizeEvent;
use Civi\Api4\Action\GetHandler;
use Civi\Api4\Api\ContactApi;
use Civi\Api4\ApiKernel;
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

    $contactApi = new ContactApi(new ApiKernel($dispatcher->reveal()));
    $contactApi->addHandler('get', new GetHandler('Contact'));

    $this->assertNotEmpty($contactApi->get()->getArrayCopy());
  }
}
