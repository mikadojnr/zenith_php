<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Core\Application;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Create and run application
$app = new Application();
$app->run();