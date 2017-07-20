<?php

namespace Civi\Test\Api4\Entity;

use Civi\Api4\ApiInterface;
use Civi\Api4\GetParameterBag;
use Civi\Test\Api4\Service\TestCreationParameterProvider;
use Civi\Test\Api4\Traits\TableDropperTrait;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 */
class ConformanceTest extends UnitTestCase {

  use TableDropperTrait;

  /**
   * @var TestCreationParameterProvider
   */
  protected $creationParamProvider;

  /**
   * Set up baseline for testing
   */
  public function setUp() {
    $tablesToTruncate = array(
      'civicrm_custom_group',
      'civicrm_custom_field',
      'civicrm_option_group',
    );
    $this->dropByPrefix('civicrm_value_myfavorite');
    $this->cleanup(array('tablesToTruncate' => $tablesToTruncate));
    $this->loadDataSet('ConformanceTest');
    $this->creationParamProvider = \Civi::container()->get('test.param_provider');
    parent::setUp();
  }

  public function testConformance() {

    $container = \Civi::container();
    $entityApi = $container->get('entity.api');

    $entities = $entityApi->request('get', NULL, FALSE)->getArrayCopy();
    $this->assertNotEmpty($entities);

    foreach ($entities as $entityName) {
      /** @var ApiInterface $api */
      $api = $container->get(sprintf('%s.api', strtolower($entityName)));

      if ($entityName === 'Entity') {
        continue;
      }

      $this->checkActions($api);
      $this->checkFields($api);
      $id = $this->checkCreation($api);
      $this->checkGet($api, $id);
      $this->checkDeletion($api, $id);
      $this->checkPostDelete($api, $id);
    }
  }

  /**
   * @param ApiInterface $api
   */
  protected function checkFields(ApiInterface $api) {
    $fields = $api->request('getFields', NULL, FALSE)->indexBy('name');

    $errMsg = sprintf('%s is missing required ID field', $api->getEntity());
    $subset = array('data_type' => 'Integer');

    $this->assertArraySubset($subset, $fields['id'], $errMsg);
  }

  /**
   * @param ApiInterface $api
   */
  protected function checkActions($api) {
    $actions = $api->request('getActions', NULL, FALSE)->getArrayCopy();

    $basicActions = array('getActions', 'create', 'get', 'delete', 'getFields');
    $missing = array_diff($basicActions, $actions);

    $this->assertEmpty($missing);
  }

  /**
   * @param ApiInterface $api
   *
   * @return mixed
   */
  protected function checkCreation($api) {
    $entity = $api->getEntity();
    $requiredParams = $this->creationParamProvider->getRequired($entity);

    $createResult = $api->request('create', $requiredParams, FALSE);
    $id = $createResult['id'];

    $this->assertArrayHasKey('id', $createResult, "create missing ID");
    $this->assertGreaterThanOrEqual(1, $id, "$entity ID not positive");

    return $id;
  }

  /**
   * @param ApiInterface $api
   * @param $id
   */
  protected function checkGet(ApiInterface $api, $id) {
    $params = new GetParameterBag();
    $params->addWhere('id', '=', $id);
    $getResult = $api->request('get', $params, FALSE);

    $errMsg = sprintf('Failed to fetch a %s after creation', $api->getEntity());
    $this->assertEquals(1, count($getResult), $errMsg);
  }

  /**
   * @param ApiInterface $api
   * @param $id
   */
  protected function checkDeletion(ApiInterface $api, $id) {
    $params = new GetParameterBag();
    $params->addWhere('id', '=', $id);
    $deleteResult = $api->request('delete', $params, FALSE);

    // should get back an array of deleted id
    $this->assertEquals(array($id), (array)$deleteResult);
  }

  /**
   * @param ApiInterface $api
   * @param int $id
   */
  protected function checkPostDelete(ApiInterface $api, $id) {
    $params = new GetParameterBag();
    $params->addWhere('id', '=', $id);
    $getDeletedResult = $api->request('get', $params, FALSE);

    $errMsg = sprintf('Entity "%s" was not deleted', $api->getEntity());
    $this->assertEquals(0, count($getDeletedResult), $errMsg);
  }

}
