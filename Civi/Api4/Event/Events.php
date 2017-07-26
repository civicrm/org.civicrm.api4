<?php

namespace Civi\Api4\Event;

/**
 * The API kernel dispatches a series of events while processing each API request.
 * For a successful API request, the sequence is RESOLVE => AUTHORIZE => PREPARE => RESPOND.
 * If an exception arises in any stage, then the sequence is aborted and the EXCEPTION
 * event is dispatched.
 *
 * Event subscribers which are concerned about the order of execution should assign
 * a weight to their subscription (such as W_EARLY, W_MIDDLE, or W_LATE).
 * W_LATE).
 */
interface Events {
  /**
   * Determine whether the API request is allowed for the current user.
   * For successful execution, at least one listener must invoke
   * $event->authorize().
   *
   * @see AuthorizeEvent
   */
  const AUTHORIZE = 'civi.api4.authorize';

  /**
   * Apply pre-execution logic
   *
   * @see PrepareEvent
   */
  const PREPARE = 'civi.api4.prepare';

  /**
   * Apply post-execution logic
   *
   * @see RespondEvent
   */
  const RESPOND = 'civi.api4.respond';

  /**
   * Handle any exceptions.
   *
   * @see ExceptionEvent
   */
  const EXCEPTION = 'civi.api4.exception';

  /**
   * Prepare the specification for a request. Fired from within a request to
   * get fields.
   *
   * @see GetSpecEvent
   */
  const GET_SPEC = 'civi.api4.get_spec';

  /**
   * Build the database schema, allow adding of custom joins and tables.
   *
   * @see SchemaMapBuildEvent
   */
  const SCHEMA_MAP_BUILD = 'civi.api4.schema_map.build';
}
