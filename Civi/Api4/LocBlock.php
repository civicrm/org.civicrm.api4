<?php

namespace Civi\Api4;

use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Generic\AbstractEntity;

/**
 * Class LocBlock.
 */
class LocBlock extends AbstractEntity {

  public static $entity = 'Location';

  /**
   * @param $action
   * @param $ignore
   *
   * @throws \Civi\API\Exception\NotImplementedException
   *
   * @return mixed
   */
  public static function __callStatic($action, $ignore) {
    $entity = 'LocBlock';
    // Find class for this action.
    $entityAction = "\\Civi\\Api4\\Action\\$entity\\" . ucfirst($action);
    $genericAction = '\Civi\Api4\Action\\' . ucfirst($action);
    if (class_exists($entityAction)) {
      return new $entityAction($entity);
    }
    if (class_exists($genericAction)) {
      return new $genericAction($entity);
    }
    throw new NotImplementedException("Api $entity $action version 4 does not exist.");
  }

}
