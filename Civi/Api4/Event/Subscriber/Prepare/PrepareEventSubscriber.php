<?php

namespace Civi\Api4\Event\Subscriber\Prepare;

use Civi\Api4\ApiRequest;

/**
 * Interface common to subscribers to prepare events. Used to separate logic for
 * applying changes to checks if the changes should be done.
 */
interface PrepareEventSubscriber {

  /**
   * Modify the request
   *
   * @param ApiRequest $request
   *
   * @return void
   */
  function modify(ApiRequest $request);

  /**
   * Check if this subscriber should be applied to the request
   *
   * @param ApiRequest $request
   *
   * @return bool
   */
  function applies(ApiRequest $request);
}
