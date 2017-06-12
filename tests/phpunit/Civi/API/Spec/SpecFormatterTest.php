<?php

namespace phpunit\Civi\API\Spec;

use Civi\API\Spec\FieldSpec;
use Civi\API\Spec\RequestSpec;
use Civi\API\Spec\SpecFormatter;
use Civi\API\V4\UnitTestCase;

/**
 * @group headless
 */
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

  /**
   * @dataProvider arrayFieldSpecProvider
   *
   * @param array $fieldData
   * @param string $expectedName
   * @param string $expectedType
   */
  public function testArrayToField($fieldData, $expectedName, $expectedType) {
    $field = SpecFormatter::arrayToField($fieldData);

    $this->assertEquals($expectedName, $field->getName());
    $this->assertEquals($expectedType, $field->getDataType());
  }

  /**
   * @return array
   */
  public function arrayFieldSpecProvider() {
    return array(
      array(
        array(
          'name' => 'Foo',
          'title' => 'Bar',
          'type' => \CRM_Utils_Type::T_STRING
        ),
        'Foo',
        'String'
      ),
      array(
        array(
          'name' => 'MyField',
          'title' => 'Bar',
          'type' => \CRM_Utils_Type::T_STRING,
          'data_type' => 'Boolean' // this should take precedence
        ),
        'MyField',
        'Boolean'
      )
    );
  }
}
