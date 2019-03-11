<?php

namespace Civi\Api4\Generic;

use Civi\Api4\Service\Spec\SpecGatherer;
use Civi\Api4\Service\Spec\SpecFormatter;

/**
 * Get fields for an entity.
 *
 * @method $this setIncludeCustom(bool $value)
 * @method bool getIncludeCustom()
 * @method $this setloadOptions(bool $value)
 * @method bool getloadOptions()
 * @method $this setAction(string $value)
 */
class DAOGetFieldsAction extends BasicGetAction {

  /**
   * Include custom fields for this entity, or only core fields?
   *
   * @var bool
   */
  protected $includeCustom = TRUE;

  /**
   * Fetch option lists for fields?
   *
   * @var bool
   */
  protected $loadOptions = FALSE;

  /**
   * Which attributes of the fields should be returned?
   *
   * @options name, title, description, default_value, required, options, data_type, fk_entity, serialize, custom_field_id, custom_group_id
   *
   * @var array
   */
  protected $select = [];

  /**
   * @var string
   */
  protected $action = 'get';

  protected function getRecords() {
    $fields = $this->_itemsToGet('name');
    /** @var SpecGatherer $gatherer */
    $gatherer = \Civi::container()->get('spec_gatherer');
    // Any fields name with a dot in it is custom
    if ($fields) {
      $this->includeCustom = strpos(implode('', $fields), '.') !== FALSE;
    }
    $spec = $gatherer->getSpec($this->getEntityName(), $this->action, $this->includeCustom);
    return SpecFormatter::specToArray($spec->getFields($fields), (array) $this->select, $this->loadOptions);
  }

  /**
   * @return string
   */
  public function getAction() {
    return $this->action;
  }

}
