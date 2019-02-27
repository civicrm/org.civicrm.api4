<?php

namespace Civi\Api4;

use Civi\Api4\Action\Entity\Get;
use Civi\Api4\Action\Entity\GetFields;
use Civi\Api4\Action\Entity\GetLinks;
use Civi\Api4\Generic\AbstractEntity;

/**
 * Retrieves information about all Api4 entities.
 *
 * @package Civi\Api4
 */
class Entity extends AbstractEntity {

  /**
   * @return Get
   */
  public static function get() {
    return new Get('Entity');
  }

  /**
   * @return GetFields
   */
  public static function getFields() {
    return new GetFields('Entity');
  }

  /**
   * @return GetFields
   */
  public static function getLinks() {
    return new GetLinks('Entity');
  }

  /**
   * @return array
   */
  public static function permissions() {
    return [];
  }

}
