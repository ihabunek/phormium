Phormium Changelog
==================

0.9.0 / TBA
-----------

This release reorganizes the code substantially. The global state which was
littered all over the project (DB, Conf, Event) is now consolidated in one
place, the central `Phormium\Orm` class.

New features:

* `QuerySet`s are now iterable - this will fetch rows one by one instead of
  fetching all at once.
* Column and table names are properly quoted in SQL as expected by each
  database - double quotes for most and backticks for MySQL.

This causes several breaks to **backward compatibility**:

* **Requires PHP 5.6**
* `DB` class is deprecated in favour of `Orm::database()`
* `Event` class is deprecated in favour of `Orm::emitter()`
* `Conf` class has been removed, use `Orm::configure()`
* Made `Printer` methods non-static
* Made `ColumnFilter`, `RawFilter` and `CompositeFilter` immutable.
  * `CompositeFilter::add()` no longer exists because it mutated the filter, and
   has been replaced with `CompositeFilter::withAdded()` which returns a new
   `CompositeFilter` instance.
  * Made `ColumnFilter`, `RawFilter` and `CompositeFilter` properties private,
   available via getter methods which are named the same as the properties, e.g.
   `ColumnFilter::value()`
* Renamed existing `CompositeFilter` accessor methods:
  * `CompositeFilter::getOperation()` -> `CompositeFilter::operation()`
  * `CompositeFilter::getFilters()` -> `CompositeFilter::filters()`

Deprecated methods will emit a deprecation warning when used and will be removed
in the next release.

Bug fixes:

* Fix: `Connection->execute()` should return the number of affected rows (#12)
* Fix: Apply limit and offset to distinct queries (#18)
* Fix: Fix handling of boolean `false` (#24)

0.8.0 / 2015-05-07
------------------

* Added database attributes to configuration
* **BC BREAK**: Phormium will no longer force lowercase column names on
  database tables. This can still be done manually by setting the
  `PDO::ATTR_CASE:` attribute to `PDO::CASE_LOWER` in the configuration.

0.7.0 / 2014-12-05
------------------

* **BC BREAK**: Dropped support for PHP 5.3
* Added a shorthand for model relations with `Model->hasChildren()` and
  `Model->hasParent()`

0.6.2 / 2014-09-28
------------------

* Fixed an issue with shallow cloning which caused the same Filter instance to
  be used in cloned QuerySets.

0.6.1 / 2014-09-13
------------------

* Added `DB::disconnect()`, for disconnecting a single connection
* Added `DB::isConnected()`, for checking if a connection is up
* Added `DB::setConnection()`, useful for mocking
* Added `Connection->inTransaction()`

0.6 / 2014-04-10
----------------

* **BC BREAK**: Moved filter classes to `Phormium\Filter` namespace<br />
  Please update your references (e.g. `use Phormium\ColumnFilter` to
  `use Phormium\Filter\ColumnFilter`).
* **BC BREAK**: Removed logging and stats classes<br />
  These will be reimplemented using events and available as separate packages.
* Added `Model::all()`
* Added `Model->toYAML()`
* Added `Model::fromYAML()`
* Added raw filters
* Added events

* Modified `Model::fromJSON()` to take an optional `$strict` parameter

0.5 / 2013-12-10
----------------

* Added `Model->dump()`
* Added `Filter::col()`
* Added gathering of query stats

0.4 / 2013-07-17
----------------

* Added support for custom queries via `Connection` object
* Added `Model->merge()`
* Added `Model::find()`
* Added `Model::exists()`
* Modified `Model::get()` to accept the primary key as an array
* Modified `Model->save()` to be safer

0.3 / 2013-06-14
----------------

* Added `QuerySet::valuesFlat()`
* Added optional parameter `$allowEmpty` to `QuerySet->single()`

0.2 / 2013-05-10
----------------

* Added transactions
* Added `QuerySet->dump()`
* Added logging via [Apache log4php](http://logging.apache.org/log4php/)
* Added composite filters

0.1 / 2013-04-25
----------------

* Initial release
