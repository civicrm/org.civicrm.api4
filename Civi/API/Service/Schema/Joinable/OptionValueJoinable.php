<?php

namespace Civi\API\Service\Schema\Joinable;

class OptionValueJoinable extends Joinable {
  /**
   * @var string
   */
  protected $optionGroupName;

  /**
   * @param string $optionGroupName
   * @param string $keyColumn
   * @param string|null $alias
   */
  public function __construct($optionGroupName, $keyColumn = 'value', $alias = NULL) {
    $this->optionGroupName = $optionGroupName;
    $optionValueTable = 'civicrm_option_value';

    // default join alias to option group name, e.g. activity_type
    if (!$alias) {
      $alias = $optionGroupName;
    }

    parent::__construct($optionValueTable, $keyColumn, $alias);

    $subSelect = 'SELECT id FROM civicrm_option_group WHERE name = "%s"';
    $subQuery = sprintf($subSelect, $optionGroupName);
    $condition = sprintf('%s.option_group_id = (%s)', $alias, $subQuery);
    $this->addCondition($condition);
  }
}
