<?php

namespace Civi\Api4\Event;

/**
 * Class Events.
 */
class Events
{
    /**
     * Prepare the specification for a request. Fired from within a request to
     * get fields.
     *
     * @see GetSpecEvent
     */
    public const GET_SPEC = 'civi.api.get_spec';

    /**
     * Build the database schema, allow adding of custom joins and tables.
     */
    public const SCHEMA_MAP_BUILD = 'api.schema_map.build';

    /**
     * Alter query results of APIv4 select query.
     */
    public const POST_SELECT_QUERY = 'api.select_query.post';
}
