<?php

/**
 * @file
 * AUTO-GENERATED FILE -- Civix may overwrite any changes made to this file.
 */

/**
 * (Delegated) Implements hook_civicrm_config().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function _api4_civix_civicrm_config(&$config = NULL) {
  static $configured = FALSE;
  if ($configured) {
    return;
  }
  $configured = TRUE;
  $template = &CRM_Core_Smarty::singleton();
  $extRoot = __DIR__ . DIRECTORY_SEPARATOR;
  $extDir = $extRoot . 'templates';
  if (\is_array($template->template_dir)) {
    \array_unshift($template->template_dir, $extDir);
  }
  else {
    $template->template_dir = [$extDir, $template->template_dir];
  }
  $include_path = $extRoot . PATH_SEPARATOR . \get_include_path();
  \set_include_path($include_path);
}

/**
 * (Delegated) Implements hook_civicrm_xmlMenu().
 *
 * @param array|string $files
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function _api4_civix_civicrm_xmlMenu(&$files) {
  foreach (_api4_civix_glob(__DIR__ . '/xml/Menu/*.xml') as $file) {
    $files[] = $file;
  }
}

/**
 * Implements hook_civicrm_install().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 *
 * @throws \CRM_Core_Exception
 * @throws \CRM_Exception
 */
function _api4_civix_civicrm_install() {
  _api4_civix_civicrm_config();
  if ($upgrader = _api4_civix_upgrader()) {
    $upgrader->onInstall();
  }
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 *
 * @throws \ReflectionException
 */
function _api4_civix_civicrm_postInstall() {
  _api4_civix_civicrm_config();
  if (($upgrader = _api4_civix_upgrader()) && \is_callable([$upgrader, 'onPostInstall'])) {
    $upgrader->onPostInstall();
  }
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 *
 * @throws \CRM_Exception
 */
function _api4_civix_civicrm_uninstall() {
  _api4_civix_civicrm_config();
  if ($upgrader = _api4_civix_upgrader()) {
    $upgrader->onUninstall();
  }
}

/**
 * (Delegated) Implements hook_civicrm_enable().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function _api4_civix_civicrm_enable() {
  _api4_civix_civicrm_config();
  if (($upgrader = _api4_civix_upgrader()) && \is_callable([$upgrader, 'onEnable'])) {
    $upgrader->onEnable();
  }
}

/**
 * (Delegated) Implements hook_civicrm_disable().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 *
 * @return mixed
 */
function _api4_civix_civicrm_disable() {
  _api4_civix_civicrm_config();
  if (($upgrader = _api4_civix_upgrader()) && \is_callable([$upgrader, 'onDisable'])) {
    $upgrader->onDisable();
  }
}

/**
 * (Delegated) Implements hook_civicrm_upgrade().
 *
 * @param string          $op
 *   the type of operation being performed; 'check' or 'enqueue'.
 * @param CRM_Queue_Queue $queue
 *   (for 'enqueue') the modifiable list of pending up upgrade tasks.
 *
 * @return mixed based on op. for 'check', returns array(boolean) (TRUE if
 *   upgrades are pending) for 'enqueue', returns void
 *
 * @throws \ReflectionException
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function _api4_civix_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  if ($upgrader = _api4_civix_upgrader()) {
    return $upgrader->onUpgrade($op, $queue);
  }
}

/**
 * @return CRM_Api4_Upgrader
 */
function _api4_civix_upgrader() {
  if (!\file_exists(__DIR__ . '/CRM/Api4/Upgrader.php')) {
    return NULL;
  }

  return CRM_Api4_Upgrader_Base::instance();
}

/**
 * Search directory tree for files which match a glob pattern.
 *
 * Note: Dot-directories (like "..", ".git", or ".svn") will be ignored.
 * Note: In Civi 4.3+, delegate to CRM_Utils_File::findFiles()
 *
 * @param $dir
 *   string, base dir
 * @param $pattern
 *   string, glob pattern, eg "*.txt"
 *
 * @return array|string
 */
function _api4_civix_find_files($dir, $pattern) {
  if (\is_callable(['CRM_Utils_File', 'findFiles'])) {
    return CRM_Utils_File::findFiles($dir, $pattern);
  }
  $todos = [$dir];
  $result = [];
  while (!empty($todos)) {
    $subdir = \array_shift($todos);
    foreach (_api4_civix_glob("${subdir}/${pattern}") as $match) {
      if (!\is_dir($match)) {
        $result[] = $match;
      }
    }
    if ($dh = \opendir($subdir)) {
      while (FALSE !== ($entry = \readdir($dh))) {
        $path = $subdir . DIRECTORY_SEPARATOR . $entry;
        if ('.' == $entry[0]) {
        }
        elseif (\is_dir($path)) {
          $todos[] = $path;
        }
      }
      \closedir($dh);
    }
  }

  return $result;
}

/**
 * (Delegated) Implements hook_civicrm_managed().
 *
 * Find any *.mgd.php files, merge their content, and return.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function _api4_civix_civicrm_managed(&$entities) {
  $mgdFiles = _api4_civix_find_files(__DIR__, '*.mgd.php');
  foreach ($mgdFiles as $file) {
    $es = include $file;
    foreach ($es as $e) {
      if (empty($e['module'])) {
        $e['module'] = 'org.civicrm.api4';
      }
      $entities[] = $e;
      if (empty($e['params']['version'])) {
        $e['params']['version'] = '3';
      }
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_caseTypes().
 *
 * Find any and return any files matching "xml/case/*.xml"
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function _api4_civix_civicrm_caseTypes(&$caseTypes) {
  if (!\is_dir(__DIR__ . '/xml/case')) {
    return;
  }
  foreach (_api4_civix_glob(__DIR__ . '/xml/case/*.xml') as $file) {
    $name = \preg_replace('/\.xml$/', '', \basename($file));
    if ($name != CRM_Case_XMLProcessor::mungeCaseType($name)) {
      $errorMessage = \sprintf(
       'Case-type file name is malformed (%s vs %s)',
       $name,
       CRM_Case_XMLProcessor::mungeCaseType($name)
        );
      CRM_Core_Error::fatal($errorMessage);
      // Throw new CRM_Core_Exception($errorMessage);
    }
    $caseTypes[$name] = [
      'module' => 'org.civicrm.api4',
      'name' => $name,
      'file' => $file,
    ];
  }
}

/**
 * (Delegated) Implements hook_civicrm_angularModules().
 *
 * Find any and return any files matching "ang/*.ang.php"
 *
 * Note: This hook only runs in CiviCRM 4.5+.
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function _api4_civix_civicrm_angularModules(&$angularModules) {
  if (!\is_dir(__DIR__ . '/ang')) {
    return;
  }
  $files = _api4_civix_glob(__DIR__ . '/ang/*.ang.php');
  foreach ($files as $file) {
    $name = \preg_replace(':\.ang\.php$:', '', \basename($file));
    $module = include $file;
    if (empty($module['ext'])) {
      $module['ext'] = 'org.civicrm.api4';
    }
    $angularModules[$name] = $module;
  }
}

/**
 * Glob wrapper which is guaranteed to return an array.
 *
 * The documentation for glob() says, "On some systems it is impossible to
 * distinguish between empty match and an error." Anecdotally, the return
 * result for an empty match is sometimes array() and sometimes FALSE.
 * This wrapper provides consistency.
 *
 * @see http://php.net/glob
 *
 * @param string $pattern
 *
 * @return array, possibly empty
 */
function _api4_civix_glob($pattern) {
  $result = \glob($pattern);

  return \is_array($result) ? $result : [];
}

/**
 * Inserts a navigation menu item at a given place in the hierarchy.
 *
 * @param array $menu
 *   - menu hierarchy.
 * @param string|array $path
 *   - path where insertion should happen (ie.
 *   Administer/System Settings)
 * @param array $item
 *   - menu you need to insert (parent/child attributes will
 *   be filled for you)
 *
 * @return bool
 */
function _api4_civix_insert_navigation_menu(&$menu, $path, $item) {
  // If we are done going down the path, insert menu.
  if (empty($path)) {
    $menu[] = [
      'attributes' => \array_merge([
        'label' => CRM_Utils_Array::value('name', $item),
        'active' => 1,
      ], $item),
    ];

    return TRUE;
  }
  // Find an recurse into the next level down.
  $found = FALSE;
  $path = \explode('/', $path);
  $first = \array_shift($path);
  foreach ($menu as $key => &$entry) {
    if ($entry['attributes']['name'] == $first) {
      if (!isset($entry['child'])) {
        $entry['child'] = [];
      }
      $found = _api4_civix_insert_navigation_menu(
       $entry['child'],
       \implode('/', $path),
       $item,
       $key
       );
    }
  }

  return $found;
}

/**
 * (Delegated) Implements hook_civicrm_navigationMenu().
 */
function _api4_civix_navigationMenu(&$nodes) {
  if (!\is_callable(['CRM_Core_BAO_Navigation', 'fixNavigationMenu'])) {
    _api4_civix_fixNavigationMenu($nodes);
  }
}

/**
 * Given a navigation menu, generate navIDs for any items which are
 * missing them.
 */
function _api4_civix_fixNavigationMenu(&$nodes) {
  $maxNavID = 1;
  \array_walk_recursive($nodes, function ($item, $key) use (&$maxNavID) {
    if ('navID' === $key) {
      $maxNavID = \max($maxNavID, $item);
    }
  });
  _api4_civix_fixNavigationMenuItems($nodes, $maxNavID, NULL);
}

/**
 *
 */
function _api4_civix_fixNavigationMenuItems(&$nodes, &$maxNavID, $parentID) {
  $origKeys = \array_keys($nodes);
  foreach ($origKeys as $origKey) {
    if (!isset($nodes[$origKey]['attributes']['parentID']) && NULL !== $parentID) {
      $nodes[$origKey]['attributes']['parentID'] = $parentID;
    }
    // If no navID, then assign navID and fix key.
    if (!isset($nodes[$origKey]['attributes']['navID'])) {
      $newKey = ++$maxNavID;
      $nodes[$origKey]['attributes']['navID'] = $newKey;
      $nodes[$newKey] = $nodes[$origKey];
      unset($nodes[$origKey]);
      $origKey = $newKey;
    }
    if (isset($nodes[$origKey]['child'])
    && \is_array($nodes[$origKey]['child'])) {
      _api4_civix_fixNavigationMenuItems(
      $nodes[$origKey]['child'],
      $maxNavID,
      $nodes[$origKey]['attributes']['navID']
       );
    }
  }
}

/**
 * (Delegated) Implements hook_civicrm_alterSettingsFolders().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function _api4_civix_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  static $configured = FALSE;
  if ($configured) {
    return;
  }
  $configured = TRUE;
  $settingsDir = __DIR__ . DIRECTORY_SEPARATOR . 'settings';
  if (\is_dir($settingsDir) && !\in_array($settingsDir, $metaDataFolders)) {
    $metaDataFolders[] = $settingsDir;
  }
}
