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
use API\Middleware\JsonBodyParserMiddleware;
use API\ResponseEmitter\ResponseEmitter;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

//gnuboard 로딩
$g5_path = g5_root_path();
require_once(dirname(__DIR__, 1) . '/config.php');   // 설정 파일
unset($g5_path);

include_once(G5_LIB_PATH.'/hook.lib.php');    // hook 함수 파일
include_once (G5_LIB_PATH.'/common.lib.php'); // 공통 라이브러리 // @todo 정리후 삭제대상

$dbconfig_file = G5_DATA_PATH.'/'.G5_DBCONFIG_FILE;
if (file_exists($dbconfig_file)) {
    include_once($dbconfig_file);
}
//-------------------------
// Create refresh token table
create_refresh_token_table();

// Set error display settings
// - Should be set to false in production
$displayErrorDetails = true;

//@todo 임시 전역변수 정리후 삭제대상
$config = get_gnuconfig();
/**
 * Instantiate App
 */
$container = new Container();
AppFactory::setContainer($container);
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

// Include all Routers for the requested API version.
$routerFiles = glob(__DIR__ . "/{$api_version}/Routers/*.php");
foreach ($routerFiles as $routerFile) {
    include_once $routerFile;
}

/**
 * Route Cache (Optional)
 * To generate the route cache data, you need to set the file to one that does not exist in a writable directory.
 * After the file is generated on first run, only read permissions for the file are required.
 *
 * You may need to generate this file in a development environment and committing it to your project before deploying
 * if you don't have write permissions for the directory where the cache file resides on the server it is being deployed to
 */
/*
$cache_dir = G5_DATA_PATH . "/cache/API";
if (!is_dir($cache_dir)) {
    @mkdir($cache_dir, G5_DIR_PERMISSION);
}
$routeCollector = $app->getRouteCollector();
$routeCollector->setCacheFile("{$cache_dir}/router-cache.php");
*/

// Run App & Custom Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
