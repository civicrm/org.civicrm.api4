<?php

require_once 'api4.civix.php';

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\Reference;
use Civi\API\Provider\ActionObjectProvider;
use Civi\API\Event\Subscriber\CustomGroupPreCreationSubscriber;
use Civi\API\Event\Subscriber\CustomFieldPreCreationSubscriber;
use Civi\API\Event\Subscriber\ContactPreUpdateSubscriber;

/**
 * Procedural wrapper for the OO api version 4.
 *
 * @param $entity
 * @param $action
 * @param array $params
 * @return \Civi\API\Result
 */
function civicrm_api4($entity, $action, $params = array()) {
  $params['version'] = 4;
  $request = \Civi\API\Request::create($entity, $action, $params);
  return \Civi::service('civi_api_kernel')->runRequest($request);
}

/**
 * @param Symfony\Component\DependencyInjection\ContainerBuilder $container
 */
function api4_civicrm_container($container) {
  $container->addResource(new FileResource(__FILE__));
  $container->setDefinition('action_object_provider', new Definition(
    ActionObjectProvider::class
  ));
  $container->findDefinition('dispatcher')->addMethodCall('addSubscriber',
    array(new Reference('action_object_provider'))
  );
  $container->findDefinition('civi_api_kernel')->addMethodCall('registerApiProvider',
    array(new Reference('action_object_provider'))
  );

  $dispatcher = $container->get('dispatcher');
  $dispatcher->addSubscriber(new CustomGroupPreCreationSubscriber());
  $dispatcher->addSubscriber(new CustomFieldPreCreationSubscriber());
  $dispatcher->addSubscriber(new ContactPreUpdateSubscriber());
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function api4_civicrm_config(&$config) {
  _api4_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function api4_civicrm_xmlMenu(&$files) {
  _api4_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function api4_civicrm_install() {
  _api4_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function api4_civicrm_uninstall() {
  _api4_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function api4_civicrm_enable() {
  _api4_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function api4_civicrm_disable() {
  _api4_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function api4_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _api4_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function api4_civicrm_managed(&$entities) {
  _api4_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function api4_civicrm_angularModules(&$angularModules) {
_api4_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function api4_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _api4_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Functions below this ship commented out. Uncomment as required.
 *

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function api4_civicrm_preProcess($formName, &$form) {

}

*/
