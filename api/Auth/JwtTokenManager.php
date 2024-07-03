<?php

namespace API\Auth;

use API\EnvironmentConfig;

/**
 * JWT Token Manager
 */
class JwtTokenManager
{
    public EnvironmentConfig $env_config;
    public string $algorithm;
    public string $type;

    public function __construct(EnvironmentConfig $env_config, string $type = 'access')
    {
        $this->env_config = $env_config;
        $this->type = $type;
        $this->algorithm = 'HS256';
    }

    public function secret_key()
    {
        return $this->type == 'refresh'
            ? $this->env_config->refresh_token_secret_key
            : $this->env_config->access_token_secret_key;
    }

    public function expire_minutes()
    {
        return $this->type == 'refresh'
            ? $this->env_config->refresh_token_expire_minutes
            : $this->env_config->access_token_expire_minutes;
    }
}
