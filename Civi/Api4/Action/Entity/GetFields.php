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

namespace Civi\Api4\Action\Entity;

use Civi\API\Exception\UnauthorizedException;
use Civi\Api4\Action\GetFields as GenericGetFields;
use Civi\Api4\Entity;
use Civi\Api4\Generic\Result;

/**
 * Get fields for all entities.
 */
class GetFields extends GenericGetFields {

  /**
   * @param \Civi\Api4\Generic\Result $result
   *
   * @throws \API_Exception
   * @throws \Civi\API\Exception\NotImplementedException
   * @throws \ReflectionException
   */
  public function _run(Result $result) {
    $action = $this->getAction();
    $includeCustom = $this->getIncludeCustom();
    try {
      $entities = Entity::get()->execute();
    }
    catch (UnauthorizedException $e) {
    }
    foreach ($entities as $entity) {
      $data = ['entity' => $entity, 'fields' => []];
      // Prevent infinite recursion.
      if ('Entity' !== $entity) {
        $data['fields'] = (array) civicrm_api4(
         $entity,
         'getFields',
         ['action' => $action, 'includeCustom' => $includeCustom]
        );
      }
      $result[] = $data;
    }
  }

}
