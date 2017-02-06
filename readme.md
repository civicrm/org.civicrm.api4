CiviCRM API Version 4
=====================

Design Principles
-----------------

* **TDD** - tests come first; writing the tests will inform design decisions.
* **Clean** - leave all the legacy cruft in v3 and start with a clean slate.
* **Consistent** - uniformity between all entities as much as possible, minimize oddities.
* **Strict** - ditch the aliases, unique names, camelCase conversions, and alternate syntaxes. Params will only be accepted in one format.
* **OOP** - use classes in the \Civi namespace - minimize boilerplate via class inheritance/traits.
* **Discoverable** - params are self-documenting through fluent style and api reflection; no undocumented params
* **Doable** - prioritize new features based on impact and keep scope proportionate to developer capacity.

Input
-----

$params array will be organized into categories, expanding on the "options" convention in v3:

```php
// fluent style
\Civi\Api4\Contact::get()
  ->setSelect(['id', 'sort_name'])
  ->addWhere('contact_type', '=', 'Individual')
  ->addOrderBy('sort_name', 'DESC')
  ->setCheckPermissions(TRUE)
  ->execute();
 
// traditional style
civicrm_api4('Contact', 'get', array(
  'select' => array('id', 'sort_name'),
  'where' => array('contact_type' => 'Individual'),
  'orderBy' => array('sort_name' => 'DESC'),
  'checkPermissions' => TRUE,
));
```

Output
------

The php binding returns an arrayObject. This gives immediate access to the results, plus allows returning additional properties.

```php
$result = \Civi\Api4\Contact::get()->execute();
 
// you can loop through the results directly
foreach ($result as $contact) {}
 
// you can just grab the first one
$contact1 = $result->first();
 
// reindex results on-the-fly (replacement for sequential=1 in v3)
$result->indexBy('id');
 
// or fetch some metadata about the call
$entity = $result->entity; // "Contact"
$fields = $result->fields; // contact getfields
```

We can do the something very similar in javascript thanks to js arrays also being objects:

```javascript
CRM.api4('Contact', 'get', params).done(function(result) {
  // you can loop through the results
  result.forEach(function(contact, n) {});
 
  // you can just grab the first one
  var contact1 = result[0];
 
  // or fetch some metadata about the call
  var entity = result.entity; // "Contact"
});
``` 

Feature Wishlist
----------------

### Get Action
* `OR` as well as `AND` in select queries.
* Ability to add a field to a query more than once e.g. `sort_name LIKE 'bob' OR sort_name LIKE 'robert'`.
* Joins across all FKs and pseudo FKs.

### Delete Action
* Delete multiple items at once.
* Search by any field, not just ID

### Error Handling
* Ability to simulate an api call
* Report on all errors, not just the first one to be thrown

The JIRA issue number is [CRM-17867](https://issues.civicrm.org/jira/browse/CRM-17867)

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

The `Action` class uses the magic [__call()](http://php.net/manual/en/language.oop5.overloading.php#object.call) method to `set`, `add` and `get` parameters.
The base action `execute()` method calls the core [`civi_api_kernel`](https://github.com/civicrm/civicrm-core/blob/master/Civi/API/Kernel.php)
service `runRequest()` method. Action objects find their business access objects via [V3 API code](https://github.com/civicrm/civicrm-core/blob/master/api/v3/utils.php#L381).

Each action object has a `_run()` method that accepts a decorated [arrayobject](http://php.net/manual/en/class.arrayobject.php) ([`Result`](Civi/API/Result.php)) as a parameter and is accessed by the action's `execute()` method.

All `action` classes accept an entity with their constructor and use the standard PHP [ReflectionClass](http://php.net/manual/en/class.reflectionclass.php)
for metadata tracking with a custom
[`ReflectionUtils`](Civi/API/V4/ReflectionUtils.php) class to extract PHP comments. The metadata is available via `getParams()` and `getParamInfo()` methods. Each action object is able to report its entitiy class name (`getEntity()`) and action verb (`getAction()`).

Each `action` object also has an `$options` property and a set of methods (`offsetExists()`, `offsetGet()`,  `offsetSet()` and `offsetUnset()`) that act as interface to a `thisArrayStorage` property.

The **get** action class uses a [`Api4SelectQuery`](Civi/API/Api4SelectQuery.php) object
(based on the core
[SelectQuery](https://github.com/civicrm/civicrm-core/blob/master/Civi/API/SelectQuery.php)
object which uses
[the V3 API utilities](https://github.com/civicrm/civicrm-core/blob/master/api/v3/utils.php)
and the
[CRM_Utils_SQL_Select](https://github.com/civicrm/civicrm-core/blob/master/CRM/Utils/SQL/Select.php) class)
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

The
  [`API kernel`](https://github.com/civicrm/civicrm-core/blob/master/Civi/API/Kernel.php)
, shared with V3 of the API, is constructed with a [Symfony event dispatcher](http://api.symfony.com/3.1/Symfony/Component/EventDispatcher.html)
and a collection of `apiProviders`.

Security
--------

Each `action` object has a `$checkPermissions` property. This is set to `FALSE` for calls from PHP but `TRUE` for calls from REST.


Tests
-----

Tests are located in the `tests` directory (surprise!)
To run the entire Api4 test suite go to the api4 extension directory and type `phpunit4` from the command line.
