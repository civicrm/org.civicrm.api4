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

Tests
-----

Tests are located in the `tests` directory (surprise!)
To run the entire Api4 test suite go to the api4 extension directory and type `phpunit4` from the command line.
