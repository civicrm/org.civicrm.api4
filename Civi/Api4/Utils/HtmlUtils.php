<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
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
namespace Civi\Api4\Utils;

/**
 * This is a backport of CRM_Utils_API_HTMLInputCoder (circa v5.19.2)
 * for addressing CIVI-SA-2019-23 in the "org.civicrm.api4" extension. It
 * includes helpers that aren't available in previous versions of civicrm-core.
 *
 * Ordinarily, this kind of duplication would be problematic for future
 * maintenance; however, this whole repo is already deprecated (since api4
 * moved in core@5.19+) and security support for this repo will be ending soon.
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2019
 */
class HtmlUtils {
  private $skipFields = NULL;

  /**
   * @var static
   */
  private static $_singleton = NULL;

  /**
   * @return static
   */
  public static function singleton() {
    if (self::$_singleton === NULL) {
      self::$_singleton = new static();
    }
    return self::$_singleton;
  }

  /**
   * Get skipped fields.
   *
   * @return array<string>
   *   list of field names
   */
  public function getSkipFields() {
    if ($this->skipFields === NULL) {
      $this->skipFields = \CRM_Utils_API_HTMLInputCoder::singleton()->getSkipFields();
    }
    return $this->skipFields;
  }

  /**
   * Is field skipped.
   *
   * @param string $fldName
   *
   * @return bool
   *   TRUE if encoding should be skipped for this field
   */
  public function isSkippedField($fldName) {
    $skipFields = $this->getSkipFields();
    if ($skipFields === NULL) {
      return FALSE;
    }
    // Strip extra numbers from custom fields e.g. custom_32_1 should be custom_32
    if (strpos($fldName, 'custom_') === 0) {
      list($fldName, $customId) = explode('_', $fldName);
      $fldName .= '_' . $customId;
    }

    // Field should be skipped
    if (in_array($fldName, $skipFields)) {
      return TRUE;
    }
    // Field is multilingual and after cutting off _xx_YY should be skipped (CRM-7230)…
    if ((preg_match('/_[a-z][a-z]_[A-Z][A-Z]$/', $fldName) && in_array(substr($fldName, 0, -6), $skipFields))) {
      return TRUE;
    }
    // Field can take multiple entries, eg. fieldName[1], fieldName[2], etc.
    // We remove the index and check again if the fieldName in the list of skipped fields.
    $matches = [];
    if (preg_match('/^(.*)\[\d+\]/', $fldName, $matches) && in_array($matches[1], $skipFields)) {
      return TRUE;
    }

    return FALSE;
  }

  public function encodeValue($value) {
    return str_replace(['<', '>'], ['&lt;', '&gt;'], $value);
  }

  /**
   * Perform in-place decode on strings (in a list of records).
   *
   * @param array $rows
   *   Ex in: $rows[0] = ['first_name' => 'A&W'].
   *   Ex out: $rows[0] = ['first_name' => 'A&amp;W'].
   */
  public function encodeRows(&$rows) {
    foreach ($rows as $rid => $row) {
      $this->encodeRow($rows[$rid]);
    }
  }

  /**
   * Perform in-place encode on strings (in a single record).
   *
   * @param array $row
   *   Ex in: ['first_name' => 'A&W'].
   *   Ex out: ['first_name' => 'A&amp;W'].
   */
  public function encodeRow(&$row) {
    foreach ($row as $k => $v) {
      if (is_string($v) && !$this->isSkippedField($k)) {
        $row[$k] = $this->encodeValue($v);
      }
    }
  }

  public function decodeValue($value) {
    return str_replace(['&lt;', '&gt;'], ['<', '>'], $value);
  }

  /**
   * Perform in-place decode on strings (in a list of records).
   *
   * @param array $rows
   *   Ex in: $rows[0] = ['first_name' => 'A&amp;W'].
   *   Ex out: $rows[0] = ['first_name' => 'A&W'].
   */
  public function decodeRows(&$rows) {
    foreach ($rows as $rid => $row) {
      $this->decodeRow($rows[$rid]);
    }
  }

  /**
   * Perform in-place decode on strings (in a single record).
   *
   * @param array $row
   *   Ex in: ['first_name' => 'A&amp;W'].
   *   Ex out: ['first_name' => 'A&W'].
   */
  public function decodeRow(&$row) {
    foreach ($row as $k => $v) {
      if (is_string($v) && !$this->isSkippedField($k)) {
        $row[$k] = $this->decodeValue($v);
      }
      // NOTE: The notation for joined data changed after 4.4.
      // In later versions, joined data goes the same $row as main data (but with a dotted key).
      // In v4.4, joined data goes into a subarray, so we have to recurse.
      elseif (is_array($v)) {
        $this->decodeRow($row[$k]);
      }
    }
  }

}
