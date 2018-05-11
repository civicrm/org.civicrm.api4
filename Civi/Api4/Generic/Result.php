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

namespace Civi\Api4\Generic;

/**
 * Container for api results.
 */
class Result extends \ArrayObject {

  /**
   * @var string
   */
  public $entity;

  /**
   * @var string
   */
  public $action;

  /**
   * Api version.
   *
   * @var int
   */
  public $version = 4;

  /**
   * Return first result.
   *
   * How to use:
   *
   * <code>
   * <?php
   * use Civi\Api4\Contact;
   * try {
   *   $contacts = Contact::get()
   *   ->addWhere('contact_type','=','Organization')
   *   ->setLimit(5)
   *   ->execute();
   * } catch (\Exception $e) {
   *   var_dump($e->getMessage());
   * }
   *
   * var_dump($contacts);
   *
   * // take first contact without condition
   * $first_contact = $contacts->first();
   *
   * $condition = $contacts->first(function ($key, $value){
   *  return $value['display_name'] === 'Some Name';
   * });
   *
   * ?>
   * </code>
   *
   * Thus in the variable $contacts always kept the original data, to which we
   * can apply later
   *
   * @param callable|null $callback
   * @param null $default
   *
   * @return null|\Civi\Api4\Generic\Result
   */
  public function first(callable $callback = NULL, $default = NULL) {
    if (NULL === $callback) {
      if ($this->isEmpty()) {
        return $default;
      }
      return new self(\reset($this));
    }
    foreach ($this as $key => $value) {
      if (\call_user_func($callback, $key, $value)) {
        if (\is_array($value) && !empty($value)) {
          return new self(\reset($this));
        }
        return $value;
      }
    }
    return $default;
  }

  /**
   * How to use:
   *
   * <code>
   * <?php
   * use Civi\Api4\Contact;
   * try {
   *   $contacts = Contact::get()
   *   ->addWhere('contact_type','=','Organization')
   *   ->setLimit(5)
   *   ->execute();
   * } catch (\Exception $e) {
   *   var_dump($e->getMessage());
   * }
   *
   * var_dump($contacts->first()->get('display_name'));
   *
   * ?>
   * </code>
   *
   * @param mixed $key
   * @param null  $default
   *
   * @return \Civi\Api4\Generic\Result|mixed|null
   */
  public function get($key, $default = NULL) {
    if (!$this->isEmpty() && $this->offsetExists($key)) {
      $offset = $this->offsetGet($key);
      if (\is_array($offset) && !empty($offset)) {
        return new self($offset);
      }
      return $offset;
    }
    return $default;
  }

  /**
   * @return bool
   */
  public function isEmpty() {
    return FALSE === (bool) $this->count();
  }

  /**
   * Re-index the results array (which by default is non-associative).
   *
   * Drops any item from the results that does not contain the specified key.
   *
   * @param string $key
   *
   * @throws \API_Exception
   *
   * @return $this
   */
  public function indexBy($key) {
    if (\count($this)) {
      $newResults = [];
      foreach ($this as $values) {
        if (isset($values[$key])) {
          $newResults[$values[$key]] = $values;
        }
      }
      if (!$newResults) {
        throw new \API_Exception("Key ${key} not found in api results");
      }
      $this->exchangeArray($newResults);
    }
    return $this;
  }
}
