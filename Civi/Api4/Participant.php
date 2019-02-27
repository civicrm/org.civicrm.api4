<?php

namespace Civi\Api4;
use Civi\Api4\Generic\DAOEntity;

/**
 * Participant entity.
 *
 * @package Civi\Api4
 */
class Participant extends DAOEntity {

  /**
   * @return \Civi\Api4\Action\Participant\Get
   */
  public static function get() {
    return new \Civi\Api4\Action\Participant\Get();
  }

}
