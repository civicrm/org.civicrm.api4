<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Query\QueryCopier;
use Civi\Test\Api4\UnitTestCase;
use \CRM_Utils_SQL_Select as SqlSelect;

/**
 * @group headless
 */
class QueryCopierTest extends UnitTestCase {
  public function testExactCopy() {
    $original = SqlSelect::from('civicrm_contact');
    $original->where('id = 1');
    $new = QueryCopier::copy($original);

    $this->assertEquals($original->toSQL(), $new->toSQL());
  }

  public function testSingleReplacement() {
    $original = SqlSelect::from('civicrm_activity a');
    $original->where('id = 1');
    $original->select('a.id as id');

    $replacements = array('selects' => array('a.subject'));
    $new = QueryCopier::copy($original, $replacements);

    $expected = 'SELECT a.subject FROM civicrm_activity a WHERE (id = 1)';
    $this->assertEquals($expected, $this->getCleanedSql($new->toSQL()));
  }

  public function testComplexReplacement() {
    $original = SqlSelect::from('civicrm_contact a');
    $original->where(array('a.id = 1', 'a.first_name IS NOT NULL'));
    $original->select('a.id as id, phones.phone');
    $original->join('phones', 'LEFT JOIN civicrm_phones phones ON phones.contact_id = a.id');
    $original->orderBy('a.display_name');
    $original->limit(25, 0);

    $replacements = array(
      'selects' => array('a.id', 'emails.email'),
      'joins' => array('emails' => 'LEFT JOIN civicrm_email emails ON emails.contact_id = a.id'),
      'orderBys' => array('a.id'),
      'limit' => array(10, 20)
    );
    $new = QueryCopier::copy($original, $replacements);

    $expected = 'SELECT a.id, emails.email ' .
      'FROM civicrm_contact a ' .
      'LEFT JOIN civicrm_email emails ON emails.contact_id = a.id ' .
      'WHERE (a.id = 1) AND (a.first_name IS NOT NULL) ' .
      'ORDER BY a.id ' .
      'LIMIT 10 ' .
      'OFFSET 20';

    $this->assertEquals($expected, $this->getCleanedSql($new->toSQL()));
  }

  /**
   * @param $sql
   *
   * @return string
   */
  private function getCleanedSql($sql) {
    return trim(str_replace("\n", ' ', $sql));
  }
}

