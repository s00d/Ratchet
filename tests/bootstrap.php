<?php
    if (!function_exists('dd')) {
        function dd()
        {
            $args = func_get_args();
            call_user_func_array('dump', $args);
            die();
        }
    }

    if (!function_exists('d')) {
        function d()
        {
            $args = func_get_args();
            call_user_func_array('dump', $args);
        }
    }

    $loader = require __DIR__ . '/../vendor/autoload.php';

    $loader->addPsr4('Ratchet\\', __DIR__ . '/helpers/Ratchet');

