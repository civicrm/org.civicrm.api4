<?php

namespace Civi\API\V4\Event\Subscriber;

use Civi\API\V4\Event\Events;
use Civi\API\V4\Event\SchemaMapBuildEvent;
use Civi\API\V4\Service\Schema\Joinable\BridgeJoinable;
use Civi\API\V4\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ActivitySchemaMapSubscriber implements EventSubscriberInterface {
  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return array(
      Events::SCHEMA_MAP_BUILD => 'onSchemaBuild'
    );
  }

  /**
   * @param SchemaMapBuildEvent $event
   */
  public function onSchemaBuild(SchemaMapBuildEvent $event) {
    $schema = $event->getSchemaMap();
    $table = $schema->getTableByName('civicrm_activity');

    $actToActContact = new Joinable('civicrm_activity_contact', 'activity_id');
    $actToActContact->setBaseTable('civicrm_activity');
    $actToActContact->setBaseColumn('id');
    $alias = 'foafeofjwefoaaj';
    $actToActContact->setAlias($alias);

    $subSubSelect = sprintf(
      'SELECT id FROM civicrm_option_group WHERE name = "%s"',
      'activity_contacts'
    );

    $subSelect = sprintf(
      'SELECT value FROM civicrm_option_value WHERE name = "%s" AND option_group_id = (%s)',
      'Activity Assignees',
      $subSubSelect
    );

    $actToActContact->addCondition(sprintf('%s.record_type_id = (%s)', $alias, $subSelect));

    $bridgeJoinable = new BridgeJoinable('civicrm_contact', 'id', 'assignees', $actToActContact);
    $bridgeJoinable->setJoinType(Joinable::JOIN_TYPE_ONE_TO_MANY);
    $table->addTableLink('contact_id', $bridgeJoinable);

    // order is important or base table will be overwritten
    $bridgeJoinable->setBaseTable($actToActContact->getTargetTable());
  }
}
