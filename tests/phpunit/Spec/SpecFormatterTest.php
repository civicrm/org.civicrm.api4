<?php

namespace Civi\Test\Api4\Spec;

use Civi\Api4\Service\Spec\CustomFieldSpec;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;
use Civi\Api4\Service\Spec\SpecFormatter;
use Civi\Test\Api4\UnitTestCase;

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

    $this->assertArrayHasKey($fieldName, $arraySpec['fields']);
    $this->assertEquals('String', $arraySpec['fields'][$fieldName]['data_type']);
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

  public function testCustomFieldWillBeReturned() {
    $customGroupId = 1432;
    $customFieldId = 3333;
    $name = 'MyFancyField';

    $data = array(
      'custom_group_id' => $customGroupId,
      'id' => $customFieldId,
      'name' => $name,
      'data_type' => 'String',
    );

    /** @var CustomFieldSpec $field */
    $field = SpecFormatter::arrayToField($data);

    $this->assertInstanceOf(CustomFieldSpec::class, $field);
    $this->assertEquals($customGroupId, $field->getCustomGroupId());
    $this->assertEquals($customFieldId, $field->getCustomFieldId());
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
