<?php

namespace Civi\Api4\Handler;

use Civi\Api4\Exception\Api4Exception;
use Civi\Api4\Request;
use Civi\Api4\Response;

class DeletionHandler extends GetHandler {

  /**
   * Batch delete function
   *
   * @inheritdoc
   */
  public function handle(Request $request) {
    if (empty($request->get('where'))) {
      throw new Api4Exception("Cannot delete without criteria");
    }

    // run the parent action (get) to get the list
    $request->set('select', array('id'));
    $getResponse = parent::handle($request)->getArrayCopy();

    foreach ($getResponse as $item) {
      // todo confirm we need a new object
      $bao = $this->getBAOForEntity($request->getEntity());
      $bao->id = $item['id'];
      $bao->delete();
    }

    $ids = array_column($getResponse, 'id');
    return new Response($ids);
  }

  /**
   * @return string
   */
  public function getAction() {
    return 'delete';
  }

}
