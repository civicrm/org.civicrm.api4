<?php

namespace Civi\Api4\Service\Schema\Joinable\Contact;

use Civi\Api4\Service\Schema\Joinable\Joinable;

class ActivityContactSourceJoinable extends Joinable {
  /**
   * @var string
   */
  protected $baseTable = 'civicrm_contact';

  /**
   * @var
   */
  protected $baseColumn = 'id';

  /**
   * @param $alias
   */
  public function __construct($alias) {
    $this->addSourceConditional($alias);
    parent::__construct('civicrm_activity_contact', 'contact_id', $alias);
  }

  /**
   * @param $alias
   */
  private function addSourceConditional($alias) {
    $subSubSelect = sprintf(
      'SELECT id FROM %s WHERE name = "%s"',
      'civicrm_option_group',
      'activity_contacts'
    );
    $subSelect = sprintf(
      'SELECT value FROM %s WHERE name = "%s" AND option_group_id = (%s)',
      'civicrm_option_value',
      'Activity Source',
      $subSubSelect
    );
    $this->addCondition(sprintf('%s.record_type_id = (%s)', $alias, $subSelect));
  }
}
