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
namespace Civi\API\V4\Action;
use Civi\API\Result;
use Civi\API\V4\Action;

/**
 * Base class for all create actions.
 *
 * @method $this setValues(array) Set all field values.
 */
class Create extends Action {

  /**
   * Field values to set
   *
   * @var array
   */
  protected $values = array();

  /**
   * Bao object based on the entity
   *
   * @var object
   */
  protected $bao;

  /**
   * Action constructor.
   * @param string $entity
   */
  public function __construct($entity) {
    parent::__construct($entity);
    $bao_name = $this->getBaoName();
    $this->bao = new $bao_name();
  }

  /**
   * Set a field value for the created object.
   *
   * @param string $key
   * @param mixed $value
   * @return $this
   */
  public function setValue($key, $value) {
    $this->values[$key] = $value;
    \Civi::log()->debug('setting $key: '.json_encode($key,JSON_PRETTY_PRINT));
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
   * @inheritDoc
   */
  public function _run(Result $result) {
    $params = $this->getParams()['values'];

    $entityId = \CRM_Utils_Array::value('id', $params);
    $params = $this->formatCustomParams($params, $this->getEntity(), $entityId);

    // get a bao back from the standard factory method
    $createResult = $this->bao->create($params);

    if (!$createResult) {
      $errMessage = sprintf('%s creation failed', $this->getEntity());
      throw new \API_Exception($errMessage);
    }

    // trim back the junk and just get the array:
    $resultAsArray = $this->baoToArray($createResult);
    // fixme should return a single row array???
    $result->exchangeArray($resultAsArray);
  }

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

      // todo custom value ID is needed if edit
      $customValueID = NULL;

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
          $customValueID,
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
