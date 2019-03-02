<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Generic\Action\DAOCreate;

class CustomGroupPreCreationSubscriber extends PreCreationSubscriber {
  /**
   * @param DAOCreate $request
   */
  protected function modify(DAOCreate $request) {
    $extends = $request->getValue('extends');
    $title = $request->getValue('title');
    $name = $request->getValue('name');

    if (is_string($extends)) {
      $request->addValue('extends', [$extends]);
    }

    if (NULL === $title && $name) {
      $request->addValue('title', $name);
    }
  }

  protected function applies(DAOCreate $request) {
    return $request->getEntityName() === 'CustomGroup';
  }

}
