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

use Civi\Api4\Request;
use Civi\Api4\RequestHandler;
use Civi\Api4\Response;
use Civi\Api4\Service\Spec\SpecFormatter;
use Civi\Api4\Service\Spec\SpecGatherer;

/**
 * Get fields for an entity
 */
class GetFieldsHandler extends RequestHandler {

  /**
   * @var SpecGatherer
   */
  protected $specGatherer;

  /**
   * @param SpecGatherer $specGatherer
   */
  public function __construct(SpecGatherer $specGatherer) {
    $this->specGatherer = $specGatherer;
  }

  /**
   * @inheritdoc
   */
  public function handle(Request $request) {
    $action = $request->get('action');
    $spec = $this->specGatherer->getSpec($request->getEntity(), $action);
    $fields = SpecFormatter::specToArray($spec)['fields'];

    return new Response($fields);
  }

  /**
   * @inheritdoc
   */
  public function getAction() {
    return 'getFields';
  }

}
