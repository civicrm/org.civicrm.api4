<?php

namespace Civi\Api4\Service\Schema\Joinable;

class BridgeJoinable extends Joinable {
  /**
   * @var Joinable
   */
  protected $middleLink;
    
    /**
     * BridgeJoinable constructor.
     *
     * @param                                             $targetTable
     * @param                                             $targetColumn
     * @param                                             $alias
     * @param \Civi\Api4\Service\Schema\Joinable\Joinable $middleLink
     */
    public function __construct($targetTable, $targetColumn, $alias, Joinable $middleLink) {
    parent::__construct($targetTable, $targetColumn, $alias);
    $this->middleLink = $middleLink;
  }

  /**
   * @return Joinable
   */
  public function getMiddleLink() {
    return $this->middleLink;
  }

}
