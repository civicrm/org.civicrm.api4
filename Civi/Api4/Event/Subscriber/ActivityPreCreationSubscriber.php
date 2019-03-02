<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Generic\Action\DAOCreate;
use Civi\Api4\OptionValue;

class ActivityPreCreationSubscriber extends PreCreationSubscriber {
  /**
   * @param DAOCreate $request
   * @throws \API_Exception
   * @throws \Exception
   */
  protected function modify(DAOCreate $request) {
    $activityType = $request->getValue('activity_type');
    if ($activityType) {
      $result = OptionValue::get()
        ->setCheckPermissions(FALSE)
        ->addWhere('name', '=', $activityType)
        ->addWhere('option_group.name', '=', 'activity_type')
        ->execute();

      if ($result->count() !== 1) {
        throw new \Exception('Activity type must match a *single* type');
      }

      $request->addValue('activity_type_id', $result->first()['value']);
    }
  }

  /**
   * @param DAOCreate $request
   *
   * @return bool
   */
  protected function applies(DAOCreate $request) {
    return $request->getEntity() === 'Activity';
  }

}
