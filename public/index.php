<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Auto-detect deployment structure and set appropriate paths
$basePath = __DIR__;
$autoloadPath = null;
$bootstrapPath = null;
$maintenancePath = null;

// Try different deployment structures
if (file_exists($basePath.'/../laravel/vendor/autoload.php')) {
    // Production structure: public_html + laravel folders
    $autoloadPath = $basePath.'/../laravel/vendor/autoload.php';
    $bootstrapPath = $basePath.'/../laravel/bootstrap/app.php';
    $maintenancePath = $basePath.'/../laravel/storage/framework/maintenance.php';
} elseif (file_exists($basePath.'/application/vendor/autoload.php')) {
    // Flat deployment: public files + application folder
    $autoloadPath = $basePath.'/application/vendor/autoload.php';
    $bootstrapPath = $basePath.'/application/bootstrap/app.php';
    $maintenancePath = $basePath.'/application/storage/framework/maintenance.php';
} elseif (file_exists($basePath.'/../vendor/autoload.php')) {
    // Standard Laravel structure
    $autoloadPath = $basePath.'/../vendor/autoload.php';
    $bootstrapPath = $basePath.'/../bootstrap/app.php';
    $maintenancePath = $basePath.'/../storage/framework/maintenance.php';
} else {
    // Fallback error
    http_response_code(500);
    echo '<h1>Application Error</h1>';
    echo '<p>Could not locate Laravel application files. Please check your deployment structure.</p>';
    echo '<p>Searched for autoload.php in:</p>';
    echo '<ul>';
    echo '<li>' . $basePath . '/../laravel/vendor/autoload.php</li>';
    echo '<li>' . $basePath . '/application/vendor/autoload.php</li>';
    echo '<li>' . $basePath . '/../vendor/autoload.php</li>';
    echo '</ul>';
    exit(1);
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenancePath)) {
    require $maintenancePath;
}

// Register the Composer autoloader...
require $autoloadPath;

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $bootstrapPath;

$app->handleRequest(Request::capture());
