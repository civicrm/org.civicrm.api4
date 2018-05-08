<?php

namespace Civi\Api4\Event\Subscriber;

use Civi\Api4\Event\Events;
use Civi\Api4\Event\SchemaMapBuildEvent;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class RelationshipSchemaMapSubscriber.
 */
class RelationshipSchemaMapSubscriber implements EventSubscriberInterface {

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return [
      Events::SCHEMA_MAP_BUILD => 'onSchemaBuild',
    ];
  }

  /**
   * @param \Civi\Api4\Event\SchemaMapBuildEvent $event
   */
  public function onSchemaBuild(SchemaMapBuildEvent $event) {
    $schema = $event->getSchemaMap();
    $table = $schema->getTableByName('civicrm_relationship');

    $this->addSecondContactToJoin($table);
  }

  /**
   * @param \Civi\Api4\Service\Schema\Table $table
   */
  private function addSecondContactToJoin($table) {
    $alias = 'contact_b';
    $targetTable = 'civicrm_contact';
    $targetColumn = 'id';
    $baseColumn = 'contact_id_b';

    $joinable = new Joinable($targetTable, $targetColumn, $alias);
    $table->addTableLink($baseColumn, $joinable);
  }

}
