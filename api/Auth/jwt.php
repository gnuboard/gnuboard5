<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * JWT Token Manager
 */
class JwtTokenManager
{
    public string $algorithm;
    public string $access_token_secret_key;
    public string $refresh_token_secret_key;
    public int $access_token_expire_minutes;
    public int $refresh_token_expire_days;
    public string $type;

    public function __construct(string $type = 'access')
    {
        $this->algorithm = 'HS256';
        $this->access_token_secret_key = ACCESS_TOKEN_SECRET_KEY ?? 'secret_key';
        $this->refresh_token_secret_key = REFRESH_TOKEN_SECRET_KEY ?? 'secret_key';
        $this->access_token_expire_minutes = ACCESS_TOKEN_EXPIRE_MINUTES ?? 30;
        $this->refresh_token_expire_days = REFRESH_TOKEN_EXPIRE_MINUTES ?? 60 * 24 * 14;
        $this->type = $type;
    }

    public function secret_key()
    {
        if ($this->type == 'refresh') {
            return $this->refresh_token_secret_key;
        }
        return $this->access_token_secret_key;
    }

    public function expire_minutes()
    {
        if ($this == 'refresh') {
            return $this->refresh_token_expire_days;
        }
        return $this->access_token_expire_minutes;
    }
}

/**
 * Create JWT token
 */
function create_token(string $type, array $add_claim = array())
{
    $token_info = new JwtTokenManager($type);

    $payload = [
        'iss' => AUTH_ISSUER,
        'aud' => AUTH_AUDIENCE,
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
    $token_info = new JwtTokenManager($type);

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
