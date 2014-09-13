Phormium Changelog
==================

0.6.1 / TBA
-----------

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
