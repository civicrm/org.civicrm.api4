<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiRequest;

class CustomGroupPreCreationSubscriber extends AbstractPreCreationSubscriber {
  /**
   * @param ApiRequest $request
   */
  public function modify(ApiRequest $request) {
    $extends = $request->get('extends');
    $title = $request->get('title');
    $name = $request->get('name');

    if (is_string($extends)) {
      $request->set('extends', array($extends));
    }

    if (NULL === $title && $name) {
      $request->set('title', $name);
    }
  }

  public function applies(ApiRequest $request) {
    return $request->getEntity() === 'CustomGroup';
  }

}
