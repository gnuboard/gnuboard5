<?php

namespace API\Auth;

/**
 * JWT Token Manager
 */
class JwtTokenManager
{
    public string $algorithm;
    public string $access_token_secret_key;
    public string $refresh_token_secret_key;
    public int $access_token_expire_minutes;
    public int $refresh_token_expire_minutes;
    public string $type;

    public function __construct(string $type = 'access')
    {
        $this->algorithm = 'HS256';
        $this->access_token_secret_key = ACCESS_TOKEN_SECRET_KEY ?? 'secret_key';
        $this->refresh_token_secret_key = REFRESH_TOKEN_SECRET_KEY ?? 'secret_key';
        $this->access_token_expire_minutes = ACCESS_TOKEN_EXPIRE_MINUTES ?? 30;
        $this->refresh_token_expire_minutes = REFRESH_TOKEN_EXPIRE_MINUTES ?? 60 * 24 * 14;
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
        if ($this->type == 'refresh') {
            return $this->refresh_token_expire_minutes;
        }
        return $this->access_token_expire_minutes;
    }
}
