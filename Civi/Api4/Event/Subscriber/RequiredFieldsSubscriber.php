<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2017                                |
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

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;

/**
 * For any API requests that correspond to a Doctrine entity
 * ($apiRequest['doctrineClass']), check permissions specified in
 * Civi\API\Annotation\Permission.
 */
class RequiredFieldsSubscriber extends AbstractPrepareSubscriber {

  /**
   * @param PrepareEvent $event
   * @throws \Exception
   */
  public function onApiPrepare(PrepareEvent $event) {
    /** @var \Civi\Api4\AbstractAction $apiRequest */
    $apiRequest = $event->getApiRequest();
    if (is_a($apiRequest, 'Civi\Api4\AbstractAction')) {
      $paramInfo = $apiRequest->getParamInfo();
      foreach ($paramInfo as $param => $info) {
        $getParam = 'get' . ucfirst($param);
        if (!empty($info['required']) && !$apiRequest->$getParam()) {
          throw new \Exception('Parameter "' . $param . '" is required');
        }
      }
    }
  }

}
