<?php

namespace Civi\API\Service\Schema;

use Civi\API\Api4SelectQuery;
use Civi\API\Service\Schema\Joinable\Joinable;

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
   * @return Joinable[]
   *   The path used to make the join
   */
  public function join(Api4SelectQuery $query, $joinPath, $side = 'LEFT') {

    $from = $query->getFrom();
    $stack = explode('.', $joinPath);
    $fullPath = array();

    foreach ($stack as $key => $targetAlias) {
      $links = $this->schemaMap->getPath($from, $targetAlias);

      if (empty($links)) {
        throw new \Exception(sprintf('Cannot join %s to %s', $from, $joinPath));
      } else {
        $fullPath = array_merge($fullPath, $links);
        $lastLink = end($links);
        $from = $lastLink->getTargetTable();
      }
    }

    $baseTable = $query::MAIN_TABLE_ALIAS;

    /** @var Joinable $link */
    foreach ($fullPath as $link) {
      $query->join(
        $side,
        $link->getTargetTable(),
        $link->getAlias(),
        $link->getConditionsForJoin($baseTable)
      );

      $query->addJoinedTable($link);
      $baseTable = $link->getAlias();
    }

    return $fullPath;
  }
}
