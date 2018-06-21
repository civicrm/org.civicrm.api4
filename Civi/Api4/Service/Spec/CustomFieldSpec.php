<?php

namespace Civi\Api4\Service\Spec;

class CustomFieldSpec extends FieldSpec {
  /**
   * @var int
   */
  protected $customFieldId;

  /**
   * @var int
   */
  protected $customGroupId;

  public function __construct($name, $dataType) {
    if ($dataType === 'ContactReference') {
      $dataType = 'Integer';
      $this->setFkEntity('Contact');
    }
    parent::__construct($name, $dataType);
  }

  /**
   * @return int
   */
  public function getCustomFieldId() {
    return $this->customFieldId;
  }

  /**
   * @param int $customFieldId
   *
   * @return $this
   */
  public function setCustomFieldId($customFieldId) {
    $this->customFieldId = $customFieldId;

    return $this;
  }

  /**
   * @return int
   */
  public function getCustomGroupId() {
    return $this->customGroupId;
  }

  /**
   * @param int $customGroupId
   *
   * @return $this
   */
  public function setCustomGroupId($customGroupId) {
    $this->customGroupId = $customGroupId;

    return $this;
  }

}
