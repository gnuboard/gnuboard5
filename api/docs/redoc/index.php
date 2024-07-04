<?php

/**
 * Swagger UI Test
 */

use OpenApi\Generator;
use API\EnvironmentConfig;

require_once '../../../vendor/autoload.php';
include_once '../../../common.php';

/**
 * .env 설정 값으로 OpenAPI 문서를 생성
 * TODO: 별도의 코드로 분리 필요
 */
$env_config = new EnvironmentConfig();
$api_dir = "../../../api/{$env_config->api_version}";
$openapi = Generator::scan([$api_dir]);
file_put_contents("{$api_dir}/openapi.yaml", $openapi->toYaml());



/**
 * API 경로
 * TODO: 비슷한 코드가 api/index.php에 있음. 중복 제거 필요
 */
$api_path = str_replace('/docs/redoc/index.php', '', $_SERVER['SCRIPT_NAME']);

$swagger_ui_path = $api_path . "/" . $env_config->api_version . "/openapi.yaml";
?>
<!DOCTYPE html>
<html>

<head>
    <title>Redoc</title>
    <!-- needed for adaptive design -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700|Roboto:300,400,700" rel="stylesheet">

    <!--
    Redoc doesn't change outer page styles
    -->
    <style>
        body {
            margin: 0;
            padding: 0;
        }
    </style>
</head>

<body>
    <redoc spec-url="<?php echo $swagger_ui_path ?>"
        required-props-first=true
        >
    </redoc>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"> </script>
</body>

</html>