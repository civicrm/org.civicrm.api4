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

namespace Civi\Api4;

use Civi\Api4\Action\Entity\Get;
use Civi\Api4\Action\Entity\GetFields;
use Civi\Api4\Action\Entity\GetLinks;
use Civi\Api4\Action\GetActions;

/**
 * Meta entity.
 */
class Entity {

  /**
   * @throws \ReflectionException
   *
   * @return Get
   */
  public static function get() {
    return new Get('Entity');
  }

  /**
   * @throws \ReflectionException
   *
   * @return GetActions
   */
  public static function getActions() {
    return new GetActions('Entity');
  }

  /**
   * @throws \ReflectionException
   *
   * @return GetFields
   */
  public static function getFields() {
    return new GetFields('Entity');
  }

  /**
   * @throws \ReflectionException
   *
   * @return GetLinks
   */
  public static function getLinks() {
    return new GetLinks('Entity');
  }
}
