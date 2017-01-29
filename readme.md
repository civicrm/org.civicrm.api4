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

A series of **action classes** inherit from the base
[`Action`](Civi/API/V4/Action.php) class
([`GetActions`](Civi/API/V4/Action/GetActions.php),
[`GetFields`](Civi/API/V4/Action/GetFields.php),
[`Create`](Civi/API/V4/Action/Create.php),
[`Get`](Civi/API/V4/Action/Get.php),
[`Update`](Civi/API/V4/Action/Update.php),
[`Delete`](Civi/API/V4/Action/Delete.php)).

The `Action` class uses the magic [__call()](http://php.net/manual/en/language.oop5.overloading.php#object.call) method to `set`, `add` and `get` parameters. The base `execute()` method calls the core `civi_api_kernel` service `runRequest()` method. Action objects find their business access objects via [V3 API code](https://github.com/civicrm/civicrm-core/blob/master/api/v3/utils.php#L381).

Each action object has a `_run()` method that accepts a decorated [arrayobject](http://php.net/manual/en/class.arrayobject.php) ([`Result`](Civi/API/Result.php)) as a parameter.

All `action` classes accept an entity with their constructor and use the standard PHP [ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)
for metadata tracking with a custom
[`ReflectionUtils`](Civi/API/V4/ReflectionUtils.php) class to extract PHP comments. The metadata is available via `getParams()` and `getParamInfo()` methods. Each object is able to report its entitiy (`getEntity()`) and action verb (`getAction()`).

Each `action` object also has an `$options` property and a set of methods (`offsetExists()`, `offsetGet()`,  `offsetSet()` and `offsetUnset()`) that act as interface to a `thisArrayStorage` property.

The **get** action class uses a [`Api4SelectQuery`](Civi/API/Api4SelectQuery.php) object to `_run()` the query based on `select`, `where`, `orderBy`, `limit` and `offset` parameters.

[Result](Civi/API/Result.php),
[ActionObjectProvider](Civi/API/Provider/ActionObjectProvider.php),

Security
--------

Each `action` object has a `$checkPermissions` property. This is set to `FALSE` for calls from PHP but `TRUE` for calls from REST.


Tests
-----

Tests are located in the `tests` directory (surprise!)
To run the entire Api4 test suite go to the api4 extension directory and type `phpunit4` from the command line.
