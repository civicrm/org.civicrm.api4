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
   *   The query object to do the joins on
   * @param string $joinPath
   *   A path of aliases in dot notation, e.g. contact.phone
   * @param string $side
   *   Can be LEFT or INNER
   *
   * @throws \Exception
   */
  public function join(Api4SelectQuery $query, $joinPath, $side = 'LEFT') {

    $from = $query->getFrom();
    $stack = explode('.', $joinPath);
    $fullPath = array();

    foreach ($stack as $targetAlias) {
      $links = $this->schemaMap->getPath($from, $targetAlias);

      if (empty($links)) {
        throw new \Exception(sprintf('Cannot join %s to %s', $from, $joinPath));
      } else {
        $fullPath = array_merge($fullPath, $links);
        $from = end($links)->getTargetTable();
      }
    }

    $baseTable = $query::MAIN_TABLE_ALIAS;

    foreach ($fullPath as $link) {
      $query->join(
        $side,
        $link->getTargetTable(),
        $link->getAlias(),
        $link->getConditionsForJoin($baseTable)
      );

      $baseTable = $link->getAlias();
    }
  }
}
