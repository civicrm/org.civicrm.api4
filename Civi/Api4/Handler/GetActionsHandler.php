<?php

namespace Civi\Api4\Handler;

use Civi\API\Exception\NotImplementedException;
use Civi\Api4\Request;
use Civi\Api4\RequestHandler;
use Civi\Api4\Utils\ReflectionUtils;

/**
 * Get actions for an entity with a list of accepted params
 */
class GetActionsHandler extends RequestHandler {

  private $actions = array();

  public function handle(Request $request) {
    $this->actions = array(); // todo check if actions property is necessary
    $includePaths = array_unique(explode(PATH_SEPARATOR, get_include_path()));
    $entityReflection = new \ReflectionClass('\Civi\Api4\Entity\\' . $request->getEntity());
    // First search entity-specific actions (including those provided by extensions
    foreach ($includePaths as $path) {
      $dir = \CRM_Utils_File::addTrailingSlash($path) . 'Civi/Api4/Action/' . $request->getEntity();
      $this->scanDir($dir);
    }
    // Scan all generic actions unless this entity does not extend generic entity
    if ($entityReflection->getParentClass()) {
      foreach ($includePaths as $path) {
        $dir = \CRM_Utils_File::addTrailingSlash($path) . 'Civi/Api4/Action';
        $this->scanDir($dir);
      }
    }
    // For oddball entities, just return their static methods
    else {
      foreach ($entityReflection->getMethods(\ReflectionMethod::IS_STATIC) as $method) {
        $this->loadAction($method->getName());
      }
    }
    $request->exchangeArray(array_values($this->actions));
  }

  /**
   * @inheritdoc
   */
  public function getAction() {
    return 'getActions';
  }

  /**
   * @param $dir
   */
  private function scanDir($dir) {
    if (is_dir($dir)) {
      foreach (glob("$dir/*.php") as $file) {
        $matches = array();
        preg_match('/(\w*).php/', $file, $matches);
        $actionName = array_pop($matches);
        if ($actionName !== 'AbstractAction') {
          $this->loadAction(lcfirst($actionName));
        }
      }
    }
  }

  /**
   * @param $actionName
   */
  private function loadAction($actionName) {
    try {
      if (!isset($this->actions[$actionName])) {
        /* @var RequestHandler $action */
        $action = call_user_func(array("\\Civi\\Api4\\Entity\\" . $this->getEntity(), $actionName));
        $actionReflection = new \ReflectionClass($action);
        $actionInfo = ReflectionUtils::getCodeDocs($actionReflection);
        unset($actionInfo['method']);
        $this->actions[$actionName] = array('name' => $actionName) + $actionInfo;
        $this->actions[$actionName]['params'] = $action->getParamInfo();
      }
    }
    catch (NotImplementedException $e) {
    }
  }

}
