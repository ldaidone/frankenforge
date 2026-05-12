<?php
/**
 * FrankenForge — frankenforge/kernel
 *
 * @author    Leo Daidone <leo.daidone@gmail.com>
 * @copyright 2026
 * @license   Apache 2.0
 */

declare(strict_types=1);

/**
 * Front controller for FrankenForge application.
 */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once __DIR__ . '/../vendor/autoload.php';

// Load .env file if present (skip in production where env vars are set directly)
if (is_file(__DIR__ . '/../.env')) {
    Dotenv\Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
}

use FrankenForge\Core\Container\Container;

// Build the application container ONCE (resident in worker memory)
$container = new Container();
$registerServices = require __DIR__ . '/../config/services.php';
$registerServices($container);

// FrankenPHP worker mode: loop to handle multiple requests per process
if (function_exists('frankenphp_handle_request')) {
    while (frankenphp_handle_request(function () use ($container): void {
        try {
            // Refresh session for worker mode
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_write_close();
            }
            session_start([
                'cookie_httponly' => true,
                'cookie_samesite' => 'Lax',
            ]);

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
