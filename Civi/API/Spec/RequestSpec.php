<?php

namespace Civi\API\Spec;

class RequestSpec {

  /**
   * @var string
   */
  protected $entity;

  /**
   * @var string
   */
  protected $action;

  /**
   * @var FieldSpec[]
   */
  protected $fields;

  /**
   * @param string $entity
   * @param string $action
   */
  public function __construct($entity, $action) {
    $this->entity = $entity;
    $this->action = $action;
    $this->fields = array();
  }

  public function addFieldSpec(FieldSpec $field) {
    $this->fields[] = $field;
  }

  /**
   * @param $name
   *
   * @return FieldSpec|null
   */
  public function getFieldSpecByName($name) {
    foreach ($this->fields as $field) {
      if ($field->getName() === $name) {
        return $field;
      }
    }

    return NULL;
  }

  /**
   * @return FieldSpec[]
   */
  public function getFields() {
    return $this->fields;
  }

  /**
   * @return string
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * @return string
   */
  public function getAction() {
    return $this->action;
  }
}
