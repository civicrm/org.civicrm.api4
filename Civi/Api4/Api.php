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
  public function __construct(ApiKernel $kernel, $entity, $handlers) {
    $this->kernel = $kernel;
    $this->entity = $entity;
    foreach ($handlers as $handler) {
      $this->addHandler($handler);
    }
  }

  /**
   * @param $action
   * @param ParameterBag|array|NULL $params
   *
   * @return Response
   */
  public function request($action, $params = NULL) {
    if (is_array($params)) {
      $params = new ParameterBag($params);
    }

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
  public function addHandler(RequestHandlerInterface $handler) {
    $action = $handler->getAction();

    if (!isset($this->handlers[$action])) {
      $this->handlers[$action] = $handler;
    }
  }

  /**
   * @inheritdoc
   */
  public function getEntity() {
    return $this->entity;
  }
}
