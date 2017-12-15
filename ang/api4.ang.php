<?php
// This file declares an Angular module which can be autoloaded
// in CiviCRM. See also:
// http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
$result = Civi\Api4\Entity\Entity::get()
  ->setCheckPermissions(FALSE)
  ->execute();
$entities = [];
foreach ($result as $entity) {
  $entities[] = [
    'id' => $entity,
    'text' => $entity,
  ];
}
$vars = [
  'entities' => $entities,
  'operators' => \CRM_Core_DAO::acceptedSQLOperators(),
];
\Civi::resources()->addVars('api4', $vars);
return [
  'js' => [
    'ang/api4.js',
    'ang/api4/*.js',
    'ang/api4/*/*.js',
  ],
  'css' => [
    'css/explorer.css',
  ],
  'partials' => [
    'ang/api4',
  ],
  'requires' => ['crmUi', 'crmUtil', 'ngRoute', 'crmRouteBinder'],
];
