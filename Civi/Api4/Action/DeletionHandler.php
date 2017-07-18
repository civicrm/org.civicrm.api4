<?php

namespace Civi\Api4\Action;

use Civi\Api4\Request;

/**
 * "delete" inherits all the abilities of "get"
 */
class DeletionHandler extends GetHandler {

  /**
   * Batch delete function
   * @todo much of this should be abstracted out to a generic batch handler
   */
  public function handle(Request $request) {
    $bao_name = $this->getBaoName($request->getEntity());
    $this->select = array('id');
    $defaults = $this->getParamDefaults();
    if ($defaults['where'] && !array_diff_key($this->where, $defaults['where'])) {
      throw new \API_Exception('Cannot delete with no "where" paramater specified');
    }
    // run the parent action (get) to get the list
    parent::handle($request);
    // Then act on the result
    $ids = array();
    foreach ($request as $item) {
      // todo confirm we need a new object
      $bao = new $bao_name();
      $bao->id = $item['id'];
      // delete it
      $action_result = $bao->delete();
      if ($action_result) {
        $ids[] = $item['id'];
      }
      else {
        // fixme - what happens here???
      }
    }
    $request->exchangeArray($ids);
    return $request;
  }

}
