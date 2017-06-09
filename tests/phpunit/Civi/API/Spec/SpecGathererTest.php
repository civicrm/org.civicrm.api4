<?php

namespace phpunit\Civi\API\Spec;

use Civi\API\Spec\FieldSpec;
use Civi\API\Spec\Provider\SpecProviderInterface;
use Civi\API\Spec\RequestSpec;
use Civi\API\Spec\SpecGatherer;
use Civi\API\V4\UnitTestCase;
use Prophecy\Argument;

/**
 * @group headless
 */
class SpecGathererTest extends UnitTestCase {

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
}
