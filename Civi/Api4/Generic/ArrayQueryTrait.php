<?php

namespace Civi\Api4\Generic;
use Civi\API\Exception\NotImplementedException;

/**
 * Helper functions for performing api queries on arrays of data.
 *
 * @package Civi\Api4\Generic
 */
trait ArrayQueryTrait {

  /**
   * @param array $values
   *   List of all rows
   * @return array
   *   Filtered list of rows
   */
  protected function queryArray($values) {
    $values = $this->filterArray($values);
    $values = $this->sortArray($values);
    $values = $this->selectArray($values);
    $values = $this->limitArray($values);
    return $values;
  }

  /**
   * @param array $values
   * @return array
   */
  protected function filterArray($values) {
    if ($this->where) {
      $values = array_filter($values, [$this, 'evaluateFilters']);
    }
    return array_values($values);
  }

  /**
   * @param array $row
   * @return bool
   */
  private function evaluateFilters($row) {
    $allConditions = in_array($this->where[0], ['AND', 'OR', 'NOT']) ? $this->where : ['AND', $this->where];
    return $this->walkFilters($row, $allConditions);
  }

  /**
   * @param array $row
   * @param array $filters
   * @return bool
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function walkFilters($row, $filters) {
    switch ($filters[0]) {
      case 'AND':
      case 'NOT':
        $result = TRUE;
        foreach ($filters[1] as $filter) {
          if (!$this->walkFilters($row, $filter)) {
            $result = FALSE;
            break;
          }
        }
        return $result == ($filters[0] == 'AND');

      case 'OR':
        $result = !count($filters[1]);
        foreach ($filters[1] as $filter) {
          if ($this->walkFilters($row, $filter)) {
            return TRUE;
          }
        }
        return $result;

      default:
        return $this->filterCompare($row, $filters);
    }
  }

  /**
   * @param array $row
   * @param array $condition
   * @return bool
   * @throws \Civi\API\Exception\NotImplementedException
   */
  private function filterCompare($row, $condition) {
    if (!is_array($condition)) {
      throw new NotImplementedException('Unexpected where syntax; expecting array.');
    }
    $value = isset($row[$condition[0]]) ? $row[$condition[0]] : NULL;
    $operator = $condition[1];
    $expected = isset($condition[2]) ? $condition[2] : NULL;
    switch ($operator) {
      case '=':
      case '!=':
      case '<>':
        $equal = $value == $expected;
        // PHP is too imprecise about comparing the number 0
        if ($expected === 0 || $expected === '0') {
          $equal = ($value === 0 || $value === '0');
        }
        // PHP is too imprecise about comparing empty strings
        if ($expected === '') {
          $equal = ($value === '');
        }
        return $equal == ($operator == '=');

      case 'IS NULL':
      case 'IS NOT NULL':
        return is_null($value) == ($operator == 'IS NULL');

      case '>':
        return $value > $expected;

      case '>=':
        return $value >= $expected;

      case '<':
        return $value < $expected;

      case '<=':
        return $value <= $expected;

      case 'BETWEEN':
      case 'NOT BETWEEN':
        $between = ($value >= $expected[0] && $value <= $expected[1]);
        return $between == ($operator == 'BETWEEN');

      case 'LIKE':
      case 'NOT LIKE':
        $pattern = '/^' . str_replace('%', '.*', preg_quote($expected, '/')) . '$/i';
        return !preg_match($pattern, $value) == ($operator != 'LIKE');

      default:
        throw new NotImplementedException("Unsupported operator: '$operator' cannot be used with array data");
    }
  }

  /**
   * @param $values
   * @return array
   */
  protected function sortArray($values) {
    if ($this->orderBy) {
      usort($values, [$this, 'sortCompare']);
    }
    return $values;
  }

  private function sortCompare($a, $b) {
    foreach ($this->orderBy as $field => $dir) {
      $modifier = $dir == 'ASC' ? 1 : -1;
      if (isset($a[$field]) && isset($b[$field])) {
        if ($a[$field] == $b[$field]) {
          continue;
        }
        return (strnatcasecmp($a[$field], $b[$field]) * $modifier);
      }
      elseif (isset($a[$field]) || isset($b[$field])) {
        return ((isset($a[$field]) ? 1 : -1) * $modifier);
      }
    }
    return 0;
  }

  /**
   * @param $values
   * @return array
   */
  protected function selectArray($values) {
    if ($this->select) {
      foreach ($values as &$value) {
        $value = array_intersect_key($value, array_flip($this->select));
      }
    }
    return $values;
  }

  /**
   * @param $values
   * @return array
   */
  protected function limitArray($values) {
    if ($this->offset || $this->limit) {
      $values = array_slice($values, $this->offset ?: 0, $this->limit ?: NULL);
    }
    return $values;
  }

}
