<?php

/**
 * GnuBoard5 API with Slim Framework
 *
 * @package g5-api
 * @version 0.0.2
 * @link
 */

use API\EnvironmentConfig;
use API\Handlers\HttpErrorHandler;
use API\Handlers\ShutdownHandler;
use API\Middleware\JsonBodyParserMiddleware;
use API\ResponseEmitter\ResponseEmitter;
use API\Service\ConfigService;
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

// 그누보드 로딩
$is_root = false;
$g5_path = g5_root_path($is_root, 1);
date_default_timezone_set('Asia/Seoul'); // 그누보드 5 기본 시간대.

require_once(dirname(__DIR__, 1) . '/config.php');   // 설정 파일
unset($g5_path);

include_once(G5_LIB_PATH . '/hook.lib.php');    // hook 함수 파일
include_once(G5_LIB_PATH . '/common.lib.php'); // 공통 라이브러리 // @todo 정리후 삭제대상

if (!include(G5_DATA_PATH . '/' . G5_DBCONFIG_FILE)) {
    header('Content-Type: application/json');
    echo json_encode('그누보드가 설치되어있지 않습니다.');
    exit;
}

create_refresh_token_table();

// 응답 json 에 오류메시지를 같이 출력합니다.
// 실서버에서는 false 이어야 합니다.
// Should be set to false in production
$displayErrorDetails = false;
if (G5_DEBUG) {
    $displayErrorDetails = true;
}

//@todo 임시 전역변수 정리후 삭제대상
$config = ConfigService::getConfig();
/**
 * Instantiate App
 */
$container = new Container();
AppFactory::setContainer($container);
$app = AppFactory::create();

//env 설정
$container->set(EnvironmentConfig::class, new EnvironmentConfig());

// PHP 서버 변수에서 값을 가져와 요청 객체를 생성합니다.
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

/**
 * Add Middleware
 */
// The routing middleware should be added earlier than the ErrorMiddleware
// Otherwise exceptions thrown from it will not be handled by the middleware
$app->addRoutingMiddleware();
//$app->add(new CorsMiddleware());

$app->add(new JsonBodyParserMiddleware());

// Error Handler
$callableResolver = $app->getCallableResolver();
$responseFactory = $app->getResponseFactory();
$errorHandler = new HttpErrorHandler($callableResolver, $responseFactory);

// Shutdown Handler
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Error Middleware
$errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, true, true);
$errorMiddleware->setDefaultErrorHandler($errorHandler);

/**
 * Add Routers
 */
// Set the base path for the API version
$api_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$app->setBasePath($api_path);

$router_files = glob(__DIR__ . "/v1/Routers/*.php");
foreach ($router_files as $router_file) {
    include $router_file;
}

$plugin_router_files = glob(__DIR__ . '/Plugin/*/Routers/*.php');
foreach ($plugin_router_files as $router_file) {
    require $router_file;
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
