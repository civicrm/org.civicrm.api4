<?php

namespace Civi\Test\Api4\Action;

/**
 * @group headless
 */
class CreateCustomValueTest extends BaseCustomValueTest {

  public function testGetWithCustomData() {
    $optionValues = ['r' => 'Red', 'g' => 'Green', 'b' => 'Blue'];

    $customGroupApi = \Civi::container()->get('custom_group.api');
    $customFieldApi = \Civi::container()->get('custom_field.api');
    $optionGroupApi = \Civi::container()->get('option_group.api');
    $optionValueApi = \Civi::container()->get('option_value.api');

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'MyContactFields',
      'extends' => 'Contact'
    ));

    $customFieldApi->request('create', array(
      'label' => 'Color',
      'options' => $optionValues,
      'custom_group_id' => $customGroup->getArrayCopy()['id'],
      'html_type' => 'Select',
      'data_type' => 'String'
    ));

    $customField = $customFieldApi->request('get', array(
      array('label', '=', 'Color')
    ))->first();

    $this->assertNotNull($customField['option_group_id']);
    $optionGroupId = $customField['option_group_id'];

    $optionGroup = $optionGroupApi->request('get', array(
      array('id', '=', $optionGroupId)
    ))->first();

    $this->assertEquals('Color', $optionGroup['title']);

    $createdOptionValues = $optionValueApi->request('get', array(
      array('option_group_id', '=', $optionGroupId)
    ))->getArrayCopy();

    $values = array_column($createdOptionValues, 'value');
    $labels = array_column($createdOptionValues, 'label');
    $createdOptionValues = array_combine($values, $labels);

    $this->assertEquals($optionValues, $createdOptionValues);
  }

}
