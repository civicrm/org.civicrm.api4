<?php

namespace Civi\Api4\Handler\Entity;

use Civi\Api4\Handler\GetHandler;
use Civi\Api4\ApiRequest;
use Civi\Api4\Response;
use Civi\Api4\Service\EntityRegister;

class EntityGetHandler extends GetHandler {

  /**
   * @var EntityRegister
   */
  protected $entityRegister;

  /**
   * @param EntityRegister $entityRegister
   */
  public function __construct(EntityRegister $entityRegister) {
    $this->entityRegister = $entityRegister;
  }

  /**
   * @inheritdoc
   */
  public function handle(ApiRequest $request) {
    return new Response($this->entityRegister->getAll());
  }

}
