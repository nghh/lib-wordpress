<?php
// Require all functions from functions dir
foreach (glob(__DIR__ . '/functions/*.inc.php') as $file) {
    require $file;
}
