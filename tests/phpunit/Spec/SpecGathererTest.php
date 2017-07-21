<?php

namespace Civi\Test\Api4\Spec;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\Provider\SpecProviderInterface;
use Civi\Api4\Service\Spec\RequestSpec;
use Civi\Api4\Service\Spec\SpecGatherer;
use Civi\Test\Api4\UnitTestCase;
use Civi\Api4\Api\CustomFieldApi;
use Civi\Api4\Api\CustomGroupApi;
use Civi\Test\Api4\Traits\TableDropperTrait;
use Prophecy\Argument;

/**
 * @group headless
 */
class SpecGathererTest extends UnitTestCase {

  use TableDropperTrait;

  public function setUpHeadless() {
    $this->dropByPrefix('civicrm_value_favorite');
    $this->cleanup(
      array(
        'tablesToTruncate' => array(
          'civicrm_custom_group',
          'civicrm_custom_field'
        )
      )
    );
    return parent::setUpHeadless();
  }

  public function testBasicFieldsGathering() {
    $gatherer = new SpecGatherer();
    $specs = $gatherer->getSpec('Contact', 'create');
    $contactDAO = _civicrm_api3_get_DAO('Contact');
    $contactFields = $contactDAO::fields();
    $specFieldNames = $specs->getFieldNames();
    $contactFieldNames = array_column($contactFields, 'name');

    $this->assertEmpty(array_diff_key($contactFieldNames, $specFieldNames));
  }

  public function testWithSpecProvider() {
    $gather = new SpecGatherer();

    $provider = $this->prophesize(SpecProviderInterface::class);
    $provider->applies('Contact', 'create')->willReturn(TRUE);
    $provider->modifySpec(Argument::any())->will(function ($args) {
      /** @var RequestSpec $spec */
      $spec = $args[0];
      $spec->addFieldSpec(new FieldSpec('foo'));
    });
    $gather->addSpecProvider($provider->reveal());

    $spec = $gather->getSpec('Contact', 'create');
    $fieldNames = $spec->getFieldNames();

    $this->assertContains('foo', $fieldNames);
  }

  public function testPseudoConstantOptionsWillBeAdded() {
    $customGroupId = CustomGroupApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FavoriteThings')
      ->setValue('extends', 'Contact')
      ->execute()['id'];

    $options = array('Red', 'Green', 'Pink');

    CustomFieldApi::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('options', $options)
      ->setValue('html_type', 'Select')
      ->setValue('data_type', 'String')
      ->execute();

    $gatherer = new SpecGatherer();
    $spec = $gatherer->getSpec('Contact', 'get');

    $regularField = $spec->getFieldByName('contact_type');

    $this->assertNotEmpty($regularField->getOptions());
    $this->assertContains('Individual', $regularField->getOptions());
  }
}
