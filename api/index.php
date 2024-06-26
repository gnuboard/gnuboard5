<?php
/**
 * GnuBoard5 API with Slim Framework
 * 
 * @package g5-api
 * @version 1.0.0
 * @link
 */

use API\Handlers\HttpErrorHandler;
use API\Handlers\ShutdownHandler;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/_common.php';
require __DIR__ . '/functions.php';
require __DIR__ . '/middleware.php';
require __DIR__ . '/Handlers/HttpErrorHandler.php';
require __DIR__ . '/Handlers/ShutdownHandler.php';

// Set error display settings
// - Should be set to false in production
$displayErrorDetails = true;

/**
 * Instantiate App
 */
$app = AppFactory::create();

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

/**
 * Add Middleware
 */
// The routing middleware should be added earlier than the ErrorMiddleware
// Otherwise exceptions thrown from it will not be handled by the middleware
$app->addRoutingMiddleware();

// Add JSON Body Parser Middleware
$app->add(new JsonBodyParserMiddleware());

// Create Error Handler
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Create Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Add Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/**
 * Add Routers
 */
// Set the base path for the API version
$api_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$api_version = explode("/", str_replace($api_path, '', $_SERVER['REQUEST_URI']))[1];
$app->setBasePath($api_path . '/' . $api_version);

// Include all routers for the requested API version.
$routerFiles = glob(__DIR__ . "/{$api_version}/routers/*.php");
foreach ($routerFiles as $routerFile) {
    include $routerFile;
}

// Run app
$app->run();
