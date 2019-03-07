CiviCRM API Version 4
=====================

Welcome
-------

This is the latest version of the API (Application Programming Interface) for CiviCRM. If you are here because you're trying to install an extension that requires this, just install this and you're done!

If you are a developer, read on...

Using Api4
----------

Once installed you can navigate to **Support -> Developer -> Api4 Explorer** in the menu. This gives a live, interactive code generator in which you can build and test api calls:

![Screenshot](/images/ApiExplorer.png)

Output
------

The php binding returns an [arrayObject](http://php.net/manual/en/class.arrayobject.php). This gives immediate access to the results, plus allows returning additional metadata properties.


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

Notable changes from Version 3:
-------------------------------

* Instead of a single `$params` array, each api action has multiple methods to set various parameters.
* Output is an array with object properties rather than a nested array.
* Use the `Update` action to update an entity rather than `Create` with an id.
* Use `$result->indexBy('id');` rather than `sequential => 0`.
* `getSingle` is gone, use `$result->first()`.
* Custom fields are refered to by name rather than id. E.g. use `constituent_information.Most_Important_Issue` instead of `custom_4`.

Security
--------

Each `action` object has a `$checkPermissions` property. This always defaults to `TRUE`, and for calls from REST it cannot be disabled.

Architecture
------------

* An [**Entity**](Civi/Api4/Generic/AbstractEntity.php) is a class implementing one or more static methods (`get()`, `create()`, `delete()`, etc).
* Each static method constructs and returns an [**Action object**](Civi/Api4/Generic/AbstractAction.php).
* All actions extend the [AbstractAction class](Civi/Api4/Generic/AbstractAction.php). A number of other abstract action classes build on this, e.g. [AbstractBatchAction](Civi/Api4/Generic/AbstractBatchAction.php) is the base class for batch-process actions (`delete`, `update`, `replace`).
* Most entity classes correspond to a `CRM_Core_DAO` subclass. E.g. `Civi\Api4\Contact` corresponds to `CRM_Contact_DAO_Contact`.
* A set of **`DAO` action classes** (e.g. [DAOGetAction](Civi/Api4/Generic/DAOGetAction.php), [DAODeleteAction](Civi/Api4/Generic/DAODeleteAction.php)) exists to support DAO-based entities. [DAOGetAction](Civi/Api4/Generic/DAOGetAction.php) uses [`Api4SelectQuery`](Civi/API/Api4SelectQuery.php) to query the database.
* A set of **`Basic` action classes** (e.g. [BasicGetAction](Civi/Api4/Generic/BasicGetAction.php), [BasicBatchAction](Civi/Api4/Generic/BasicBatchAction.php)) exists to support many other use-cases, e.g. file-based entities.
* The base action `execute()` method calls the core [`civi_api_kernel`](https://github.com/civicrm/civicrm-core/blob/master/Civi/API/Kernel.php)
service `runRequest()` method which invokes hooks and then calls the `_run` method for that action.
* Each action object has a `_run()` method that accepts and updates a [`Result`](Civi/Api4/Generic/Result.php) object (which is an extended [ArrayObject](http://php.net/manual/en/class.arrayobject.php)).

Extending Api4
--------------

#### Modifying an existing entity/action:

To alter the behavior of an existing entiy action, use [hook_civicrm_apiWrappers](https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_apiWrappers).

#### Adding an action to an existing entity:

Create a class which extends a generic action class (see below). Give it the same namespace and file location as other actions for that entity. It will be picked up automatically by api4's getActions file scanner.

#### Adding a new api entity:

If your entity has a database table and DAO, simply add a class to the `Civi/Api4` directory of your extension. Give the file and class the same name as your entity, and extend the [DAOEntity class](Civi/Api4/Generic/DAOEntity.php).

For specialty apis, try the `BasicGet`, `BasicCreate`, `BasicUpdate`, `BasicBatch` and `BasicReplace` actions as in [this example](tests/phpunit/Mock/Api4/MockBasicEntity.php).

Tests
-----

Tests are located in the `tests` directory (surprise!)
To run the entire Api4 test suite go to the api4 extension directory and type `phpunit4` from the command line.
