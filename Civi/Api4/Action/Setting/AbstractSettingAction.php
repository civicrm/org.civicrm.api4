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
   * @return array
   * @throws \API_Exception
   */
  protected function validateSettings($names) {
    $allSettings = \Civi\Core\SettingsMetadata::getMetadata([], $this->domainId);
    $invalid = array_diff($names, array_keys($allSettings));
    if ($invalid) {
      throw new \API_Exception('Unknown settings: ' . implode(', ', $invalid));
    }
    return $allSettings;
  }

}
