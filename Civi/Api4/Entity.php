<?php

namespace Civi\Api4;

/**
 * Retrieves information about all Api4 entities.
 *
 * @package Civi\Api4
 */
class Entity extends Generic\AbstractEntity {

  /**
   * @return Action\Entity\Get
   */
  public static function get() {
    return new Action\Entity\Get('Entity');
  }

  /**
   * @return Action\Entity\GetFields
   */
  public static function getFields() {
    return new Action\Entity\GetFields('Entity');
  }

  /**
   * @return Action\Entity\GetFields
   */
  public static function getLinks() {
    return new Action\Entity\GetLinks('Entity');
  }

  /**
   * @return array
   */
  public static function permissions() {
    return [];
  }

}
