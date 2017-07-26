<?php

namespace Civi\Api4\Service;

/**
 * Contains a list of available API entities.
 */
class EntityRegister {

  /**
   * @var string[]
   */
  protected $entities = array();

  /**
   * @param $entity
   *
   * @return $this
   */
  public function register($entity) {
    if (!in_array($entity, $this->entities)) {
      $this->entities[] = $entity;
    }

    return $this;
  }

  /**
   * @return \string[]
   */
  public function getAll() {
    return $this->entities;
  }
}
