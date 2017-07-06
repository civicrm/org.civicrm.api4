<?php

namespace Civi\API\V4\Service\Spec;

class FieldSpec {
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
  protected $options = array();

  /**
   * @var string
   */
  protected $dataType;

  /**
   * Aliases for the valid data types
   *
   * @var array
   */
  public static $typeAliases = array(
    'Int' => 'Integer'
  );

  /**
   * @param $name
   * @param $dataType
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
   * @return $this
   * @throws \Exception
   */
  public function setDataType($dataType) {
    if (array_key_exists($dataType, self::$typeAliases)) {
      $dataType = self::$typeAliases[$dataType];
    }

     if (!in_array($dataType, $this->getValidDataTypes())) {
       throw new \Exception(sprintf('Invalid data type "%s', $dataType));
     }

    $this->dataType = $dataType;

    return $this;
  }

  /**
   * Add valid types that are not not part of \CRM_Utils_Type::dataTypes
   *
   * @return array
   */
  private function getValidDataTypes() {
    $extraTypes = array('Boolean', 'Text');
    $extraTypes = array_combine($extraTypes, $extraTypes);

    return array_merge(\CRM_Utils_Type::dataTypes(), $extraTypes);
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
}
