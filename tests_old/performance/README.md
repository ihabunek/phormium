Phormium performance test suite
===============================

Create a test database called `phtest`:

```
$ createdb -U postgres phtest
```

Run the test:

```
$ php performance.php
```

Results will be saved in JSON in results folder.
