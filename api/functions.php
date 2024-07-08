<?php

use API\Auth\JwtTokenManager;
use API\EnvironmentConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\UploadedFileInterface;

/**
 * API Response JSON
 * 
 * @param Response $response
 * @param array $data
 * @return Response
 */
function api_response_json(Response $response, array $data, int $status = 200)
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($json);
    $response = $response->withStatus($status);
    return $response->withAddedHeader('Content-Type', 'application/json');
}

/**
 * Select only the data you want and return it as an array
 * 
 * @param array $data
 * @param array $select
 * @return array
 */
function generate_select_array(array $data, array $select)
{
    $select_array = [];
    foreach ($select as $key) {
        $select_array[$key] = $data[$key];
    }
    return $select_array;
}

/**
 * Create a refresh token table
 */
function create_refresh_token_table()
{
    global $g5;

    if (isset($g5['member_refresh_token_table'])) {
        if (!sql_query(" DESCRIBE {$g5['member_refresh_token_table']} ", false)) {
            $sql = "CREATE TABLE IF NOT EXISTS `{$g5['member_refresh_token_table']}` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `mb_id` varchar(20) NOT NULL,
                    `refresh_token` text NOT NULL,
                    `expires_at` datetime NOT NULL,
                    `created_at` datetime NOT NULL,
                    `updated_at` datetime NOT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `refresh_token` (`refresh_token`) USING HASH,
                    KEY `ix_member_refresh_token_mb_id` (`mb_id`),
                    KEY `ix_member_refresh_token_id` (`id`)
                    ) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";
            sql_query($sql);
        }
    }
}


/**
 * Create JWT token
 */
function create_token(string $type, array $add_claim = array())
{
    $env_config = new EnvironmentConfig();
    $token_info = new JwtTokenManager($env_config, $type);

    $payload = [
        'iss' => $env_config->auth_issuer,
        'aud' => $env_config->auth_audience,
        'iat' => time(),
        'nbf' => time(),
        'exp' => time() + (60 * $token_info->expire_minutes()),
    ];
    $payload = array_merge($payload, $add_claim);
    return JWT::encode($payload, $token_info->secret_key(), $token_info->algorithm);
}

/**
 * Decode JWT token
 */
function decode_token(string $type, string $token, stdClass $headers = null)
{
    $token_info = new JwtTokenManager(new EnvironmentConfig(), $type);

    /**
     * You can add a leeway to account for when there is a clock skew times between
     * the signing and verifying servers. It is recommended that this leeway should
     * not be bigger than a few minutes.
     *
     * Source: http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html#nbfDef
     */
    // JWT::$leeway = 60; // $leeway in seconds
    return JWT::decode($token, new Key($token_info->secret_key(), $token_info->algorithm), $headers);
}


/**
 * Moves the uploaded file to the upload directory and assigns it a unique name
 * to avoid overwriting an existing uploaded file.
 *
 * @param string $directory The directory to which the file is moved
 * @param UploadedFileInterface $uploadedFile The file uploaded file to move
 *
 * @return string The filename of moved file
 */
function moveUploadedFile(string $directory, UploadedFileInterface $uploadedFile)
{
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

    // see http://php.net/manual/en/function.random-bytes.php
    $basename = bin2hex(random_bytes(8));
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}