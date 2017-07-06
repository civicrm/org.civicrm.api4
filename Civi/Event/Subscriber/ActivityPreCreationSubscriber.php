<?php

namespace Civi\API\V4\Event\Subscriber;

use Civi\API\V4\Action\Create;
use Civi\API\V4\Entity\OptionValue;

class ActivityPreCreationSubscriber extends PreCreationSubscriber {
  /**
   * @param Create $request
   */
  protected function modify(Create $request) {
    $activityType = $request->getValue('activity_type');
    if ($activityType) {
      $result = OptionValue::get()
        ->setCheckPermissions(FALSE)
        ->addWhere('name', '=' ,$activityType)
        ->addWhere('option_group.name', '=', 'activity_type')
        ->execute();

      if ($result->count() !== 1) {
        throw new \Exception('Activity type must match a *single* type');
      }

      $request->setValue('activity_type_id', $result->first()['id']);
    }
  }

  /**
   * @param Create $request
   *
   * @return bool
   */
  protected function applies(Create $request) {
    return $request->getEntity() === 'Activity';
  }

}
