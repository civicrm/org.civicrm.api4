<?php

namespace Civi\Api4\Handler;

use Civi\Api4\Exception\Api4Exception;
use Civi\Api4\ApiRequest;
use Civi\Api4\Response;
use Civi\Api4\Utils\BAOFinder;

class DeletionHandler extends GetHandler {

  /**
   * Batch delete function
   *
   * @inheritdoc
   */
  public function handle(ApiRequest $request) {
    if (empty($request->get('where'))) {
      throw new Api4Exception("Cannot delete without criteria");
    }

    // run the parent action (get) to get the list
    $request->set('select', array('id'));
    $getResponse = parent::handle($request)->getArrayCopy();

    foreach ($getResponse as $item) {
      $bao = BAOFinder::getBAOForEntity($request->getEntity());
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
