<?php

namespace Civi\Api4\Action\Contact;

use Civi\Api4\Action\Update as DefaultUpdate;

/**
 * @inheritDoc
 */
class Update extends DefaultUpdate {

  protected function getObjects() {
    // For some reason the contact bao requires this for updating
    $this->addSelect('contact_type');

    return parent::getObjects();
  }

}
