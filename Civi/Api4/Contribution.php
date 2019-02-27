<?php

namespace Civi\Api4;
use Civi\Api4\Generic\DAOEntity;

/**
 * Contribution entity.
 *
 * @package Civi\Api4
 */
class Contribution extends DAOEntity {

  /**
   * @return \Civi\Api4\Action\Contribution\Create
   */
  public static function create() {
    return new \Civi\Api4\Action\Contribution\Create();
  }

}
