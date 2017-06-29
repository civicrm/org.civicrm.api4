<?php

namespace phpunit\Civi\API\V4\Action;

use Civi\API\V4\UnitTestCase;
use Civi\Api4\Activity;
use Civi\TestDataLoaderTrait;

/**
 * @group headless
 *
 * This class tests a series of complex query situations described in the
 * initial APIv4 specification
 */
class ComplexQueryTest extends UnitTestCase {

  use TestDataLoaderTrait;

  public function setUpHeadless() {
    $relatedTables = array(
      'civicrm_contact',
      'civicrm_option_group',
      'civicrm_option_value',
      'civicrm_activity',
      'civicrm_activity_contact',
    );
    $this->cleanup(array('tablesToTruncate' => $relatedTables));
    $this->loadDataSet('DefaultDataSet');

    return parent::setUpHeadless();
  }

  /**
   * Fetch all activities for housing support cases. Expects a single activity
   * loaded from the data set.
   */
  public function testGetAllHousingSupportActivities() {
    $results = Activity::get()
      ->setCheckPermissions(FALSE)
      ->addWhere('activity_type.name', '=', 'housing_support')
      ->execute();

    $this->assertCount(1, $results);
  }

  /**
   * Fetch all activities with a blue tag; and return all tags on the activities
   */
  public function testGetAllTagsForBlueTaggedActivities() {

  }

  /**
   * Fetch contacts named 'Bob' and all of their blue activities
   */
  public function testGetAllBlueActivitiesForBobs() {

  }

  /**
   * Get all contacts in a zipcode and return their Home or Work email addresses
   */
  public function testGetHomeOrWorkEmailsForContactsWithZipcode() {

  }

  /**
   * Fetch all activities where Bob is the assignee or source
   */
  public function testGetActivitiesWithBobAsAssigneeOrSource() {

  }

  /**
   * Get all contacts which
   * (a) have address in zipcode 94117 or 94118 or in city "San Francisco","LA"
   * and
   * (b) are not deceased and
   * (c) have a custom-field "most_important_issue=Environment".
   */
  public function testAWholeLotOfConditions() {

  }

  /**
   * Get participants who attended CiviCon 2012 but not CiviCon 2013.
   * Return their name and email.
   */
  public function testGettingNameAndEmailOfAttendeesOfCiviCon2012Only() {

  }

}
