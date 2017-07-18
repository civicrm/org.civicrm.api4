<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Request;

class CustomGroupPreCreationSubscriber extends PreCreationSubscriber {
  /**
   * @param Request $request
   */
  protected function modify(Request $request) {
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

  protected function applies(Request $request) {
    return $request->getEntity() === 'CustomGroup';
  }

}
