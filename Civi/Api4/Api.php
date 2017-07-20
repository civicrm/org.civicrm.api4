<?php

namespace Civi\Api4;

use Civi\Api4\Exception\Api4Exception;
use Civi\Api4\Handler\Actions;
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
    foreach ($handlers as $handler) {
      $this->addHandler($handler);
    }
  }

  /**
   * @inheritdoc
   */
  public function request($action, $params = NULL, $checkPermission = TRUE) {
    if (is_array($params)) {
      $params = new ParameterBag($params);
    }

    if ($action === Actions::GET_ACTIONS) {
      return new Response($this->getActions());
    }

    if (!isset($this->handlers[$action])) {
      $err = sprintf(
        '%s::%s is not implemented. Join the team and implement it!',
        $this->getEntity(),
        $action
      );
      throw new Api4Exception($err);
    }

    $handler = $this->handlers[$action];
    $request = new ApiRequest($this->getEntity(), $handler, $params);
    $request->setCheckPermissions($checkPermission);

    return $this->kernel->run($request);
  }

  /**
   * @inheritdoc
   */
  public function getActions() {
    $internalActions = array(Actions::GET_ACTIONS); // handled by this class
    $handlerActions = array_map(function (RequestHandlerInterface $handler) {
      return $handler->getAction();
    }, $this->handlers);

    return array_values(array_merge($internalActions, $handlerActions));
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
