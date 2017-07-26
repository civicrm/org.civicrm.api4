<?php

namespace Civi\Api4;

use Civi\Api4\Handler\RequestHandlerInterface;

/**
 * Base handler for API actions.
 */
abstract class RequestHandler implements RequestHandlerInterface {

  /**
   * @inheritdoc
   */
  abstract public function handle(ApiRequest $request);

  /**
   * @inheritdoc
   */
  abstract public function getAction();

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
