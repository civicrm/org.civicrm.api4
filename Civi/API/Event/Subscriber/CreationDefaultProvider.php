<?php

namespace Civi\API\Event\Subscriber;

use Civi\API\V4\Action\Create;

class CreationDefaultProvider extends ApiRequestModifier {
  /**
   * @param Create $request
   */
  protected function modify(Create $request) {
    if (NULL === $request->getValue('is_active')) {
      $request->setValue('is_active', 1);
    }
  }
}
