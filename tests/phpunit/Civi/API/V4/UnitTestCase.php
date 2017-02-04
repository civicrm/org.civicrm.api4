<?php
namespace Civi\API\V4;
use Civi\Api4\Participant;
use Civi\Test\HeadlessInterface;
use Civi\Test\TransactionalInterface;

/**
 * @group headless
 */
//class ParticipantTest extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {
class UnitTestCase extends \PHPUnit_Framework_TestCase implements HeadlessInterface, TransactionalInterface {

  /**
   * Constructor.
   *
   * @see also tests/phpunit/CiviTest/CiviUnitTestCase.php
   *
   * @param string $name
   * @param array $data
   * @param string $dataName
   */
  public function __construct($name = NULL, array$data = array(), $dataName = '') {
    parent::__construct($name, $data, $dataName);
    error_reporting(E_ALL & ~E_NOTICE);
  }

  public function setUpHeadless() {
    return \Civi\Test::headless()->installMe(__DIR__)->apply();
  }

 /**
   * Tears down the fixture, for example, closes a network connection.
   *
   * This method is called after a test is executed.
   */
  public function tearDown() {
    parent::tearDown();
  }

  /**
   * Quick clean by emptying tables created for the test.
   *
   * (Lifted from tests/phpunit/CiviTest/CiviUnitTestCase.php)
   * @param array
   * - $tablesToTruncate
   */
  public function cleanup($params) {
    $params += array(
        'tablesToTruncate' => array(),
    );
    \CRM_Core_DAO::executeQuery("SET FOREIGN_KEY_CHECKS = 0;");
    foreach ($params['tablesToTruncate'] as $table) {
      \Civi::log()->info('truncating: ' . $table);
      $sql = "TRUNCATE TABLE $table";
      \CRM_Core_DAO::executeQuery($sql);
    }
    \CRM_Core_DAO::executeQuery("SET FOREIGN_KEY_CHECKS = 1;");
  }

}
