<?php

require_once 'api4.civix.php';

use Civi\Api4\Exception\Api4Exception;
use CRM_Utils_Array as ArrayHelper;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Civi\Api4\Response;
use Civi\Api4\ApiInterface;

/**
 * Procedural wrapper for the OO api version 4.
 *
 * @param $entity
 * @param $action
 * @param array $params
 * @param bool $checkPermission
 *
 * @return Response
 */
function civicrm_api4($entity, $action, $params = array(), $checkPermission = TRUE) {
  $serviceId = sprintf('%s.api', strtolower($entity));
  $container = Civi::container();

  if (!$container->has($serviceId)) {
    $err = sprintf(
      'The "%s" API was not found. Join the team and implement it!',
      $entity
    );
    throw new Api4Exception($err);
  }

  /** @var ApiInterface $api */
  $api = $container->get($serviceId);
  $params = new ParameterBag($params);

  return $api->request($action, $params, $checkPermission);
}

/**
 * @param ContainerBuilder $container
 */
function api4_civicrm_container($container) {
  $loader = new XmlFileLoader($container, new FileLocator(__DIR__));
  $loader->load('services.xml');
  $loader->load('api_services.xml');

  // add event subscribers(
  $dispatcher = $container->getDefinition('dispatcher');
  $subscribers = $container->findTaggedServiceIds('event_subscriber');

  foreach (array_keys($subscribers) as $subscriber) {
    $dispatcher->addMethodCall(
      'addSubscriber',
      array(new Reference($subscriber))
    );
  }

  // add spec providers
  $providers = $container->findTaggedServiceIds('spec_provider');
  $gatherer = $container->getDefinition('spec_gatherer');

  foreach (array_keys($providers) as $provider) {
    $gatherer->addMethodCall(
      'addSpecProvider',
      array(new Reference($provider))
    );
  }

  // add API actions
  $apiEntities = $container->findTaggedServiceIds('api');
  $entityRegister = $container->getDefinition('entity_register');

  // standard API actions
  $standardCreate = $container->getDefinition('standard.create_handler');
  $standardGet = $container->getDefinition('standard.get_handler');
  $standardUpdate = $container->getDefinition('standard.update_handler');
  $standardDelete = $container->getDefinition('standard.delete_handler');
  $standardGetFields = $container->getDefinition('standard.get_fields_handler');

  foreach ($apiEntities as $serviceId => $attributes) {
    $definition = $container->getDefinition($serviceId);

    // check for standard attribute on tag
    $isStandard = array_reduce($attributes, function($carry, $attribute) {
      return ArrayHelper::value('standard', $attribute, $carry);
    }, FALSE);

    // add standard actions
    if ($isStandard) {
      $definition->addMethodCall('addHandler', array($standardGet));
      $definition->addMethodCall('addHandler', array($standardCreate));
      $definition->addMethodCall('addHandler', array($standardDelete));
      $definition->addMethodCall('addHandler', array($standardGetFields));
      $definition->addMethodCall('addHandler', array($standardUpdate));
    }

    // register Entity API
    $entityName = $definition->getArgument(1);
    $entityRegister->addMethodCall('register', array($entityName));
  }

  if (defined('CIVICRM_UF') && CIVICRM_UF === 'UnitTests') {
    $loader->load('tests/services.xml');
  }
}

/**
 * Implements hook_civicrm_coreResourceList().
 */
function api4_civicrm_coreResourceList(&$list, $region) {
  if ($region == 'html-header') {
    Civi::resources()->addScriptFile('org.civicrm.api4', 'js/api4.js', -9000, $region);
  }
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
