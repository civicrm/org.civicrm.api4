<?php

namespace Civi\Api4\Service\Schema\Joinable;

/**
 * Class CustomGroupJoinable.
 */
class CustomGroupJoinable extends Joinable {

  /**
   * @var string
   */
  protected $joinSide = self::JOIN_SIDE_LEFT;

  /**
   * @param      $targetTable
   * @param      $alias
   * @param bool $isMultiRecord
   */
  public function __construct($targetTable, $alias, $isMultiRecord = FALSE) {
    parent::__construct($targetTable, 'entity_id', $alias);
    $this->joinType = $isMultiRecord ?
      self::JOIN_TYPE_ONE_TO_MANY : self::JOIN_TYPE_ONE_TO_ONE;
  }
}
