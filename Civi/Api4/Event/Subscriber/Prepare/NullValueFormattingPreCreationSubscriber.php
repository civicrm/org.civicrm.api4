<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiRequest;

class NullValueFormattingPreCreationSubscriber extends AbstractPreCreationSubscriber {

  /**
   * @inheritdoc
   */
  public function modify(ApiRequest $request) {
    $this->formalNullInput($request);
  }

  /**
   * Because of the wacky way that database values are saved we need to format
   * some of the values here. In this strange world the string 'null' is used to
   * unset values. Hence if we encounter true null we change it to string 'null'.
   *
   * If we encounter the string 'null' then we assume the user actually wants to
   * set the value to string null. However since the string null is reserved for
   * unsetting values we must change it. Another quirk of the DB_DataObject is
   * that it allows 'Null' to be set, but any other variation of string 'null'
   * will be converted to true null, e.g. 'nuLL', 'NUlL' etc. so we change it to
   * 'Null'.
   *
   * @see \DB_DataObject::update() for how true null is ignored
   * @see \DB_DataObject::insert() for how string null is used to unset values
   *
   * @param ApiRequest $request
   */
  private function formalNullInput(ApiRequest $request) {
    foreach ($request->getAll() as $key => $value) {
      if ('null' === $value) {
        $request->set($key, 'Null');
      }
      elseif (NULL === $value) {
        $request->set($key, 'null');
      }
    }
  }

  /**
   * @inheritdoc
   *
   * @return TRUE as it should apply to all pre-creation requests
   */
  public function applies(ApiRequest $request) {
    return TRUE;
  }

}
