<?php

namespace Civi\Api4\Action\Contact;

use Civi\Api4\Action\Update as DefaultUpdate;

/**
 * @inheritDoc
 */
class Update extends DefaultUpdate {

  // For some reason the contact bao requires this for updating
  protected $select = ['id', 'contact_type'];

}
