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
use Civi\Api4\Response;

class CreationHandler extends RequestHandler {

  /**
   * @inheritDoc
   */
  public function handle(ApiRequest $request) {
    $entityId = $request->get('id');
    $params = $request->getAll();
    $entity = $request->getEntity();

    $params = $this->formatCustomParams($params, $entity, $entityId);
    $createResult = $this->create($entity, $params);

    if (!$createResult) {
      $errMessage = sprintf('%s creation failed', $request->getEntity());
      throw new \API_Exception($errMessage);
    }

    return new Response($this->baoToArray($createResult));
  }

  /**
   * @return string
   */
  public function getAction() {
    return 'create';
  }

  /**
   * @param $params
   * @param $entity
   * @param $entityId
   *
   * @return mixed
   */
  private function formatCustomParams($params, $entity, $entityId) {

    $params['custom'] = array();
    $customParams = array();

    // $customValueID is the ID of the custom value in the custom table for this
    // entity (i guess this assumes it's not a multi value entity)
    foreach ($params as $name => $value) {

      if (strpos($name, '.') === FALSE) {
        continue;
      }

      list($customGroup, $customField) = explode('.', $name);

      $customFieldId = \CRM_Core_BAO_CustomField::getFieldValue(
        \CRM_Core_DAO_CustomField::class,
        $customField,
        'id',
        'name'
      );
      $customFieldType = \CRM_Core_BAO_CustomField::getFieldValue(
        \CRM_Core_DAO_CustomField::class,
        $customField,
        'html_type',
        'name'
      );
      $customFieldExtends = \CRM_Core_BAO_CustomGroup::getFieldValue(
        \CRM_Core_DAO_CustomGroup::class,
        $customGroup,
        'extends',
        'name'
      );

      // todo are we sure we don't want to allow setting to NULL? need to test
      if ($customFieldId && NULL !== $value) {

        if ($customFieldType == 'CheckBox') {
          // this function should be part of a class
          formatCheckBoxField($value, 'custom_' . $customFieldId, $entity);
        }

        \CRM_Core_BAO_CustomField::formatCustomField(
          $customFieldId,
          $customParams,
          $value,
          $customFieldExtends,
          NULL, // todo check when this is needed
          $entityId,
          FALSE,
          FALSE,
          TRUE
        );
      }
    }

    if (!empty($customParams)) {
      $params['custom'] = $customParams;
    }

    return $params;
  }

  /**
   * Allow overriding for BAOs that use a different creation signature.
   *
   * @param string $entity
   * @param array $params
   *
   * @return mixed
   */
  protected function create($entity, $params) {
    $bao = $this->getBAOForEntity($entity);
    $method = $this->getCreationMethodName($bao);
    $createResult = $bao->$method($params);

    return $createResult;
  }

  /**
   * Return the name of the method to be called for creation. Allows overriding
   * for BAOs that use a different method name
   *
   * @param \CRM_Core_DAO $bao
   *
   * @return string
   */
  protected function getCreationMethodName($bao) {
    $method = 'create';
    if (!method_exists($bao, $method)) {
      $method = 'add';
    }

    return $method;
  }
}
