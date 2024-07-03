<?php

namespace API;

use Dotenv\Dotenv;
use Exception;

class EnvironmentConfig
{
    public const DEFAULT_API_VERSION = 'v1';
    public const DEFAULT_ACCESS_TOKEN_SECRET_KEY = 'secret_key';
    public const DEFAULT_ACCESS_TOKEN_EXPIRE_MINUTES = 30;
    public const DEFAULT_REFRESH_TOKEN_SECRET_KEY = 'secret_key';
    public const DEFAULT_REFRESH_TOKEN_EXPIRE_MINUTES = 60 * 24 * 14;
    public const DEFAULT_AUTH_ISSUER = '';
    public const DEFAULT_AUTH_AUDIENCE = '';

    public string $api_version;
    public string $access_token_secret_key;
    public int $access_token_expire_minutes;
    public string $refresh_token_secret_key;
    public int $refresh_token_expire_minutes;
    public string $auth_issuer;
    public string $auth_audience;

    private string $env_file = '.env';
    private Dotenv $dotenv;

    public function __construct()
    {
        $this->createEnvFileIfNotExists();

        // Load environment variables
        $this->dotenv = Dotenv::createImmutable(G5_PATH);
        $this->dotenv->load();

        $this->api_version = $_ENV['API_VERSION'] ?? self::DEFAULT_API_VERSION;
        $this->access_token_secret_key = $_ENV['ACCESS_TOKEN_SECRET_KEY'] ?? self::DEFAULT_ACCESS_TOKEN_SECRET_KEY;
        $this->access_token_expire_minutes = isset($_ENV['ACCESS_TOKEN_EXPIRE_MINUTES']) ? (int)$_ENV['ACCESS_TOKEN_EXPIRE_MINUTES'] : self::DEFAULT_ACCESS_TOKEN_EXPIRE_MINUTES;
        $this->refresh_token_secret_key = $_ENV['REFRESH_TOKEN_SECRET_KEY'] ?? self::DEFAULT_REFRESH_TOKEN_SECRET_KEY;
        $this->refresh_token_expire_minutes = isset($_ENV['REFRESH_TOKEN_EXPIRE_MINUTES']) ? (int)$_ENV['REFRESH_TOKEN_EXPIRE_MINUTES'] : self::DEFAULT_REFRESH_TOKEN_EXPIRE_MINUTES;
        $this->auth_issuer = $_ENV['AUTH_ISSUER'] ?? self::DEFAULT_AUTH_ISSUER;
        $this->auth_audience = $_ENV['AUTH_AUDIENCE'] ?? self::DEFAULT_AUTH_AUDIENCE;
    }

    public function createEnvFileIfNotExists(): void
    {
        $file_path = G5_PATH . '/' . $this->env_file;
        if (!file_exists($file_path)) {
            $env_content = "API_VERSION=" . self::DEFAULT_API_VERSION . "\n";
            $env_content .= "ACCESS_TOKEN_SECRET_KEY=" . self::DEFAULT_ACCESS_TOKEN_SECRET_KEY . "\n";
            $env_content .= "ACCESS_TOKEN_EXPIRE_MINUTES=" . self::DEFAULT_ACCESS_TOKEN_EXPIRE_MINUTES . "\n";
            $env_content .= "REFRESH_TOKEN_SECRET_KEY=" . self::DEFAULT_REFRESH_TOKEN_SECRET_KEY . "\n";
            $env_content .= "REFRESH_TOKEN_EXPIRE_MINUTES=" . self::DEFAULT_REFRESH_TOKEN_EXPIRE_MINUTES . "\n";
            $env_content .= "AUTH_ISSUER=" . self::DEFAULT_AUTH_ISSUER . "\n";
            $env_content .= "AUTH_AUDIENCE=" . self::DEFAULT_AUTH_AUDIENCE . "\n";

            try {
                file_put_contents($file_path, $env_content);
            } catch (Exception $e) {
                throw new Exception("Unable to create .env file: " . $e->getMessage());
            }
        }
    }
}
