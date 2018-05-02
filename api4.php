<?php

/**
 * @file
 */

require_once 'api4.civix.php';

use Civi\API\Request;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Procedural wrapper for the OO api version 4.
 *
 * @param $entity
 * @param $action
 * @param array $params
 *
 * @throws \API_Exception
 *
 * @return \Civi\Api4\Generic\Result
 */
function civicrm_api4($entity, $action, $params = []) {
  $params['version'] = 4;
  $request           = Request::create($entity, $action, $params);
  return \Civi::service('civi_api_kernel')->runRequest($request);
}

/**
 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
 */
function api4_civicrm_container($container) {
  $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
  $loader->load('services.xml');
  $container->getDefinition('civi_api_kernel')->addMethodCall(
    'registerApiProvider',
    [new Reference('action_object_provider')]
  );
  // Add event subscribers$container->get(.
  $dispatcher = $container->getDefinition('dispatcher');
  $subscribers = $container->findTaggedServiceIds('event_subscriber');
  foreach (array_keys($subscribers) as $subscriber) {
    $dispatcher->addMethodCall(
      'addSubscriber',
      [new Reference($subscriber)]
    );
  }
  // Add spec providers.
  $providers = $container->findTaggedServiceIds('spec_provider');
  $gatherer = $container->getDefinition('spec_gatherer');
  foreach (array_keys($providers) as $provider) {
    $gatherer->addMethodCall(
      'addSpecProvider',
      [new Reference($provider)]
    );
  }
  if (defined('CIVICRM_UF') && CIVICRM_UF === 'UnitTests') {
    $loader->load('tests/services.xml');
  }
}

/**
 * Implements hook_civicrm_coreResourceList().
 *
 * @param mixed $region
 */
function api4_civicrm_coreResourceList(&$list, $region) {
  if ('html-header' == $region) {
    Civi::resources()
      ->addScriptFile('org.civicrm.api4', 'js/api4.js', -9000, $region);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function api4_civicrm_config(&$config) {
  _api4_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @param array|string $files
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function api4_civicrm_xmlMenu(&$files) {
  _api4_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 *
 * @throws \CRM_Core_Exception
 * @throws \CRM_Exception
 */
function api4_civicrm_install() {
  _api4_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 *
 * @throws \CRM_Exception
 */
function api4_civicrm_uninstall() {
  _api4_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function api4_civicrm_enable() {
  _api4_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function api4_civicrm_disable() {
  _api4_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @param string $op
 *   the type of operation being performed; 'check' or
 *   'enqueue'.
 * @param $queue
 *   CRM_Queue_Queue, (for 'enqueue') the modifiable list of
 *   pending up upgrade tasks
 *
 * @return mixed
 *   Based on op. for 'check', returns array(boolean) (TRUE if upgrades are
 *   pending) for 'enqueue', returns void
 *
 * @throws \ReflectionException
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
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
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
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
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function api4_civicrm_angularModules(&$angularModules) {
  _api4_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function api4_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _api4_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
