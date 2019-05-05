<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Utils\ReflectionUtils;

/**
 * Process $current api param for Get actions
 *
 * @see \Civi\Api4\Generic\Traits\IsCurrentTrait
 */
class IsCurrentSubscriber extends Generic\AbstractPrepareSubscriber {

  public function onApiPrepare(PrepareEvent $event) {
    $action = $event->getApiRequest();
    if ($action['version'] == 4) {
      $traits = ReflectionUtils::getTraits($action);
      if (in_array('Civi\Api4\Generic\Traits\IsCurrentTrait', $traits)) {
        if ($action->getCurrent()) {
          $action->addWhere('is_active', '=', '1');
          $action->addClause('OR', ['end_date', 'IS NULL'], ['end_date', '>=', 'now']);
        }
        elseif ($action->getCurrent() === FALSE) {
          $action->addClause('OR', ['is_active', '=', '0'], ['end_date', '<', 'now']);
        }
      }
    }
  }

}
