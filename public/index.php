<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use FrankenForge\Core\Container\Container;

$container = new Container();
$registerServices = require __DIR__ . '/../config/services.php';
$registerServices($container);
$container->get('router')->dispatch();