<?php
namespace Civi\Api4\Action\Setting;

/**
 * Revert a CiviCRM setting to its default value.
 *
 * @method array getSelect
 * @method $this addSelect(string $name)
 * @method $this setSelect(array $select)
 */
class Revert extends AbstractSettingAction {

  /**
   * Names of settings to revert
   *
   * @var array
   * @required
   */
  protected $select = [];

  public function _run(\Civi\Api4\Generic\Result $result) {
    $this->validateSettings($this->select);
    $settingsBag = \Civi::settings($this->domainId);
    foreach ($this->select as $name) {
      $settingsBag->revert($name);
      $result[$name] = $settingsBag->get($name);
    }
  }

}
