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

namespace Civi\Api4\Handler;

use Civi\Api4\ApiRequest;
use Civi\Api4\RequestHandler;
use Civi\Api4\Query\Api4SelectQuery;
use Civi\Api4\Response;

class GetHandler extends RequestHandler {
  /**
   * @param ApiRequest $request
   *
   * @return Response
   */
  public function handle(ApiRequest $request) {
    $query = new Api4SelectQuery(
      $request->getEntity(),
      $request->getCheckPermissions()
    );

    $query->select = $request->get('select', array());
    $query->where = $request->get('where', array());
    $query->orderBy = $request->get('orderBy', array());
    $query->limit = $request->get('limit', 0);
    $query->offset = $request->get('offset', 0);

    return new Response($query->run());
  }

  /**
   * @inheritdoc
   */
  public function getAction() {
    return 'get';
  }

}
