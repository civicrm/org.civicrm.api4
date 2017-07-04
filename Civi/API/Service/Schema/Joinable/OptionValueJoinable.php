<?php

namespace Civi\API\Service\Schema\Joinable;

class OptionValueJoinable extends Joinable {
  /**
   * @var string
   */
  protected $optionGroupName;

  /**
   * @param string $optionGroup
   *   Can be either the option group name or ID
   * @param string $keyColumn
   * @param string|null $alias
   */
  public function __construct($optionGroup, $keyColumn = 'value', $alias = NULL) {
    $this->optionGroupName = $optionGroup;
    $optionValueTable = 'civicrm_option_value';

    // default join alias to option group name, e.g. activity_type
    if (!$alias) {
      $alias = $optionGroup;
    }

    parent::__construct($optionValueTable, $keyColumn, $alias);

    if (!is_numeric($optionGroup)) {
      $subSelect = 'SELECT id FROM civicrm_option_group WHERE name = "%s"';
      $subQuery = sprintf($subSelect, $optionGroup);
      $condition = sprintf('%s.option_group_id = (%s)', $alias, $subQuery);
    } else {
      $condition = sprintf('%s.option_group_id = %d', $alias, $optionGroup);
    }

    $this->addCondition($condition);
  }
}
