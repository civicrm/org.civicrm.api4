<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiInterface;
use Civi\Api4\GetParameterBag;
use Civi\Api4\ApiRequest;

class ActivityPreCreationSubscriber extends AbstractPreCreationSubscriber {

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
  public function modify(ApiRequest $request) {
    $activityType = $request->get('activity_type');
    if ($activityType) {
      $params = new GetParameterBag();
      $params->addWhere('name', '=' ,$activityType);
      $params->addWhere('option_group.name', '=', 'activity_type');
      $result = $this->optionValueApi->request('get', $params, FALSE);

      if ($result->count() !== 1) {
        throw new \Exception('Activity type must match a *single* type');
      }

      $request->set('activity_type_id', $result->first()['id']);
    }
  }

  /**
   * @inheritdoc
   */
  public function applies(ApiRequest $request) {
    return $request->getEntity() === 'Activity';
  }

}
