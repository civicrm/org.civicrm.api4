<?php

namespace Civi\API\Service\Schema;

use Civi\API\Api4SelectQuery;

class Joiner {

  /**
   * @var SchemaMap
   */
  protected $schemaMap;

  /**
   * @param Api4SelectQuery $query
   * @param $targetAlias
   */
  public function join(Api4SelectQuery $query, $targetAlias) {

    // todo get from from query
    $from = $query->getFrom();
    $links = $this->schemaMap->getPath($from, $targetAlias);

    foreach ($links as $link) {
      $query->join(
        'LEFT',
        $link->getTargetTable(),
        $link->getAlias(),
        $link->getConditions()
      );
    }
  }
}
