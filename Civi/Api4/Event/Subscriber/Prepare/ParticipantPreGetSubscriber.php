<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiRequest;

class ParticipantPreGetSubscriber extends AbstractPreGetSubscriber {
  /**
   * @inheritdoc
   */
  public function modify(ApiRequest $request) {
    $wheres = $request->get('where', array());
    $whereFields = array_column($wheres, 0);

    if (!in_array('is_test', $whereFields)) {
      $wheres[] = array('is_test', '=', 0);
      $request->set('where', $wheres);
    }
  }

  /**
   * @inheritdoc
   */
  public function applies(ApiRequest $request) {
    return $request->getEntity() === 'Participant';
  }
}
