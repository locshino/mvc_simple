<?php
/** @var Phroute\Phroute\RouteCollector $route */

use App\Controllers\HomeController;
use App\Core\Asset\Asset;

$route->get('/', [HomeController::class, 'index']);

// Route for serving images ( DON'T REMOVE THIS LINE )
$route->get('/assets/img/{fileName}', function ($fileName) {
  Asset::downloadImage($fileName);
});