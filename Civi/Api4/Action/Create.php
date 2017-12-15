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

namespace Civi\Api4\Action;

use Civi\Api4\AbstractAction;
use Civi\Api4\Result;

/**
 * Base class for all create actions.
 *
 * @method $this setValues($values) Set all field values.
 */
class Create extends AbstractAction {

  /**
   * Field values to set
   *
   * @var array
   */
  protected $values = [];

  /**
   * Set a field value for the created object.
   *
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setValue($key, $value) {
    $this->values[$key] = $value;
    \Civi::log()->debug('setting $key: ' . json_encode($key, JSON_PRETTY_PRINT));
    return $this;
  }

  /**
   * @param $key
   *
   * @return mixed|null
   */
  public function getValue($key) {
    return isset($this->values[$key]) ? $this->values[$key] : NULL;
  }

  /**
   * @return array
   */
  public function getValues() {
    return $this->values;
  }

  /**
   * @inheritDoc
   */
  public function _run(Result $result) {
    $params = $this->getParams()['values'];

    $entityId = \CRM_Utils_Array::value('id', $params);
    $params = $this->formatCustomParams($params, $this->getEntity(), $entityId);

    $bao_name = $this->getBaoName();
    $bao = new $bao_name();

    // Some BAOs are weird and don't support a straightforward "create" method.
    $oddballs = [
      'Website' => 'add',
      'Address' => 'add',
    ];
    $method = \CRM_Utils_Array::value($this->getEntity(), $oddballs, 'create');
    if (!method_exists($bao, $method)) {
      $method = 'add';
    }
    $createResult = $bao->$method($params);

    if (!$createResult) {
      $errMessage = sprintf('%s creation failed', $this->getEntity());
      throw new \API_Exception($errMessage);
    }

    // trim back the junk and just get the array:
    $resultAsArray = $this->baoToArray($createResult);
    // fixme should return a single row array???
    $result->exchangeArray($resultAsArray);
  }

  /**
   * @param $params
   * @param $entity
   * @param $entityId
   * @return mixed
   */
  private function formatCustomParams($params, $entity, $entityId) {

    $params['custom'] = [];
    $customParams = [];

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

}
