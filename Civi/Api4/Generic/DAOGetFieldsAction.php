<?php

namespace Civi\Api4\Generic;

use Civi\Api4\Service\Spec\SpecGatherer;
use Civi\Api4\Service\Spec\SpecFormatter;
use Civi\Api4\Generic\Result;

/**
 * Get fields for an entity.
 *
 * @method $this setIncludeCustom(bool $value)
 * @method bool getIncludeCustom()
 * @method $this setOptions(bool $value)
 * @method bool getOptions()
 * @method $this setAction(string $value)
 * @method $this setSelect(array $value)
 * @method $this addSelect(string $value)
 * @method array getSelect()
 * @method $this setFields(array $value)
 * @method $this addField(string $value)
 * @method array getFields()
 */
class DAOGetFieldsAction extends AbstractAction {

  /**
   * Override default to allow open access
   * @inheritDoc
   */
  protected $checkPermissions = FALSE;

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
  protected $getOptions = FALSE;

  /**
   * Which fields should be returned?
   *
   * Ex: ['contact_type', 'contact_sub_type']
   *
   * @var array
   */
  protected $fields = [];

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

  public function _run(Result $result) {
    /** @var SpecGatherer $gatherer */
    $gatherer = \Civi::container()->get('spec_gatherer');
    // Any fields name with a dot in it is custom
    if ($this->fields) {
      $this->includeCustom = strpos(implode('', $this->fields), '.') !== FALSE;
    }
    $spec = $gatherer->getSpec($this->getEntityName(), $this->action, $this->includeCustom);
    $fields = SpecFormatter::specToArray($spec->getFields($this->fields), (array) $this->select, $this->getOptions);
    $result->exchangeArray(array_values($fields));
  }

  /**
   * @return string
   */
  public function getAction() {
    return $this->action;
  }

}
