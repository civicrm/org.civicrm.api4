<?php

namespace Civi\API\Event\Subscriber;

use Civi\API\Event\Events;
use Civi\API\Event\SchemaMapBuildEvent;
use Civi\API\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContactSchemaMapSubscriber implements EventSubscriberInterface {
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
    $table = $schema->getTableByName('civicrm_contact');
    $joinable = new Joinable('civicrm_activity_contact', 'contact_id', 'created_activities');
    $joinable->addCondition('created_activities.record_type_id = 1');
    $joinable->setJoinType($joinable::JOIN_TYPE_ONE_TO_MANY);
    $table->addTableLink('id', $joinable);
  }
}
