<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

use FrankenForge\Core\Container\Container;

// Build the application container ONCE (resident in worker memory)
$container = new Container();
$registerServices = require __DIR__ . '/../config/services.php';
$registerServices($container);

// FrankenPHP worker mode: loop to handle multiple requests per process
if (function_exists('frankenphp_handle_request')) {
    while (frankenphp_handle_request(function () use ($container): void {
        try {
            $container->get('router')->dispatch();
        } catch (\Throwable $e) {
            $container->get('errorHandler')->serverError($e)->send();
        }
    })) {
        // continue
    }
} else {
    // Standard mode (testing, PHP-FPM): single request
    try {
        $container->get('router')->dispatch();
    } catch (\Throwable $e) {
        $container->get('errorHandler')->serverError($e)->send();
    }
}