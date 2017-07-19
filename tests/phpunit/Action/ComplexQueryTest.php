<?php

namespace Civi\Test\Api4\Action;

use Civi\Api4\GetParameterBag;
use Civi\Test\Api4\Traits\TableDropperTrait;
use Civi\Test\Api4\UnitTestCase;

/**
 * @group headless
 *
 * This class tests a series of complex query situations described in the
 * initial APIv4 specification
 */
class ComplexQueryTest extends UnitTestCase {

  use TableDropperTrait;

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
      'civicrm_entity_tag',
      'civicrm_email',
      'civicrm_phone',
      'civicrm_address',
      'civicrm_tag',
      'civicrm_location_type',
      'civicrm_custom_group',
      'civicrm_custom_field',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));

    $this->dropByPrefix('civicrm_value_mycontactfields');

    $this->loadDataSet('ContactCustomFields');
    $this->loadDataSet('LocationTypes');
    $this->loadDataSet('OptionGroups');
    $this->loadDataSet('ActivityContactTypes');
    $this->loadDataSet('ColoredTags');
    $this->loadDataSet('NumberedContacts');
    $this->loadDataSet('NumberedContactPhones');
    $this->loadDataSet('NumberedContactAddresses');
    $this->loadDataSet('NumberedContactsEmails');
    $this->loadDataSet('ComplexQuery');

    return parent::setUpHeadless();
  }

  public function tearDown() {
    $this->dropByPrefix('civicrm_value_mycontactfields');
  }

  /**
   * Fetch all activities for housing support cases. Expects a single activity
   * loaded from the data set.
   */
  public function testGetAllHousingSupportActivities() {
    $activityApi = \Civi::container()->get('activity.api');
    $params = new GetParameterBag();
    $params->addWhere('activity_type.name', '=', 'housing_support');
    $results = $activityApi->get($params);

    $this->assertCount(1, $results);
  }

  /**
   * Fetch all activities with a blue tag; and return all tags on the activities
   */
  public function testGetAllActivitiesWithTagsForBlueTaggedActivities() {
    $results = ActivityApi::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('subject')
      ->addSelect('activity_type.label')
      ->addSelect('tags.name')
      ->addWhere('tags.name', '=', 'blue')
      ->execute();

    $this->assertCount(1, $results);
    $first = $results->first();
    $this->assertCount(2, $first['tags']);
    $tagNames = array_column($first['tags'], 'name');
    $this->assertContains('blue', $tagNames);
  }

  /**
   * Fetch contacts named 'Bob' and all of their blue activities
   */
  public function testGetActivitiesForBobsWithBlueActivities() {
    $result = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('first_name')
      ->addSelect('last_name')
      ->addSelect('source_activities.subject')
      ->addSelect('source_activities.tags.name')
      ->addWhere('first_name', '=', 'Bob')
      ->addWhere('source_activities.tags.name', '=', 'blue')
      ->execute();

    $this->assertCount(1, $result);
    $first = $result->first();
    $this->assertEquals('Bob', $first['first_name']);
    $this->assertCount(2, $first['source_activities']);
  }

  /**
   * Get all contacts in a zipcode and return their Home or Work email addresses
   */
  public function testEmailsForContactsWithZipcode() {
    $contacts = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('emails.email')
      ->addSelect('addresses.postal_code')
      ->addWhere('addresses.postal_code', '=', '11201')
      ->execute();

    $this->assertCount(1, $contacts);
    $firstContact = $contacts->first();
    $firstAddress = array_shift($firstContact['addresses']);
    $this->assertEquals('11201', $firstAddress['postal_code']);
    $this->assertCount(1, $firstContact['emails']);
    $firstEmail = array_shift($firstContact['emails']);
    $this->assertEquals('contact2_home@fakedomain.com', $firstEmail['email']);
  }

  /**
   * Fetch all activities where Bob is the assignee or source
   *
   * ComplexQuery.json has 4 activities, bob is source for 2, assignee for 2,
   * 1 of which he is also source for. So there is only 1 where he is neither
   * source or assignee.
   *
   * For now this test is to show what is possible without using the OR operator
   */
  public function testGetActivitiesWithBobAsAssigneeOrSource() {
    $asAssignee = ActivityApi::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('subject')
      ->addWhere('assignees.first_name', '=', 'Bob')
      ->execute()
      ->indexBy('id');

    $asSource = ActivityApi::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('subject')
      ->addWhere('source.first_name', '=', 'Bob')
      ->execute()
      ->indexBy('id');

    $this->assertEquals(4, $asAssignee->count() + $asSource->count());
    $all = $asAssignee->getArrayCopy() + $asSource->getArrayCopy();
    $this->assertCount(3, $all);
  }

  /**
   * Get all contacts which
   * (a) have address in zipcode 94117 or 94118 or in city "San Francisco","LA"
   * and
   * (b) are not deceased and
   * (c) have a custom-field "most_important_issue=Environment".
   */
  public function testAWholeLotOfConditions() {
    $byZipcode = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('MyContactFields.MostImportantIssue')
      ->addWhere('is_deceased', '=', FALSE)
      ->addWhere('MyContactFields.MostImportantIssue', '=', 'Environment')
      ->addWhere('addresses.postal_code', 'IN', array('94117', '94118'))
      ->execute()
      ->indexBy('id');

    $byCity = Contact::get()
      ->setCheckPermissions(FALSE)
      ->addSelect('MyContactFields.MostImportantIssue')
      ->addWhere('is_deceased', '=', FALSE)
      ->addWhere('MyContactFields.MostImportantIssue', '=', 'Environment')
      ->addWhere('addresses.city', '=', 'San Francisco')
      ->execute()
      ->indexBy('id');

    $this->assertEquals(2, $byCity->count() + $byZipcode->count());
    $all = $byZipcode->getArrayCopy() + $byCity->getArrayCopy();
    foreach ($all as $contact) {
      $this->assertEquals('Environment', $contact['MyContactFields']['MostImportantIssue']);
    }
  }

  /**
   * Get participants who attended CiviCon 2012 but not CiviCon 2013.
   * Return their name and email.
   */
  public function testGettingNameAndEmailOfAttendeesOfCiviCon2012Only() {

  }

}
