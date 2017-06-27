<?php

namespace Civi\API\Service\Schema;

use Civi\API\Api4SelectQuery;

class Joiner {
  /**
   * @var SchemaMap
   */
  protected $schemaMap;

  /**
   * @param SchemaMap $schemaMap
   */
  public function __construct(SchemaMap $schemaMap) {
    $this->schemaMap = $schemaMap;
  }

  /**
   * @param Api4SelectQuery $query
   * @param $targetAlias
   */
  public function join(Api4SelectQuery $query, $targetAlias) {

    $from = $query->getFrom();
    $links = $this->schemaMap->getPath($from, $targetAlias);

    if (empty($links)) {
      throw new \Exception(sprintf('Cannot join %s to %s', $from, $targetAlias));
    }

    $baseTable = $query::MAIN_TABLE_ALIAS;

    foreach ($links as $link) {
      $query->join(
        'LEFT',
        $link->getTargetTable(),
        $link->getAlias(),
        $link->getConditionsForJoin($baseTable)
      );

      $baseTable = $link->getAlias();
    }
  }
}
