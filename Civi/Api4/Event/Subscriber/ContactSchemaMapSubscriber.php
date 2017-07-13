<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Event\Events;
use Civi\Api4\Event\SchemaMapBuildEvent;
use Civi\Api4\Service\Schema\Joinable\BridgeJoinable;
use Civi\Api4\Service\Schema\Joinable\Contact\ActivityContactSourceJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use CRM_Utils_String as StringHelper;

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
    $this->addActivitySourceBridge($event);
  }

  /**
   * @param SchemaMapBuildEvent $event
   */
  protected function addActivitySourceBridge(SchemaMapBuildEvent $event) {
    $schema = $event->getSchemaMap();
    $table = $schema->getTableByName('civicrm_contact');

    $middleAlias = StringHelper::createRandom(10, implode(range('a', 'z')));
    $middleLink = new ActivityContactSourceJoinable($middleAlias);

    $bridge = new BridgeJoinable('civicrm_activity', 'id', 'source_activities', $middleLink);
    $bridge->setBaseTable('civicrm_contact');
    $bridge->setJoinType(Joinable::JOIN_TYPE_ONE_TO_MANY);

    $table->addTableLink('id', $bridge);
  }
}
