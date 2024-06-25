<?php
/**
 * GnuBoard5 API with Slim Framework
 * 
 * @package g5-api
 * @version 1.0.0
 * @link
 */
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/_common.php';

/**
 * Instantiate App
 *
 * In order for the factory to work you need to ensure you have installed
 * a supported PSR-7 implementation of your choice e.g.: Slim PSR-7 and a supported
 * ServerRequest creator (included with Slim PSR-7)
 */
$app = AppFactory::create();

/**
 * Set base path
 * 
 * This is used to set the base path for the API version.
 */
$api_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$api_version = explode("/", str_replace($api_path, '', $_SERVER['REQUEST_URI']))[1];
$app->setBasePath($api_path . '/' . $api_version);

// Set middleware
require_once __DIR__ . '/middleware.php';

// Include all routers for the requested API version.
$routerFiles = glob(__DIR__ . "/{$api_version}/routers/*.php");
foreach ($routerFiles as $routerFile) {
    include $routerFile;
}

// Run app
$app->run();