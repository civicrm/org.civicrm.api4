<?php

namespace Civi\API\Event;

class Events {

  /**
   * Prepare the specification for a request. Fired from within a request to
   * get fields.
   *
   * @see GetSpecEvent
   */
  const GET_SPEC = 'civi.api.get_spec';
}