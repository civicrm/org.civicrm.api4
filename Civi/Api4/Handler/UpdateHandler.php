<?php

namespace Civi\Api4\Handler;

use Civi\Api4\Exception\Api4Exception;
use Civi\Api4\GetParameterBag;
use Civi\Api4\ApiRequest;
use Civi\Api4\Response;

class UpdateHandler extends GetHandler {
  /**
   * @inheritdoc
   */
  public function handle(ApiRequest $request) {
    $targetIds = $this->getTargetIds($request);
    $updated = array();

    foreach ($targetIds as $id) {
      $updateResult = $this->update($request, $id);
      $updated[] = $this->baoToArray($updateResult);
    }

    return new Response($updated);
  }

  /**
   * @inheritdoc
   */
  public function getAction() {
    return 'update';
  }

  /**
   * @param ApiRequest $request
   *
   * @return array
   *   An array of IDs to be updated
   */
  protected function getTargetIds(ApiRequest $request) {
    $wheres = $request->get('where', array());

    if (empty($wheres)) {
      throw new Api4Exception('Update request must have criteria');
    }

    $getParams = new GetParameterBag();
    $getParams->addSelect('id');
    // copy where values
    foreach ($wheres as $where) {
      $getParams->addClause($where);
    }

    $subRequest = new ApiRequest($request->getEntity(), $this, $getParams);
    $response = parent::handle($subRequest);

    return array_column($response->getArrayCopy(), 'id');
  }

  /**
   * Run the update. Override for non-conforming BAOs.
   *
   * @param ApiRequest $request
   * @param $id
   *
   * @return \CRM_Core_DAO
   */
  protected function update(ApiRequest $request, $id) {
    $bao = $this->getBAOForEntity($request->getEntity());
    $params = array_merge(array('id' => $id), $request->getAll());

    return $bao->create($params);
  }
}
