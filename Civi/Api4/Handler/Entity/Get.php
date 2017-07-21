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

namespace Civi\Api4\Handler\Entity;

use Civi\Api4\Request;
use Civi\Api4\RequestHandler;
use Civi\Api4\Response;

/**
 * Get entities
 */
class Get extends RequestHandler {

  /**
   * Scan all api directories to discover entities
   *
   * @param Response $request
   */
  public function handle(Request $request) {
    $entities = array();
    foreach (explode(PATH_SEPARATOR, get_include_path()) as $path) {
      $dir = \CRM_Utils_File::addTrailingSlash($path) . 'Civi/Api4/Entity';
      if (is_dir($dir)) {
        foreach (glob("$dir/*.php") as $file) {
          $matches = array();
          preg_match('/(\w*).php/', $file, $matches);
          $entities[$matches[1]] = $matches[1];
        }
      }
    }
    $entities = array_values($entities);
    if (in_array('BaseEntity', $entities)) {
      unset($entities[array_search('BaseEntity', $entities)]);
    }
    $request->exchangeArray($entities);
  }

}
