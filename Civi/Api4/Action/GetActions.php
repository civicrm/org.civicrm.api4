<?php

namespace Civi\Api4\Action;

use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Generic\AbstractAction;
use Civi\Api4\Generic\BasicGetAction;
use Civi\Api4\Utils\ReflectionUtils;

/**
 * Get actions for an entity with a list of accepted params
 */
class GetActions extends BasicGetAction {

  /**
   * Override default to allow open access
   * @inheritDoc
   */
  protected $checkPermissions = FALSE;

  private $_actions = [];

  private $_actionsToGet = [];

  protected function getRecords() {
    foreach ($this->where as $clause) {
      if ($clause[0] == 'name' && in_array($clause[1], ['=', 'IN'])) {
        $this->_actionsToGet = (array) $clause[2];
      }
    }
    $entityReflection = new \ReflectionClass('\Civi\Api4\\' . $this->getEntityName());
    foreach ($entityReflection->getMethods(\ReflectionMethod::IS_STATIC | \ReflectionMethod::IS_PUBLIC) as $method) {
      $actionName = $method->getName();
      if ($actionName != 'permissions' && $actionName[0] != '_') {
        $this->loadAction($actionName);
      }
    }
    if (!$this->_actionsToGet || count($this->_actionsToGet) > count($this->_actions)) {
      $includePaths = array_unique(explode(PATH_SEPARATOR, get_include_path()));
      // Search entity-specific actions (including those provided by extensions)
      foreach ($includePaths as $path) {
        $dir = \CRM_Utils_File::addTrailingSlash($path) . 'Civi/Api4/Action/' . $this->getEntityName();
        $this->scanDir($dir);
      }
    }
    ksort($this->_actions);
    return $this->_actions;
  }

  /**
   * @param $dir
   */
  private function scanDir($dir) {
    if (is_dir($dir)) {
      foreach (glob("$dir/*.php") as $file) {
        $matches = [];
        preg_match('/(\w*).php/', $file, $matches);
        $actionName = array_pop($matches);
        $this->loadAction(lcfirst($actionName));
      }
    }
  }

  /**
   * @param $actionName
   */
  private function loadAction($actionName) {
    try {
      if (!isset($this->_actions[$actionName]) && (!$this->_actionsToGet || in_array($actionName, $this->_actionsToGet))) {
        /* @var AbstractAction $action */
        $action = call_user_func(["\\Civi\\Api4\\" . $this->getEntityName(), $actionName], NULL);
        if (is_object($action)) {
          $this->_actions[$actionName] = ['name' => $actionName];
          if (!$this->select || array_diff($this->select, ['params', 'name'])) {
            $actionReflection = new \ReflectionClass($action);
            $actionInfo = ReflectionUtils::getCodeDocs($actionReflection);
            unset($actionInfo['method']);
            $this->_actions[$actionName] += $actionInfo;
          }
          if (!$this->select || in_array('name', $this->select)) {
            $this->_actions[$actionName]['params'] = $action->getParamInfo();
          }
        }
      }
    }
    catch (NotImplementedException $e) {
    }
  }

}
