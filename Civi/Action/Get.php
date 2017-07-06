<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2015                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

namespace Civi\API\V4\Action;

use Civi\API\V4\Query\Api4SelectQuery;
use Civi\API\V4\Result;

/**
 *
 * Base class for all get actions.
 *
 * @method $this addSelect(string $select)
 * @method $this setSelect(array $selects)
 * @method $this setWhere(array $wheres)
 * @method $this setOrderBy(array $order)
 * @method $this setLimit(int $limit)
 * @method $this setOffset(int $offset)
 */
class Get extends AbstractAction {
  /**
   * Fields to return. Defaults to all non-custom fields.
   *
   * @var array
   */
  protected $select = array();
  /**
   * Array of conditions keyed by field.
   *
   * $example->addWhere('contact_type', 'IN', array('Individual', 'Household'))
   *
   * @var array
   */
  protected $where = array();
  /**
   * Array of field(s) to use in ordering the results
   *
   * Defaults to id ASC
   * $example->addOrderBy('sort_name', 'ASC')
   *
   * @var array
   */
  protected $orderBy = array();
  /**
   * Maximum number of results to return.
   *
   * Defaults to unlimited.
   *
   * @var int
   */
  protected $limit = 0;
  protected $offset = 0;

  /**
   * @param string $field
   * @param string $op
   * @param mixed $value
   * @return $this
   * @throws \API_Exception
   */
  public function addWhere($field, $op, $value) {
    if (!in_array($op, \CRM_Core_DAO::acceptedSQLOperators())) {
      throw new \API_Exception('Unsupported operator');
    }
    $this->where[] = array($field, $op, $value);
    return $this;
  }

  /**
   * @param array $clause
   * @return $this
   * @throws \API_Exception
   */
  public function addClause($clause) {
    $this->where[] = $clause;
    return $this;
  }

  /**
   * @param string $field
   * @param string $direction
   * @return $this
   */
  public function addOrderBy($field, $direction = 'ASC') {
    $this->orderBy[$field] = $direction;
    return $this;
  }

  public function _run(Result $result) {
    $query = new Api4SelectQuery($this->getEntity(), $this->checkPermissions);
    $query->select = $this->select;
    $query->where = $this->where;
    $query->orderBy = $this->orderBy;
    $query->limit = $this->limit;
    $query->offset = $this->offset;
    $result->exchangeArray($query->run());
  }

}
