<?php

namespace Civi\Api4\Query;

use \CRM_Utils_SQL_Select as SqlSelect;

class QueryCopier {
  /**
   * @var array
   */
  public static $replaceableParts = array(
    'selects',
    'joins',
    'wheres',
    'groupBys',
    'orderBys',
    'limit',
  );

  /**
   * Copy a query, but allow replacing some parts of it. Used when you want to
   * completely replace parts of a query, and not just merge them.
   *
   * @param SqlSelect $original
   *   The query to be cloned
   * @param array $replacements
   *   An array of parts to use in the new query. Will overwrite those in
   *   original. Each part should use a key the replaceableParts array
   * @see QueryCopier::$replaceableParts
   *
   * @return SqlSelect
   */
  public static function copy(SqlSelect $original, $replacements = array()) {
    $copy = SqlSelect::fragment();
    $replacedParts = array();
    $replaceableParts = self::$replaceableParts;

    foreach ($replaceableParts as $part) {
      if (!isset($replacements[$part])) {
        continue;
      }

      $replacement = $replacements[$part];
      static::setValue($copy,$part, $replacement);
      $replacedParts[] =  $part;
    }

    $originalPartsToUse = array_diff($replaceableParts, $replacedParts);
    $originalPartsToUse[] = 'from'; // always use original from
    $copy->merge($original, $originalPartsToUse);

    return $copy;
  }

  /**
   * @param SqlSelect $query
   * @param $part
   * @param $value
   *
   * @return SqlSelect
   */
  private static function setValue(SqlSelect $query, $part, $value) {
    switch ($part) {
      case 'selects':
        return $query->select($value);
      case 'wheres':
        return $query->where($value);
      case 'groupBys':
        return $query->groupBy($value);
      case 'orderBys':
        return $query->orderBy($value);
      case 'limit':
        $limit = $value;
        $offset = 0;
        if (is_array($value)) {
          list($limit, $offset) = $value;
        }
        return $query->limit($limit, $offset);
      case 'joins':
          return $query->join(NULL, $value);
      default:
        return $query;
    }
  }
}
