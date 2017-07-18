<?php

namespace Civi\Api4;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class GetParameterBag extends ParameterBag {

  /**
   * @param array $parameters
   */
  public function __construct(array $parameters = array()) {
    parent::__construct($parameters);
    $this->set('where', array());
    $this->set('orderBy', array());
    $this->set('select', array());
  }

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
    $wheres = $this->get('where');
    $wheres[] = array($field, $op, $value);
    $this->set('where', $wheres);

    return $this;
  }

  /**
   * @param $field
   * @param $order
   *
   * @return $this
   */
  public function addOrderBy($field, $order) {
    $orderBy = $this->get('orderBy');
    $orderBy[] = array($field, $order);
    $this->set('orderBy', $orderBy);

    return $this;
  }

  /**
   * @param $select
   *
   * @return $this
   */
  public function addSelect($select) {
    $selects = $this->get('select');
    $selects[] = $select;
    $this->set('select', $selects);

    return $this;
  }

  /**
   * @param array $clause
   * @return $this
   * @throws \API_Exception
   */
  public function addClause($clause) {
    $where = $this->get('where');
    $where[] = $clause;
    $this->set('where', $where);

    return $this;
  }
}
