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
$api_path = str_replace('/docs/swagger_ui/index.php', '', $_SERVER['SCRIPT_NAME']);

?>
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="./swagger-ui.css" />
    <link rel="stylesheet" type="text/css" href="index.css" />
    <link rel="icon" type="image/png" href="./favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="./favicon-16x16.png" sizes="16x16" />
    <script>
        const api_version = "<?php echo $env_config->api_version ?>";
        const api_path = "<?php echo $api_path ?>";
    </script>
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="./swagger-ui-bundle.js" charset="UTF-8"> </script>
    <script src="./swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
    <script src="./swagger-initializer.js" charset="UTF-8"> </script>
</body>

</html>