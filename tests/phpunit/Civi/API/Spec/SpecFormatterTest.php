<?php

namespace phpunit\Civi\API\Spec;

use Civi\API\Spec\FieldSpec;
use Civi\API\Spec\RequestSpec;
use Civi\API\Spec\SpecFormatter;
use Civi\API\V4\UnitTestCase;

class SpecFormatterTest extends UnitTestCase {

  public function testSpecToArray() {
    $spec = new RequestSpec('Contact', 'get');
    $fieldName = 'lastname';
    $field = new FieldSpec($fieldName);
    $spec->addFieldSpec($field);
    $arraySpec = SpecFormatter::specToArray($spec);


    $this->assertArrayHasKey($fieldName, $arraySpec);
    $this->assertEquals('String', $arraySpec[$fieldName]['data_type']);
  }

  public function testArrayToField() {
    $fieldData = array(
      'name' => 'Foo',
      'title' => 'Bar',
      'type' => \CRM_Utils_Type::T_STRING
    );

    $field = SpecFormatter::arrayToField($fieldData);

    $this->assertEquals('Foo', $field->getName());
    $this->assertEquals('String', $field->getDataType());
  }
}
