<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Api\OptionValueApi;
use Civi\Api4\Request;

class ActivityPreCreationSubscriber extends PreCreationSubscriber {
  /**
   * @inheritdoc
   */
  protected function modify(Request $request) {
    $activityType = $request->get('activity_type');
    if ($activityType) {
      $result = OptionValueApi::get() // todo make dependency
        ->setCheckPermissions(FALSE)
        ->addWhere('name', '=' ,$activityType)
        ->addWhere('option_group.name', '=', 'activity_type')
        ->execute();

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
