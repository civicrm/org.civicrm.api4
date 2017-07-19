<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\ApiInterface;
use Civi\Api4\GetParameterBag;
use Civi\Api4\Request;

class ActivityPreCreationSubscriber extends PreCreationSubscriber {

  /**
   * @var ApiInterface
   */
  protected $optionValueApi;

  /**
   * @param ApiInterface $optionValueApi
   */
  public function __construct(ApiInterface $optionValueApi) {
    $this->optionValueApi = $optionValueApi;
  }

  /**
   * @inheritdoc
   */
  protected function modify(Request $request) {
    $activityType = $request->get('activity_type');
    if ($activityType) {
      $params = new GetParameterBag();
      $params->addWhere('name', '=' ,$activityType);
      $params->addWhere('option_group.name', '=', 'activity_type');
      $result = $this->optionValueApi->request('get', $params);

      if ($result->count() !== 1) {
        throw new \Exception('Activity type must match a *single* type');
      }

      $request->set('activity_type_id', $result->first()['id']);
    }
  }

  /**
   * @inheritdoc
   */
  protected function applies(Request $request) {
    return $request->getEntity() === 'Activity';
  }

}
