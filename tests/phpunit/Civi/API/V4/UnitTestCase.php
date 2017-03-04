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

  /**
   * Quick record counter
   *
   * @param string $table_name
   * @returns int record count
   */
  public function countTable($table_name) {
    $sql = "SELECT count(*) FROM $table_name";
    return (int)\CRM_Core_DAO::singleValueQuery($sql);
  }

  /**
   * Create sample entities (using V3 for now).
   *
   * @param array (type, seq, overrides, count)
   * @return array (either single, or array of array if count >1)
   */
  public static function createEntity($params) {
    $params += array(
      'count' => 1,
      'seq' => 0,
    );
    $entities = array();
    for ($i = 0; $i < $params['count']; $i++) {
      $params['seq']++;
      $data = self::sample($params);
      $api_params = array('sequential' => 1) + $data['sample_params'];
      $result = civicrm_api3($data['entity'], 'create', $api_params);
      if ($result['is_error']) {
        throw new Exception("creating $data[entity] failed");
      }
      $entity = $result['values'][0];
      if (!($entity['id'] > 0)) {
        throw new Exception("created entity is malformed");
      }
      $entities[] = $entity;
    }
    return $params['count'] == 1 ? $entity : $entities;
  }

  /**
   * Helper function for creating sample entities.
   *
   * Depending on the supplied sequence integer, plucks values from the dummy data.
   * Constructs a foreign entity when an ID is required but isn't supplied in the overrides.
   *
   * Inspired by CiviUnitTestCase::
   * @todo - extract this function to own class and share with CiviUnitTestCase?
   * @param array
   * - type: string roughly matching entity type
   * - seq: (optional) int sequence number for the values of this type
   * - overrides: (optional) array of fill in parameters
   *
   * @return array
   * - entity: string API entity type (usually the type supplied except for contact subtypes)
   * - sample_params: array API sample_params properties of sample entity
   */
  public static function sample($params) {
    $params += array(
        'seq' => 0,
        'overrides' => array(),
    );
    $type = $params['type'];
    // sample data - if field is array then chosed based on `seq`
    $sample_params = array();
    if (in_array($type, array('Individual', 'Organization', 'Household'))) {
      $sample_params['contact_type'] = $type;
      $entity = 'Contact';
    }
    else {
      $entity = $type;
    }
    // use the seq to pluck a set of params out
    foreach (self::sampleData($type) as $key => $value) {
      if (is_array($value)) {
        $sample_params[$key] = $value[$params['seq'] % count($value)];
      }
      else {
        $sample_params[$key] = $value;
      }
    }
    if ($type == 'Individual') {
      $sample_params['email'] = strtolower(
        $sample_params['first_name'] . '_' . $sample_params['last_name'] . '@civicrm.org'
      );
      $sample_params['prefix_id'] = 3;
      $sample_params['suffix_id'] = 3;
    }
    if (!count($sample_params)) {
      throw new Exception("unknown sample type: $type");
    }
    $sample_params = $params['overrides'] + $sample_params;
    // make foreign enitiies if they haven't been supplied
    foreach ($sample_params as $key => $value) {
      if (substr($value, 0, 6) === 'dummy.') {
        $foreign_entity = self::createEntity(array(
          'type' => substr($value, 6),
          'seq' => $params['seq']));
        $sample_params[$key] = $foreign_entity['id'];
      }
    }
    return compact("entity", "sample_params");
  }

  /**
   * Provider of sample data.
   *
   * @return array
   *   Array values represent a set of allowable items.
   *   Strings in the form "dummy.Entity" require creating a foreign entity first.
   */
  public static function sampleData($type) {
    $data = array(
      'Individual' => array(
        // The number of values in each list need to be coprime numbers to not have duplicates
        'first_name' => array('Anthony', 'Joe', 'Terrence', 'Lucie', 'Albert', 'Bill', 'Kim'),
        'middle_name' => array('J.', 'M.', 'P', 'L.', 'K.', 'A.', 'B.', 'C.', 'D', 'E.', 'Z.'),
        'last_name' => array('Anderson', 'Miller', 'Smith', 'Collins', 'Peterson'),
        'contact_type' => 'Individual'
      ),
      'Organization' => array(
        'organization_name' => array(
          'Unit Test Organization',
          'Acme',
          'Roberts and Sons',
          'Cryo Space Labs',
          'Sharper Pens',
        ),
      ),
      'Household' => array(
        'household_name' => array('Unit Test household'),
      ),
      'Event' => array(
        'title' => 'Annual CiviCRM meet',
        'summary' => 'If you have any CiviCRM related issues or want to track where CiviCRM is heading, Sign up now',
        'description' => 'This event is intended to give brief idea about progess of CiviCRM and giving solutions to common user issues',
        'event_type_id' => 1,
        'is_public' => 1,
        'start_date' => 20081021,
        'end_date' => 20081023,
        'is_online_registration' => 1,
        'registration_start_date' => 20080601,
        'registration_end_date' => 20081015,
        'max_participants' => 100,
        'event_full_text' => 'Sorry! We are already full',
        'is_monetary' => 0,
        'is_active' => 1,
        'is_show_location' => 0,
      ),
      'Participant' => array(
        'event_id' => 'dummy.Event',
        'contact_id' => 'dummy.Individual',
        'status_id' => 2,
        'role_id' => 1,
        'register_date' => 20070219,
        'source' => 'Wimbeldon',
        'event_level' => 'Payment',
      ),
      'Contribution' => array(
        'contact_id' => 'dummy.Individual',
        'financial_type_id' => 1, // donation, 2 = member, 3 = campaign contribution, 4=event
        'total_amount' => 7.3,
      ),
      'Activity' => array(
        //'activity_type_id' => 1,
        'subject' => 'unit testing',
        'source_contact_id' => 'dummy.Individual',
      ),
    );
    if ($type == 'Contact') {
      $type = 'Individual';
    }
    return $data[$type];
  }

}
