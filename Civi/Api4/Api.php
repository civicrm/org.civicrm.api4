<?php

namespace Civi\Api4;

use Civi\Api4\Handler\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class Api implements ApiInterface {

  /**
   * @var string
   *   The entity this API interacts with
   */
  protected $entity;

  /**
   * @var RequestHandlerInterface[]
   *   All available handlers, each for a different action
   */
  protected $handlers;

  /**
   * @var ApiKernel
   */
  private $kernel;

  /**
   * @param ApiKernel $kernel
   * @param string $entity
   * @param RequestHandlerInterface[] $handlers
   */
  public function __construct(ApiKernel $kernel, $entity, $handlers = array()) {
    $this->kernel = $kernel;
    $this->entity = $entity;
    $this->handlers = $handlers;
  }

  /**
   * @param $action
   * @param ParameterBag|NULL $params
   *
   * @return Response
   */
  public function request($action, ParameterBag $params = NULL) {

    if (!isset($this->handlers[$action])) {
      $err = sprintf(
        '%s::%s is not implemented. Join the team and implement it!',
        $this->getEntity(),
        $action
      );
      throw new \Exception($err);
    }

    // todo I'm not sure if the Request should be aware of the handler
    // seems more like a job for the Kernel
    $request = new Request($this->getEntity(), $this->handlers[$action], $params);

    return $this->kernel->run($request);
  }

  /**
   * @inheritdoc
   */
  public function getActions() {
    return array_map(function (RequestHandlerInterface $handler) {
      return $handler->getAction();
    }, $this->handlers);
  }

  /**
   * @inheritdoc
   */
  public function addHandler(RequestHandler $handler) {
    $this->handlers[$handler->getAction()] = $handler;
  }

  /**
   * @inheritdoc
   */
  public function getEntity() {
    return $this->entity;
  }
}
