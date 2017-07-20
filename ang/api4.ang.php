<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
$result = Civi::container()->get('entity.api')->request('get');
$entities = array();
foreach ($result as $entity) {
  $entities[] = array(
    'id' => $entity,
    'text' => $entity,
  );
}
$vars = array(
  'entities' => $entities,
);
\Civi::resources()->addVars('api4', $vars);
return array(
  'js' => array(
    'ang/api4.js',
    'ang/api4/*.js',
    'ang/api4/*/*.js',
  ),
  'css' => array(
    'css/explorer.css',
  ),
  'partials' => array(
    'ang/api4',
  ),
  'requires' => array('crmUi', 'crmUtil', 'ngRoute', 'crmRouteBinder'),
);
