<?php

namespace Civi\Api4\Service\Schema\Joinable;

class EntityTagJoinable  extends Joinable {
  /**
   * @var int
   */
  protected $joinType = self::JOIN_TYPE_ONE_TO_MANY;

  /**
   * @param $baseTable
   * @param $joinAlias
   */
  public function __construct($baseTable, $joinAlias) {
    $this->setBaseTable($baseTable);
    $this->setBaseColumn('id');
    parent::__construct('civicrm_entity_tag', 'entity_id', $joinAlias);
    $this->addCondition(sprintf('%s.entity_table = "%s"', $joinAlias, $baseTable));
  }
}
