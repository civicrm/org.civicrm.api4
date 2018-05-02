<?php

namespace Civi\Api4\Service\Spec;

/**
 * Class FieldSpec.
 */
class FieldSpec {
  /**
   * Aliases for the valid data types.
   *
   * @var array
   */
  public static $typeAliases = ['Int' => 'Integer'];

  /**
   * @var mixed
   */
  protected $defaultValue;

  /**
   * @var string
   */
  protected $name;

  /**
   * @var string
   */
  protected $title;

  /**
   * @var string
   */
  protected $description;

  /**
   * @var bool
   */
  protected $required = FALSE;

  /**
   * @var array
   */
  protected $options = [];

  /**
   * @var string
   */
  protected $dataType;

  /**
   * @var string
   */
  protected $fkEntity;

  /**
   * @var int
   */
  protected $serialize;

  /**
   * @param        $name
   * @param string $dataType
   *
   * @throws \Exception
   */
  public function __construct($name, $dataType = 'String') {
    $this->setName($name);
    $this->setDataType($dataType);
  }

  /**
   * @return mixed
   */
  public function getDefaultValue() {
    return $this->defaultValue;
  }

  /**
   * @param mixed $defaultValue
   *
   * @return $this
   */
  public function setDefaultValue($defaultValue) {
    $this->defaultValue = $defaultValue;

    return $this;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param string $title
   *
   * @return $this
   */
  public function setTitle($title) {
    $this->title = $title;

    return $this;
  }

  /**
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;

    return $this;
  }

  /**
   * @return bool
   */
  public function isRequired() {
    return $this->required;
  }

  /**
   * @param bool $required
   *
   * @return $this
   */
  public function setRequired($required) {
    $this->required = $required;

    return $this;
  }

  /**
   * @return string
   */
  public function getDataType() {
    return $this->dataType;
  }

  /**
   * @param $dataType
   *
   * @throws \Exception
   *
   * @return $this
   */
  public function setDataType($dataType) {
    if (\array_key_exists($dataType, self::$typeAliases)) {
      $dataType = self::$typeAliases[$dataType];
    }
    if (!\in_array($dataType, $this->getValidDataTypes())) {
      throw new \Exception(\sprintf('Invalid data type "%s', $dataType));
    }
    $this->dataType = $dataType;

    return $this;
  }

  /**
   * @return int
   */
  public function getSerialize() {
    return $this->serialize;
  }

  /**
   * @param int|null $serialize
   */
  public function setSerialize($serialize) {
    $this->serialize = $serialize;
  }

  /**
   * Add valid types that are not not part of \CRM_Utils_Type::dataTypes.
   *
   * @return array
   */
  private function getValidDataTypes() {
    $extra_types = ['Boolean', 'Text', 'Float', 'Memo'];
    $valid_types = \array_keys(\CRM_Utils_Type::getValidTypes());
    $extra_types = \array_merge($extra_types, $valid_types);
    $extra_types = \array_combine($extra_types, $extra_types);

    return \array_merge(\CRM_Utils_Type::dataTypes(), $extra_types);
  }

  /**
   * @return array
   */
  public function getOptions() {
    return $this->options;
  }

  /**
   * @param array $options
   *
   * @return $this
   */
  public function setOptions($options) {
    $this->options = $options;

    return $this;
  }

  /**
   * @param $option
   */
  public function addOption($option) {
    $this->options[] = $option;
  }

  /**
   * @return string
   */
  public function getFkEntity() {
    return $this->fkEntity;
  }

  /**
   * @param string $fkEntity
   *
   * @return $this
   */
  public function setFkEntity($fkEntity) {
    $this->fkEntity = $fkEntity;

    return $this;
  }

  /**
   * @return array
   */
  public function toArray() {
    $ret = [];
    foreach (\get_object_vars($this) as $key => $val) {
      $key = \strtolower(\preg_replace('/(?=[A-Z])/', '_$0', $key));
      $ret[$key] = $val;
    }

    return $ret;
  }

}
