<?php
namespace Civi\Api4\Action\Setting;

/**
 * Set the value of one or more CiviCRM settings.
 *
 * @method array getValues
 * @method $this setValues(array $value)
 * @method $this addValue(string $name, mixed $value)
 */
class Set extends AbstractSettingAction {

  /**
   * Setting names/values to set.
   *
   * @var mixed
   * @required
   */
  protected $values = [];

  public function _run(\Civi\Api4\Generic\Result $result) {
    $meta = $this->validateSettings(array_keys($this->values));
    foreach ($this->values as $name => $value) {
      if (isset($value) && !empty($meta[$name]['serialize'])) {
        $value = \CRM_Core_DAO::serializeField($value, $meta[$name]['serialize']);
      }
      \Civi::settings($this->domainId)->set($name, $value);
    }
    $result->exchangeArray($this->values);
  }

}
