<?php

namespace Civi\Test\Api4\Query;

use Civi\Api4\Entity\Contact;
use Civi\Api4\Entity\OptionGroup;
use Civi\Api4\Entity\OptionValue;
use Civi\Test\Api4\UnitTestCase;

/**
 * Class OneToOneJoinTest
 * @package Civi\Test\Api4\Query
 * @group headless
 */
class OneToOneJoinTest extends UnitTestCase {

  public function testOneToOneJoin() {
    $languageGroupId = OptionGroup::create()
      ->setValue('name', 'languages')
      ->execute()
      ->getArrayCopy()['id'];

    OptionValue::create()
      ->setValue('option_group_id', $languageGroupId)
      ->setValue('name', 'hy_AM')
      ->setValue('value', 'hy')
      ->setValue('label', 'Armenian')
      ->execute();

    OptionValue::create()
      ->setValue('option_group_id', $languageGroupId)
      ->setValue('name', 'eu_ES')
      ->setValue('value', 'eu')
      ->setValue('label', 'Basque')
      ->execute();

    $armenianContact = Contact::create()
      ->setValue('first_name', 'Contact')
      ->setValue('last_name', 'One')
      ->setValue('contact_type', 'Individual')
      ->setValue('preferred_language', 'hy_AM')
      ->execute()
      ->getArrayCopy();

    $basqueContact = Contact::create()
      ->setValue('first_name', 'Contact')
      ->setValue('last_name', 'Two')
      ->setValue('contact_type', 'Individual')
      ->setValue('preferred_language', 'eu_ES')
      ->execute()
      ->getArrayCopy();

    $contacts = Contact::get()
      ->addWhere('id', 'IN', [$armenianContact['id'], $basqueContact['id']])
      ->addSelect('preferred_language.label')
      ->addSelect('last_name')
      ->execute()
      ->indexBy('last_name')
      ->getArrayCopy();

    $this->assertEquals($contacts['One']['preferred_language']['label'], 'Armenian');
    $this->assertEquals($contacts['Two']['preferred_language']['label'], 'Basque');
  }

}
