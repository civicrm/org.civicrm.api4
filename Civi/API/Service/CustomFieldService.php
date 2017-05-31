<?php

namespace Civi\API\Service;

use Civi\Api4\CustomField;

class CustomFieldService {
  /**
   * @var array
   */
  protected $cache = array();

  /**
   * @param $whereParts
   *
   * @return array|object
   */
  public function findBy($whereParts) {
    sort($whereParts);
    $key = json_encode($whereParts);

    if (!isset($this->cache[$key])) {
      $request = CustomField::get()->setCheckPermissions(FALSE);
      foreach ($whereParts as $where) {
        $request->addWhere($where[0], $where[1], $where[2]);
      }
      $this->cache[$key] = $request->execute()->getArrayCopy();
    }

    return $this->cache[$key];
  }

  /**
   * Resets the cache, for example if a custom group was changed
   */
  public function clearCache() {
    $this->cache =  array();
  }

}
