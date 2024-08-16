<?php

namespace API\Auth;

use API\EnvironmentConfig;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use stdClass;

/**
 * JWT Token Manager
 */
class JwtTokenManager
{
    public const ALGORITHM = 'HS256';
    private EnvironmentConfig $env_config;

    public function __construct(EnvironmentConfig $env_config)
    {
        $this->env_config = $env_config;
    }

    public function secret_key(string $type = 'access'): string
    {
        return $type === 'refresh'
            ? $this->env_config->refresh_token_secret_key
            : $this->env_config->access_token_secret_key;
    }

    public function expire_minutes(string $type = 'access'): int
    {
        return $type === 'refresh'
            ? $this->env_config->refresh_token_expire_minutes
            : $this->env_config->access_token_expire_minutes;
    }

    /**
     * Create JWT token
     */
    public function create_token(string $type, array $add_claim = []): string
    {
        $payload = [
            'iss' => $this->env_config->auth_issuer,
            'aud' => $this->env_config->auth_audience,
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + (60 * $this->expire_minutes($type)),
        ];
        $payload = array_merge($payload, $add_claim);
        return JWT::encode($payload, $this->secret_key($type), self::ALGORITHM);
    }

    /**
     * Decode JWT token
     */
    public function decode_token(string $type, string $token, ?stdClass $headers = null): stdClass
    {
        // JWT::$leeway = 60; // $leeway in seconds, if needed
        return JWT::decode($token, new Key($this->secret_key($type), self::ALGORITHM), $headers);
    }
}
