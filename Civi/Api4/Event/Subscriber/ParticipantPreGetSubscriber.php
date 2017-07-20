<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Request;

class ParticipantPreGetSubscriber extends AbstractPreGetSubscriber {
  /**
   * @inheritdoc
   */
  protected function modify(Request $request) {
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
  protected function applies(Request $request) {
    return $request->getEntity() === 'Participant';
  }
}
