<?php
namespace Civi\Api4\Action\Setting;

/**
 * Base class for setting actions.
 *
 * @method int getDomainId
 * @method $this setDomainId(int $domainId)
 */
abstract class AbstractSettingAction extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Domain id of setting. Leave NULL for default domain.
   *
   * @var int
   */
  protected $domainId;

  /**
   * Ensure setting exists.
   *
   * @param array|string $names
   * @throws \API_Exception
   */
  protected function validateSettings($names) {
    $allSettings = \Civi\Core\SettingsMetadata::getMetadata([], $this->domainId);
    foreach ((array) $names as $name) {
      if (!isset($allSettings[$name])) {
        throw new \API_Exception('Unknown setting: ' . $name);
      }
    }
  }

}
