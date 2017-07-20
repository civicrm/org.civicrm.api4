<?php

namespace Civi\Api4;

use Civi\Api4\Handler\RequestHandlerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class ApiRequest implements \ArrayAccess {
  /**
   * @var string
   */
  protected $entity;

  /**
   * @var RequestHandlerInterface
   */
  protected $handler;

  /**
   * @var ParameterBag
   */
  protected $params;

  /**
   * @var bool
   */
  protected $checkPermissions = TRUE;

  /**
   * @param $entity
   * @param RequestHandlerInterface $handler
   * @param ParameterBag|NULL $params
   */
  public function __construct(
    $entity,
    $handler,
    ParameterBag $params = NULL
  ) {
    $this->entity = $entity;
    $this->handler = $handler;
    $this->params = $params ?: new ParameterBag();
    $this->set('version', 4);
  }

  /**
   * @return string
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * @return RequestHandlerInterface
   */
  public function getHandler() {
    return $this->handler;
  }

  /**
   * @return string
   */
  public function getAction() {
    return $this->handler->getAction();
  }

  /**
   * @param $name
   * @param $value
   */
  public function set($name, $value) {
    $this->offsetSet($name, $value);
  }

  /**
   * @param $name
   * @param null $default
   *
   * @return mixed
   */
  public function get($name, $default = NULL) {
    if ($this->offsetExists($name)) {
      return $this->offsetGet($name);
    }

    return $default;
  }

  /**
   * @return array
   */
  public function getAll() {
    return $this->params->all();
  }

  /**
   * @return bool
   */
  public function getCheckPermissions() {
    return $this->checkPermissions;
  }

  /**
   * @param bool $checkPermissions
   *
   * @return $this
   */
  public function setCheckPermissions($checkPermissions) {
    $this->checkPermissions = $checkPermissions;

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function offsetExists($offset) {
    return $this->params->has($offset);
  }

  /**
   * @inheritdoc
   */
  public function offsetGet($offset) {
    return $this->params->get($offset);
  }

  /**
   * @inheritdoc
   */
  public function offsetSet($offset, $value) {
    $this->params->set($offset, $value);
  }

  /**
   * @inheritdoc
   */
  public function offsetUnset($offset) {
    $this->params->remove($offset);
  }

}
