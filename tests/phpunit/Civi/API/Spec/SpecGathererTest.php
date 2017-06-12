<?php

namespace phpunit\Civi\API\Spec;

use Civi\API\Spec\FieldSpec;
use Civi\API\Spec\Provider\CustomFieldSpecProvider;
use Civi\API\Spec\Provider\SpecProviderInterface;
use Civi\API\Spec\RequestSpec;
use Civi\API\Spec\SpecGatherer;
use Civi\API\V4\UnitTestCase;
use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\TableDropperTrait;
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

  public function testWithCustomFields() {
    $customGroupId = CustomGroup::create()
      ->setCheckPermissions(FALSE)
      ->setValue('name', 'FavoriteThings')
      ->setValue('extends', 'Contact')
      ->execute()['id'];

    CustomField::create()
      ->setCheckPermissions(FALSE)
      ->setValue('label', 'FavColor')
      ->setValue('custom_group_id', $customGroupId)
      ->setValue('html_type', 'Text')
      ->setValue('data_type', 'String')
      ->execute();

    $gather = new SpecGatherer();
    $gather->addSpecProvider(new CustomFieldSpecProvider());
    $spec = $gather->getSpec('Contact', 'get');
    $fieldNames = $spec->getFieldNames();

    $this->assertContains('FavoriteThings.FavColor', $fieldNames);
  }
}
