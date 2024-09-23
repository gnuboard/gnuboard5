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

    /**
     * @param string $type
     * @return string
     */
    public function secretKey(string $type = 'access'): string
    {
        return $type === 'refresh'
            ? $this->env_config->refresh_token_secret_key
            : $this->env_config->access_token_secret_key;
    }

    /**
     * @param string $type
     * @return int
     */
    public function expireMinutes(string $type = 'access')
    {
        return $type === 'refresh'
            ? (int)$this->env_config->refresh_token_expire_minutes
            : $this->env_config->access_token_expire_minutes;
    }

    /**
     * JWT token 생성
     * @param string $type
     * @param array $add_claim
     * @return string
     */
    public function createToken(string $type, array $add_claim = []): string
    {
        $payload = [
            'iss' => $this->env_config->auth_issuer,
            'aud' => $this->env_config->auth_audience,
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + (60 * $this->expireMinutes($type)),
        ];
        $payload = array_merge($payload, $add_claim);
        return JWT::encode($payload, $this->secretKey($type), self::ALGORITHM);
    }

    /**
     * 만료시간을 지정해서 JWT 토큰을 생성
     * @param string $type
     * @param array $add_claim
     * @param int $time unix time
     * @return string
     */
    public function createTokenWithTime(string $type, array $add_claim, int $time): string
    {
        $payload = [
            'iss' => $this->env_config->auth_issuer,
            'aud' => $this->env_config->auth_audience,
            'iat' => time(),
            'nbf' => time(),
            'exp' => $time,
        ];
        $payload = array_merge($payload, $add_claim);
        return JWT::encode($payload, $this->secretKey($type), self::ALGORITHM);
    }

    /**
     * @param string $type
     * @param string $token
     * @param stdClass|null $headers
     * @throws \Firebase\JWT\BeforeValidException
     * @throws \Firebase\JWT\ExpiredException
     * @throws \Firebase\JWT\SignatureInvalidException
     * @throws \Firebase\JWT\InvalidArgumentException
     * @throws \UnexpectedValueException
     * @throws \DomainException
     * @return stdClass
     */
    public function decodeToken(string $type, string $token, ?stdClass $headers = null): stdClass
    {
        // JWT::$leeway = 60; // $leeway in seconds, if needed
        return JWT::decode($token, new Key($this->secretKey($type), self::ALGORITHM), $headers);
    }
}
