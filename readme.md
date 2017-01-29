CiviCRM API Version 4
=====================

Spec
----

https://wiki.civicrm.org/confluence/display/CRM/API+v4+Spec

Requirements
------------

CiviCRM version 4.7.13+

Discussion
----------

Use the mailing list http://lists.civicrm.org/lists/info/civicrm-api

Contributing
------------

Create a pull-request, or, for frequent contributors, we can give you direct push access to this repo.

Architecture
------------

The API use embedded magic functions to extend generic PHP OOP approaches and provide easy to use naming, autoloading and self-documentation.
In order for the magic to work, coders extending the API need to use consistent paths, class names and class name-spacing.

API V4 **entities** have both general and specific single class actions.
Specific single-class action class are named `\Civi\API\V4\Entity\[$entity]\[ucfirst($action)]`
and generic actions `\Civi\API\V4\Action\[ucfirst($action)]`.
Although called as static entity class methods, each action is implemented as its own class courtesy of some magic in
[`Civi\API\V4\Entity::__callStatic()`](Civi\API\V4\Entity.php).

A series of **action classes** inherit from the base
[`Action`](Civi/API/V4/Action.php) class
([`GetActions`](Civi/API/V4/Action/GetActions.php),
[`GetFields`](Civi/API/V4/Action/GetFields.php),
[`Create`](Civi/API/V4/Action/Create.php),
[`Get`](Civi/API/V4/Action/Get.php),
[`Update`](Civi/API/V4/Action/Update.php),
[`Delete`](Civi/API/V4/Action/Delete.php)).

The `Action` class uses the magic [__call()](http://php.net/manual/en/language.oop5.overloading.php#object.call) method to `set`, `add` and `get` parameters. The base `execute()` method calls the core `civi_api_kernel` service `runRequest()` method. Action objects find their business access objects via [V3 API code](https://github.com/civicrm/civicrm-core/blob/master/api/v3/utils.php#L381).

Each action object has a `_run()` method that accepts a decorated [arrayobject](http://php.net/manual/en/class.arrayobject.php) ([`Result`](Civi/API/Result.php)) as a parameter and is accessed by the action's `execute()` method.

All `action` classes accept an entity with their constructor and use the standard PHP [ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)
for metadata tracking with a custom
[`ReflectionUtils`](Civi/API/V4/ReflectionUtils.php) class to extract PHP comments. The metadata is available via `getParams()` and `getParamInfo()` methods. Each action object is able to report its entitiy class name (`getEntity()`) and action verb (`getAction()`).

Each `action` object also has an `$options` property and a set of methods (`offsetExists()`, `offsetGet()`,  `offsetSet()` and `offsetUnset()`) that act as interface to a `thisArrayStorage` property.

The **get** action class uses a [`Api4SelectQuery`](Civi/API/Api4SelectQuery.php) object
(based on the core
[SelectQuery](https://github.com/civicrm/civicrm-core/blob/master/Civi/API/SelectQuery.php) object)
to execute the query based on the action's `select`, `where`, `orderBy`, `limit` and `offset` parameters.

The **[`GetActions`](Civi/API/V4/Action/GetActions.php) action** globs the
`Civi/API/V4/Entity/[ENTITY_NAME]` subdirectories of the
`[get_include_path()](http://php.net/manual/en/function.get-include-path.php)`
then the `Civi/API/V4/Action` subdirectories for generic actions. In the event
of duplicate actions, only the first is reported.

The **[`GetFields`](Civi/API/V4/Action/GetFields.php) action** uses the `[BAO]->fields()` method.

todo: [ActionObjectProvider](Civi/API/Provider/ActionObjectProvider.php),
  implements the
Symfony [EventSubscriberInterface](http://symfony.com/doc/current/components/event_dispatcher.html#using-event-subscribers)
(the single `getSubscribedEvents()` method) and
the CiviCRM [ProviderInterface](https://github.com/civicrm/civicrm-core/blob/master/Civi/API/Provider/ProviderInterface.php) interfaces
(`invoke($apiRequest)`, `getEntityNames($version)` and `getActionNames($version, $entity)`).

Security
--------

Each `action` object has a `$checkPermissions` property. This is set to `FALSE` for calls from PHP but `TRUE` for calls from REST.


Tests
-----

Tests are located in the `tests` directory (surprise!)
To run the entire Api4 test suite go to the api4 extension directory and type `phpunit4` from the command line.
