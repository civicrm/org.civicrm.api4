<?php

namespace Civi\Api4;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

abstract class AbstractApi implements ApiInterface {

  /**
   * @var RequestHandler[]
   */
  protected $actions;

  /**
   * @var ApiKernel
   */
  private $kernel;

  /**
   * @param ApiKernel $kernel
   */
  public function __construct($kernel) {
    $this->kernel = $kernel;
  }

  /**
   * @inheritdoc
   */
  public function get(ParameterBag $params = NULL) {
    return $this->doRequest('get', $params);
  }

  /**
   * @inheritdoc
   */
  public function create(ParameterBag $params) {
    return $this->doRequest('create', $params);
  }

  /**
   * @inheritdoc
   */
  public function getActions() {
    return $this->actions;
  }

  /**
   * @param $action
   * @param ParameterBag|NULL $params
   *
   * @return Response
   */
  private function doRequest($action, ParameterBag $params = NULL) {

    if (!isset($this->actions[$action])) {
      $err = sprintf('%s::%s is not implemented', $this->getEntity(), $action);
      throw new \Exception($err);
    }

    // todo I'm not sure if the Request should be aware of the handler
    // seems more like a job for the Kernel
    $request = new Request($this->getEntity(), $this->actions[$action], $params);

    return $this->kernel->run($request);
  }

  /**
   * @inheritdoc
   */
  public function addHandler(RequestHandler $handler) {
    $this->actions[$handler->getAction()] = $handler;
  }

  /**
   * @inheritdoc
   */
  public function getEntity() {
    return substr(strrchr(get_class($this), '\\'), 1, -3);
  }
}
