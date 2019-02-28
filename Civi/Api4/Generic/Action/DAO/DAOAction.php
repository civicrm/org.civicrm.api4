<?php
namespace Civi\Api4\Generic\Action\DAO;

use Civi\Api4\Generic\Action\AbstractAction;
use CRM_Utils_Array as UtilsArray;
use Civi\Api4\Utils\FormattingUtil;

abstract class DAOAction extends AbstractAction {

  /* @var array */
  private $entityFields;

  /**
   * @return \CRM_Core_DAO|string
   */
  protected function getBaoName() {
    require_once 'api/v3/utils.php';
    return \_civicrm_api3_get_BAO($this->getEntity());
  }

  /**
   * Extract the true fields from a BAO
   *
   * (Used by create and update actions)
   * @param object $bao
   * @return array
   */
  public static function baoToArray($bao) {
    $fields = $bao->fields();
    $values = [];
    foreach ($fields as $key => $field) {
      $name = $field['name'];
      if (property_exists($bao, $name)) {
        $values[$name] = $bao->$name;
      }
    }
    return $values;
  }

  /**
   * Write a bao object as part of a create/update action.
   *
   * @param array $items
   *   The record to write to the DB.
   * @return array
   *   The record after being written to the DB (e.g. including newly assigned "id").
   * @throws \API_Exception
   */
  protected function writeObjects($items) {
    $baoName = $this->getBaoName();

    // Some BAOs are weird and don't support a straightforward "create" method.
    $oddballs = [
      'Address' => 'add',
      'GroupContact' => 'add',
      'Website' => 'add',
    ];
    $method = UtilsArray::value($this->getEntity(), $oddballs, 'create');
    if (!method_exists($baoName, $method)) {
      $method = 'add';
    }

    $result = [];

    foreach ($items as $item) {
      $entityId = UtilsArray::value('id', $item);
      FormattingUtil::formatWriteParams($item, $this->getEntity(), $this->getEntityFields());
      $this->formatCustomParams($item, $entityId);

      // For some reason the contact bao requires this
      if ($entityId && $this->getEntity() == 'Contact') {
        $item['contact_id'] = $entityId;
      }
      if (method_exists($baoName, $method)) {
        $createResult = $baoName::$method($item);
      }
      else {
        $createResult = $this->genericCreateMethod($item);
      }

      if (!$createResult) {
        $errMessage = sprintf('%s write operation failed', $this->getEntity());
        throw new \API_Exception($errMessage);
      }

      if (!empty($this->reload) && is_a($createResult, 'CRM_Core_DAO')) {
        $createResult->find(TRUE);
      }

      // trim back the junk and just get the array:
      $result[] = $this->baoToArray($createResult);
    }
    return $result;
  }

  /**
   * Fallback when a BAO does not contain create or add functions
   *
   * @param $params
   * @return mixed
   */
  private function genericCreateMethod($params) {
    $baoName = $this->getBaoName();
    $hook = empty($params['id']) ? 'create' : 'edit';

    \CRM_Utils_Hook::pre($hook, $this->getEntity(), UtilsArray::value('id', $params), $params);
    /** @var \CRM_Core_DAO $instance */
    $instance = new $baoName();
    $instance->copyValues($params, TRUE);
    $instance->save();
    \CRM_Utils_Hook::post($hook, $this->getEntity(), $instance->id, $instance);

    return $instance;
  }

  /**
   * Returns schema fields for this entity & action.
   *
   * @return array
   * @throws \API_Exception
   */
  public function getEntityFields() {
    if (!$this->entityFields) {
      $this->entityFields = civicrm_api4($this->getEntity(), 'getFields', ['action' => $this->getAction(), 'includeCustom' => FALSE])
        ->indexBy('name');
    }
    return $this->entityFields;
  }

  /**
   * @param array $params
   * @param int $entityId
   * @return mixed
   */
  private function formatCustomParams(&$params, $entityId) {
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
          formatCheckBoxField($value, 'custom_' . $customFieldId, $this->getEntity());
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

    if ($customParams) {
      $params['custom'] = $customParams;
    }
  }

}