<?php

namespace Civi\API\V4\Entity\CustomGroup;

use Civi\API\V4\Action;

class Create extends Action\Create {
  /**
   * @param string $key
   * @param mixed $value
   *
   * @return $this
   */
  public function setValue($key, $value) {
    if ($key === 'extends' && is_string($value)) {
      $value = [$value];
    }

    return parent::setValue($key, $value);
  }

}
