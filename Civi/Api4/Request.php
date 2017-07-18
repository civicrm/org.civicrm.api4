<?php

namespace Civi\Api4;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class Request implements \ArrayAccess {
  /**
   * @var string
   */
  protected $entity;

  /**
   * @var RequestHandler
   */
  protected $action;

  /**
   * @var ParameterBag
   */
  protected $params;

  /**
   * @var bool
   */
  protected $checkPermission = TRUE;

  /**
   * @param $entity
   * @param $action
   * @param ParameterBag|NULL $params
   */
  public function __construct(
    $entity,
    RequestHandler $action,
    ParameterBag $params = NULL
  ) {
    $this->entity = $entity;
    $this->action = $action;
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
   * @return RequestHandler
   */
  public function getAction() {
    return $this->action;
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
  public function isCheckPermission() {
    return $this->checkPermission;
  }

  /**
   * @param bool $checkPermission
   *
   * @return $this
   */
  public function setCheckPermission($checkPermission) {
    $this->checkPermission = $checkPermission;

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
