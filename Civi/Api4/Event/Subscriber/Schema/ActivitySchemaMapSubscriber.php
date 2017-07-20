<?php

namespace Civi\Api4\Event\Subscriber\Schema;

use Civi\Api4\Service\Schema\Joinable\EntityTagJoinable;
use Civi\Api4\Event\Events;
use Civi\Api4\Event\SchemaMapBuildEvent;
use Civi\Api4\Service\Schema\Joinable\Activity\ActivityContactJoinable;
use Civi\Api4\Service\Schema\Joinable\BridgeJoinable;
use Civi\Api4\Service\Schema\Joinable\Joinable;
use Civi\Api4\Service\Schema\Table;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \CRM_Utils_String as StringHelper;

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

    $this->addAssigneesBridge($table);
    $this->addSourceBridge($table);
    $this->fixOptionValueJoin($table);
    $this->addEntityTagLink($table);
    $this->addTagsBridge($table);
  }

  /**
   * @param $table
   */
  private function addAssigneesBridge(Table $table) {
    $middleAlias = StringHelper::createRandom(10, implode(range('a', 'z')));
    $middleLink = new ActivityContactJoinable('Activity Assignees', $middleAlias);

    $bridge = new BridgeJoinable('civicrm_contact', 'id', 'assignees', $middleLink);
    $bridge->setBaseTable('civicrm_activity_contact');
    $bridge->setJoinType(Joinable::JOIN_TYPE_ONE_TO_MANY);
    $table->addTableLink('contact_id', $bridge);
  }

  /**
   * @param $table
   */
  private function addSourceBridge(Table $table) {
    $middleAlias = StringHelper::createRandom(10, implode(range('a', 'z')));
    $middleLink = new ActivityContactJoinable('Activity Source', $middleAlias);

    $bridge = new BridgeJoinable('civicrm_contact', 'id', 'source', $middleLink);
    $bridge->setBaseTable('civicrm_activity_contact');
    $bridge->setJoinType(Joinable::JOIN_TYPE_ONE_TO_ONE);
    $table->addTableLink('contact_id', $bridge);
  }

  /**
   * @param $table
   */
  private function fixOptionValueJoin(Table $table) {
    // activity_type_id is a FK to option_value 'ID', not to 'value'
    $activityTypeLink = $table->getLinkToAlias('activity_type');
    if ($activityTypeLink) {
      $activityTypeLink->setTargetColumn('id');
    }
  }

  /**
   * @param $table
   */
  private function addEntityTagLink(Table $table) {
    $table->addTableLink('id', new EntityTagJoinable($table->getName(), 'entityTags'));
  }

  /**
   * @param $table
   */
  protected function addTagsBridge(Table $table) {
    $middleAlias = StringHelper::createRandom(10, implode(range('a', 'z')));
    $middleLink = new EntityTagJoinable($table->getName(), $middleAlias);

    $bridge = new BridgeJoinable('civicrm_tag', 'id', 'tags', $middleLink);
    $bridge->setBaseTable('civicrm_entity_tag');
    $bridge->setBaseColumn('tag_id');
    $bridge->setJoinType(Joinable::JOIN_TYPE_ONE_TO_MANY);

    $table->addTableLink('id', $bridge);
  }
}