# Nghh nghh/lib-wordpress

Library of PHP Utilities and Helper Classes for Wordpress

---

## Table of Contents

-   [WP Router](#wp-router)
-   [Config](#config)

---

## WP Router

A MVC Router the Wordpress way. 
This router works like the default wp template hierarchy.  
@see https://wphierarchy.com

Currently you should have the following controllers and methods:

SingularController::post()  
SingularController::page()  
SingularController::attachment()

ArchiveController::category()  
ArchiveController::postTag()  
ArchiveController::author()

ErrorController::error404()

IndexController::frontPage()  
IndexController::home()

```php
// in your theme e.g. functions.php
use Nghh\Lib\Wordpress\Utils\WP_Router;

// Optional args. If you use namespace, pass it like this
$args = [
    'namespace' => __NAMESPACE__ . '\Controllers\\',
    'env'       => 'local'
];

(new WP_Router($args))->registerHooks();

// A controller can look like this e.g. Controllers/SingularController.php
namespace Nghh\Theme\Controllers;

use Nghh\Theme\Models\Post;
use Nghh\Theme\Models\Page;
use Nghh\Theme\Models\Attachment;

class SingularController extends BaseController {

    public function page()
    {
        echo $this->view('pages.singular.page', ['Page' => new Page()]);
    }

    public function post()
    {
        echo $this->view('pages.singular.post', ['Post' => new Post()]);
    }

    public function attachment()
    {
        echo $this->view('pages.singular.attachment', ['Attachment' => new Attachment()]);
    }
}

```
There is a wp filter, where you can modify the controller and action

```php
add_filter('nghh/lib/router', 'nghh_modify_controller', 10, 2);

function nghh_modify_controller($controller, $wp_query)
{
    /**
     * $controller['name] = 'singular'
     * $controller['action] = 'post'
     * 
     * => calls SingularController::post()
    */

    return $controller;
}
```

---

## Config

Config class based on laravel config system.

```php
// functions.php
use Nghh\Lib\Wordpress\Utils\Config;

/**
 * Init config class in your functions.php
 * and pass full path to config dir and environemt
 */
Config::instance()->init('/path/to/config/dir', 'local');

// config/app.php
return [
    'version' => '0.0.1'
];

// You can then get config data with helper class function like this:
\Nghh\Lib\Wordpress\func\config('app.version');

// => 0.0.1
```