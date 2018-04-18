<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\API\Event\PrepareEvent;
use Civi\Api4\Action\Create;
use Civi\Api4\Action\Update;

/**
 * Class NullValueFormattingPresaveSubscriber.
 */
class NullValueFormattingPresaveSubscriber extends AbstractPrepareSubscriber {

  /**
   * @param PrepareEvent $event
   */
  public function onApiPrepare(PrepareEvent $event) {
    $apiRequest = $event->getApiRequest();
    if ($apiRequest instanceof Create || $apiRequest instanceof Update) {
      $this->formalNullInput($apiRequest);
    }
  }

  /**
   * Because of the wacky way that database values are saved we need to format
   * some of the values here. In this strange world the string 'null' is used
   * to
   * unset values. Hence if we encounter true null we change it to string
   * 'null'.
   *
   * If we encounter the string 'null' then we assume the user actually wants
   * to
   * set the value to string null. However since the string null is reserved
   * for
   * unsetting values we must change it. Another quirk of the DB_DataObject is
   * that it allows 'Null' to be set, but any other variation of string 'null'
   * will be converted to true null, e.g. 'nuLL', 'NUlL' etc. so we change it
   * to
   * 'Null'.
   *
   * @see \DB_DataObject::update() for how true null is ignored
   * @see \DB_DataObject::insert() for how string null is used to unset values
   *
   * @param Create $request
   */
  private function formalNullInput($request) {
    foreach ($request->getValues() as $key => $value) {
      if ('null' === $value) {
        $request->addValue($key, 'Null');
      }
      elseif (NULL === $value) {
        $request->addValue($key, 'null');
      }
    }
  }
}
