Install
=======

Using Composer
--------------
Install [Composer](http://getcomposer.org/download/).

Create a `composer.json` file:

```javascript
{
    "require": {
        "ihabunek/phormium": "dev-master"
    }
}
```

Start the Composer install procedure:

    php composer.phar install

Phormium will be installed in `vendor/ihabunek/phormium`.

In your code, include `vendor/autoload.php` to get access to Phormium classes.

```php
require 'vendor/autoload.php';
```

### Clone from Github

Clone the source into `phormium` directory:

    git clone https://github.com/ihabunek/phormium.git

In your code, include and register the Phormium autoloader:

```php
require 'phormium/Phormium/Autoloader.php';
\Phormium\Autoloader::register();
```
