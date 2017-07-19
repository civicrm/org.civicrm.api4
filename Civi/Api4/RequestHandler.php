<?php

namespace Civi\Api4;

use Civi\Api4\Action\RequestHandlerInterface;

/**
 * Base handler for API actions.
 */
abstract class RequestHandler implements RequestHandlerInterface {

  /**
   * @inheritdoc
   */
  abstract public function handle(Request $request);

  /**
   * @inheritdoc
   */
  abstract public function getAction();

  /**
   * @param $entity
   *
   * @return \CRM_Core_DAO
   */
  protected function getBAOForEntity($entity) {
    $className = $this->getBaoName($entity);

    return new $className();
  }

  /**
   * todo replace api3 code
   *
   * @return null|string
   */
  protected function getBaoName($entity) {
    require_once 'api/v3/utils.php';

    return \_civicrm_api3_get_BAO($entity);
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
    $values = array();
    foreach ($fields as $key => $field) {
      $name = $field['name'];
      if (property_exists($bao, $name)) {
        $values[$name] = $bao->$name;
      }
    }
    return $values;
  }

}
