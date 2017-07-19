<?php

namespace Civi\Api4\Action;

use Civi\Api4\Request;

class UpdateHandler extends GetHandler {

  /**
   * @inheritdoc
   */
  public function handle(Request $request) {
    $bao_name = $this->getBaoName($request->getEntity());
    // First run the parent action (get)
    $this->select = array('id');
    $patch_values = $this->getParams()['values'];
    parent::handle($request);
    // Then act on the result
    $updated_results = array();
    foreach ($request as $item) {
      // todo confirm we need a new object
      $bao = new $bao_name();
      $patch = $item + $patch_values;
      // update it
      $update_result_bao = $this->getBAOForEntity($request->getEntity())->create($patch);
      // trim back the junk and just get the array:
      $updated_results[] = $this->baoToArray($update_result_bao);
    }
    $request->exchangeArray($updated_results);
  }

  /**
   * @inheritdoc
   */
  public function getAction() {
    return 'update';
  }
}
