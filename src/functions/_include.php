<?php
namespace Nghh\Lib\Wordpress\func;

// Require all functions
foreach (glob(__DIR__ . '/*.inc.php') as $file) {
    require $file;
}