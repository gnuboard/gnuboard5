<?php

use API\OASGenerator;

require_once '../../../vendor/autoload.php';
require_once '../../../common.php';

$version = $_GET['version'] ?? null;
$open_api = new OASGenerator($version);
$open_api->generate();
$swagger_url = $open_api->getOASUrl();
?>
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
        const swagger_url = "<?php echo $swagger_url ?>";
    </script>
</head>

<body>
    <div id="swagger-ui"></div>
    <script src="./swagger-ui-bundle.js" charset="UTF-8"> </script>
    <script src="./swagger-ui-standalone-preset.js" charset="UTF-8"> </script>
    <script src="./swagger-initializer.js" charset="UTF-8"> </script>
</body>

</html>