<?php

use API\OASGenerator;

require_once '../../../common.php';
require_once '../../../vendor/autoload.php';

$version = $_GET['version'] ?? null;
$open_api = new OASGenerator($version);
$open_api->generate();
$swagger_url = $open_api->getOASUrl();
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
    <redoc spec-url="<?php echo $swagger_url ?>"
        required-props-first=true
        >
    </redoc>
    <script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"> </script>
</body>

</html>