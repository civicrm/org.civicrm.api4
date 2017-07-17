<?php

namespace Civi\Api4\Service\Schema\Joinable\Activity;

use Civi\Api4\Service\Schema\Joinable\Joinable;

class ActivityContactJoinable extends Joinable {
  /**
   * @var string
   */
  protected $baseTable = 'civicrm_activity';

  /**
   * @var string
   */
  protected $baseColumn = 'id';

  /**
   * @param $recordType
   *   The type of the contact, e.g. 'Activity Assignees'
   * @param $alias
   *   The join alias to be used
   */
  public function __construct($recordType, $alias) {
    $optionValueTable = 'civicrm_option_value';
    $optionGroupTable = 'civicrm_option_group';

    $subSubSelect = sprintf(
      'SELECT id FROM %s WHERE name = "%s"',
      $optionGroupTable,
      'activity_contacts'
    );

    $subSelect = sprintf(
      'SELECT value FROM %s WHERE name = "%s" AND option_group_id = (%s)',
      $optionValueTable,
      $recordType,
      $subSubSelect
    );

    $this->addCondition(sprintf('%s.record_type_id = (%s)', $alias, $subSelect));
    parent::__construct('civicrm_activity_contact', 'activity_id', $alias);
  }
}
