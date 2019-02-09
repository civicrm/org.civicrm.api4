<?php

namespace Civi\Api4\Action\Navigation;

use Civi\Api4\Action\Get as DefaultGet;

/**
 * @inheritDoc
 *
 * Fetch items from the navigation menu. By default this will fetch items from the current domain.
 */
class Get extends DefaultGet {

  /**
   * @inheritDoc
   */
  protected $where = [
    ['domain_id', '=', 'current_domain'],
  ];

}
