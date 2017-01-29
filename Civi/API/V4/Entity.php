<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */
namespace Civi\API\V4;
use Civi\API\Exception\NotImplementedException;

/**
 * Base class for all api entities.
 *
 * @method static \Civi\API\V4\Action\Get get
 * @method static \Civi\API\V4\Action\GetFields getFields
 * @method static \Civi\API\V4\Action\GetActions getActions
 * @method static \Civi\API\V4\Action\Create create
 * @method static \Civi\API\V4\Action\Update update
 * @method static \Civi\API\V4\Action\Delete delete
 */
abstract class Entity {

  /**
   * Magic method to return the action object for an api.
   *
   * @param string $action
   * @param null $ignore
   * @return Action
   * @throws NotImplementedException
   */
  public static function __callStatic($action, $ignore) {
    // Get entity name from called class
    $entity = substr(static::class, strrpos(static::class, '\\') + 1);
    // Find class for this action
    $entityAction = "\\Civi\\API\\V4\\Entity\\$entity\\" . ucfirst($action);
    $genericAction = '\Civi\API\V4\Action\\' . ucfirst($action);
    if (class_exists($entityAction)) {
      return new $entityAction($entity);
    }
    elseif (class_exists($genericAction)) {
      return new $genericAction($entity);
    }
    throw new NotImplementedException("Api $entity $action version 4 does not exist.");
  }

}
