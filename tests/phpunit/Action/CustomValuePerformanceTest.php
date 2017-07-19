<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;
use Civi\Test\Api4\Traits\QueryCounterTrait;

/**
 * @group headless
 */
class CustomValuePerformanceTest extends BaseCustomValueTest {

  use QueryCounterTrait;

  public function testQueryCount() {

    $customGroupApi = \Civi::container()->get('custom_group.api');
    $customFieldApi = \Civi::container()->get('custom_field.api');
    $contactApi = \Civi::container()->get('contact.api');

    $customGroup = $customGroupApi->request('create', array(
      'name' => 'MyContactFields',
      'title' => 'MyContactFields',
      'extends' => 'Contact',
    ));

    $customGroupId = $customGroup->getArrayCopy()['id'];

    $customFieldApi->request('create', array(
      'label' => 'FavColor',
      'custom_group_id' => $customGroupId,
      'options' => array('r' => 'Red', 'g' => 'Green', 'b' => 'Blue'),
      'html_type' => 'Select',
      'data_type' => 'String',
    ));

    $customFieldApi->request('create', array(
      'label' => 'FavAnimal',
      'custom_group_id' => $customGroupId,
      'html_type' => 'Text',
      'data_type' => 'String'
    ));

    $customFieldApi->request('create', array(
      'label' => 'FavLetter',
      'custom_group_id' => $customGroupId,
      'html_type' => 'Text',
      'data_type' => 'String',
    ));

    $customFieldApi->request('create', array(
      'label' => 'FavFood',
      'custom_group_id' => $customGroupId,
      'html_type' => 'Text',
      'data_type' => 'String',
    ));

    $this->beginQueryCount();

    $contactApi->request('create', array(
      'first_name' => 'Red',
      'last_name' => 'Tester',
      'contact_type' => 'Individual',
      'MyContactFields.FavColor' => 'r',
      'MyContactFields.FavAnimal' => 'Sheep',
      'MyContactFields.FavLetter' => 'z',
      'MyContactFields.FavFood' => 'Coconuts',
    ));

    $params = new GetParameterBag();
    $params->addSelect('display_name');
    $params->addSelect('MyContactFields.FavColor.label');
    $params->addSelect('MyContactFields.FavColor.weight');
    $params->addSelect('MyContactFields.FavColor.is_default');
    $params->addSelect('MyContactFields.FavAnimal');
    $params->addSelect('MyContactFields.FavLetter');
    $params->addWhere('MyContactFields.FavColor', '=', 'r');
    $params->addWhere('MyContactFields.FavFood', '=', 'Coconuts');
    $params->addWhere('MyContactFields.FavAnimal', '=', 'Sheep');
    $params->addWhere('MyContactFields.FavLetter', '=', 'z');

    $contactApi->request('get', $params)->first();

    // this is intentionally high since, but performance should be addressed
    $this->assertLessThan(400, $this->getQueryCount());
  }
}
