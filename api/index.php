<?php

/**
 * GnuBoard5 API with Slim Framework
 *
 * @package g5-api
 * @version 0.8.5
 * @link
 */

use API\EnvironmentConfig;
use API\Handlers\HttpErrorHandler;
use API\Handlers\ShutdownHandler;
use API\Middleware\IpCheckMiddleware;
use API\Middleware\JsonBodyParserMiddleware;
use API\ResponseEmitter\ResponseEmitter;
use API\Service\ConfigService;
use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Factory\ServerRequestCreatorFactory;

require __DIR__ . '/../vendor/autoload.php';

// 그누보드 로딩
$is_root = false;
$g5_path = g5_root_path($is_root, 1);
date_default_timezone_set('Asia/Seoul'); // 그누보드 5 기본 시간대.

require_once(dirname(__DIR__, 1) . '/config.php');   // 설정 파일
unset($g5_path);

include_once(__DIR__ . '/hook.lib.php');    // hook
include_once(dirname(__DIR__, 1) . '/lib/pbkdf2.compat.php');
include_once(G5_LIB_PATH . '/common.lib.php'); // 공통 라이브러리

if (!include(G5_DATA_PATH . '/' . G5_DBCONFIG_FILE)) {
    header('Content-Type: application/json');
    echo json_encode('그누보드가 설치되어있지 않습니다.');
    exit;
}

if (($GLOBALS['g5']['member_refresh_token_table'] ?? '') === '') {
    create_refresh_token_table();
}


// 응답 json 에 오류메시지를 같이 출력합니다.
// 실서버에서는 false 이어야 합니다.
// Should be set to false in production
$display_error_details = false;
if (G5_DEBUG) {
    $display_error_details = true;
}

// common.lib.php 의 함수 내 $config 를 사용하기 위해 설정합니다.
$config = ConfigService::getConfig();
/**
 * Instantiate App
 */

$container_builder = new ContainerBuilder();
$container_builder->useAutowiring(true);
$container = $container_builder->build();

AppFactory::setContainer($container);

/**
 * @var \Slim\App<\Psr\Container\ContainerInterface> $app
 */
$app = AppFactory::create();

//env 설정
$container->set(EnvironmentConfig::class, new EnvironmentConfig());

// PHP 서버 변수에서 값을 가져와 요청 객체를 생성합니다.
$server_request_creator = ServerRequestCreatorFactory::create();
$request = $server_request_creator->createServerRequestFromGlobals();

/**
 * Add Middleware
 */
// The routing middleware should be added earlier than the ErrorMiddleware
// Otherwise exceptions thrown from it will not be handled by the middleware
$app->addRoutingMiddleware();

$app->add(new JsonBodyParserMiddleware());
$app->add(new IpCheckMiddleware());

// Error Handler
$callable_resolver = $app->getCallableResolver();
$response_factory = $app->getResponseFactory();
$error_handler = new HttpErrorHandler($callable_resolver, $response_factory);

// Shutdown Handler
$shutdown_handler = new ShutdownHandler($request, $error_handler, $display_error_details);
register_shutdown_function($shutdown_handler);

// Error Middleware
$error_middleware = $app->addErrorMiddleware($display_error_details, true, true);
$error_middleware->setDefaultErrorHandler($error_handler);

/**
 * Add Routers
 */
// Set the base path for the API version
$api_path = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
$app->setBasePath($api_path);

$router_files = glob(__DIR__ . '/v1/Routers/*.php');
foreach ($router_files as $router_file) {
    include $router_file;
}

$plugin_router_files = glob(__DIR__ . '/Plugin/*/Routers/*.php');
foreach ($plugin_router_files as $router_file) {
    include $router_file;
}

$hooks_files = glob(__DIR__ . '/Hooks/**/event.php');
foreach ($hooks_files as $event) {
    include $event;
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
$response_emitter = new ResponseEmitter();
$response_emitter->emit($response);
