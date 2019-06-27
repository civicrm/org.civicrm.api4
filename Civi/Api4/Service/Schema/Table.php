<?php

namespace Civi\Api4\Service\Schema;

use Civi\Api4\Service\Schema\Joinable\Joinable;
use Civi\Api4\Service\Schema\Joinable\CustomGroupJoinable;
use Civi\Api4\Service\Schema\Joinable\BridgeJoinable;
use Civi\Api4\CustomGroup;
use Civi\Api4\Utils\CoreUtil;

class Table {

  /**
   * @var string
   */
  protected $name;

  /**
   * @var \Civi\Api4\Service\Schema\Joinable\Joinable[]
   *   Array of links to other tables
   */
  protected $tableLinks = [];

  /**
   * @param $name
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * @return string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * @param string $name
   *
   * @return $this
   */
  public function setName($name) {
    $this->name = $name;

    return $this;
  }

  /**
   * @return \Civi\Api4\Service\Schema\Joinable\Joinable[]
   */
  public function getTableLinks() {
    return $this->tableLinks;
  }

  /**
   * @return \Civi\Api4\Service\Schema\Joinable\Joinable[]
   *   Only those links that are not joining the table to itself
   */
  public function getExternalLinks() {
    return array_filter($this->tableLinks, function (Joinable $joinable) {
      return $joinable->getTargetTable() !== $this->getName();
    });
  }

  /**
   * @param \Civi\Api4\Service\Schema\Joinable\Joinable $linkToRemove
   */
  public function removeLink(Joinable $linkToRemove) {
    foreach ($this->tableLinks as $index => $link) {
      if ($link === $linkToRemove) {
        unset($this->tableLinks[$index]);
      }
    }
  }

  /**
   * @return void
   */
  public function addCustomTableLinks() {
    $tableName = $this->getName();
    if (strstr($tableName, 'civicrm_value_')) {
      $entity = CoreUtil::getCustomEntityByTableName($tableName);
      $links = CoreUtil::getCustomTableLinksByTableName($tableName);
    }
    else {
      $entity = CoreUtil::getApiNameFromTableName($tableName);
      $links = CoreUtil::getCustomTableLinks($entity);
    }

    foreach ($links as $alias => $link) {
      $joinable = new CustomGroupJoinable($link['tableName'], $alias, $link['isMultiple'], $entity, $link['columns']);
      $this->addTableLink('id', $joinable);

      foreach ($link['columns'] as $alias => $column) {
        $middleLink = new Joinable('civicrm_custom_field', 'id', $alias);
        $bridge = new BridgeJoinable('civicrm_custom_field', 'id', $alias, $middleLink);
        $bridge->setBaseTable('civicrm_custom_field');
        $bridge->setJoinType(Joinable::JOIN_TYPE_ONE_TO_ONE);
        $this->addTableLink($column, $bridge);
      }
    }
  }

  /**
   * @param string $baseColumn
   * @param \Civi\Api4\Service\Schema\Joinable\Joinable $joinable
   *
   * @return $this
   */
  public function addTableLink($baseColumn, Joinable $joinable) {
    $target = $joinable->getTargetTable();
    $targetCol = $joinable->getTargetColumn();
    $alias = $joinable->getAlias();

    if (!$this->hasLink($target, $targetCol, $alias)) {
      if (!$joinable->getBaseTable()) {
        $joinable->setBaseTable($this->getName());
      }
      if (!$joinable->getBaseColumn()) {
        $joinable->setBaseColumn($baseColumn);
      }
      $this->tableLinks[] = $joinable;
    }

    return $this;
  }

  /**
   * @param mixed $tableLinks
   *
   * @return $this
   */
  public function setTableLinks($tableLinks) {
    $this->tableLinks = $tableLinks;

    return $this;
  }

  /**
   * @param $target
   * @param $targetCol
   * @param $alias
   *
   * @return bool
   */
  private function hasLink($target, $targetCol, $alias) {
    foreach ($this->tableLinks as $link) {
      if ($link->getTargetTable() === $target
        && $link->getTargetColumn() === $targetCol
        && $link->getAlias() === $alias
      ) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
