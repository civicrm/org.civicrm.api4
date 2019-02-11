<?php

namespace Civi\Api4\Action\CustomValue;

/**
 * Get fields for a custom group.
 */
class GetActions extends \Civi\Api4\Action\GetActions {

  /**
   * @param $customGroup
   * @return static
   */
  public function setCustomGroup($customGroup) {
    // Ignore value
    return $this;
  }

}
