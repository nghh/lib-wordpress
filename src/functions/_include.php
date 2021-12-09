<?php

// Require all functions
foreach (glob(__DIR__ . '/*.inc.php') as $file) {
    require_once $file;
}