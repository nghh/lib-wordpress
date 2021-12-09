# Nghh nghh/lib-wordpress

Library of PHP Utilities and Helper Classes for Wordpress

---

## Table of Contents

-   [WP Router](#wp-router)

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

HomeController::static()  
HomeController::blog()


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
