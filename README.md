# Nghh Wordpress

Library of PHP Utilities and Helper Classes for Wordpress

---

## Table of Contents

-   [Notice](#notice)

---

## Notice

PHP Class to create and show notices in admin area of wordpress

```php

use Nghh\Lib\Wordpress\Notice; // Using Class
use function Nghh\Lib\Wordpress\Func\notice; // Using helper Function

// Init Admin Notices with args
$args = [
    'transient_name' => '_ng_admin_notices', // (optional)
    'date_format' => '', // date format (optional)
    'template' => '', // Html string (optional)
];

// Init Admin Notices in functions.php
Notice::instance($args)->registerHooks();

// Create notices in your app
Notice::instance()->info('Message', $dismisable = true);
Notice::instance()->warn('Message', $dismisable = true);
Notice::instance()->error('Message', $dismisable = true);
Notice::instance()->success('Message', $dismisable = true);

// or by using helper function
notice('Message'); // same as
notice()->info('Message', true);

notice()->warn('Message', $dismisable = true);
notice()->error('Message', $dismisable = true);
notice()->success('Message', $dismisable = true);

```
