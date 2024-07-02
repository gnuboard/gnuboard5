<?php

namespace API\Auth;

use API\Setting;

/**
 * JWT Token Manager
 */
class JwtTokenManager
{
    public Setting $setting;
    public string $algorithm;
    public string $type;

    public function __construct(Setting $setting, string $type = 'access')
    {
        $this->setting = $setting;
        $this->type = $type;
        $this->algorithm = 'HS256';
    }

    public function secret_key()
    {
        return $this->type == 'refresh'
            ? $this->setting->refresh_token_secret_key
            : $this->setting->access_token_secret_key;
    }

    public function expire_minutes()
    {
        return $this->type == 'refresh'
            ? $this->setting->refresh_token_expire_minutes
            : $this->setting->access_token_expire_minutes;
    }
}
