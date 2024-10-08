<?php

namespace API;

use Dotenv\Dotenv;
use Exception;

class EnvironmentConfig
{
    public const DEFAULT_API_VERSION = 'v1';
    public const DEFAULT_ACCESS_TOKEN_EXPIRE_MINUTES = 30;
    public const DEFAULT_REFRESH_TOKEN_EXPIRE_MINUTES = 60 * 24 * 14; //2주
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

    /**
     * @throws \Random\RandomException
     * @throws Exception
     */
    public function __construct()
    {
        $this->createEnvFileIfNotExists();

        // Load environment variables
        $this->dotenv = Dotenv::createImmutable(G5_PATH);
        $this->dotenv->load();
        $this->api_version = $_ENV['API_VERSION'] ?? self::DEFAULT_API_VERSION;

        if (!trim($_ENV['ACCESS_TOKEN_SECRET_KEY'])) {
            throw new Exception('env ACCESS_TOKEN_SECRET_KEY is empty');
        }
        $this->access_token_secret_key = $_ENV['ACCESS_TOKEN_SECRET_KEY'];
        $this->access_token_expire_minutes = isset($_ENV['ACCESS_TOKEN_EXPIRE_MINUTES']) ? (int)$_ENV['ACCESS_TOKEN_EXPIRE_MINUTES'] : self::DEFAULT_ACCESS_TOKEN_EXPIRE_MINUTES;
        if(!trim($_ENV['REFRESH_TOKEN_SECRET_KEY'])) {
            throw new Exception('env REFRESH_TOKEN_SECRET_KEY is empty');
        }
        $this->refresh_token_secret_key = $_ENV['REFRESH_TOKEN_SECRET_KEY'];
        $this->refresh_token_expire_minutes = isset($_ENV['REFRESH_TOKEN_EXPIRE_MINUTES']) ? (int)$_ENV['REFRESH_TOKEN_EXPIRE_MINUTES'] : self::DEFAULT_REFRESH_TOKEN_EXPIRE_MINUTES;
        $this->auth_issuer = $_ENV['AUTH_ISSUER'] ?? self::DEFAULT_AUTH_ISSUER;
        $this->auth_audience = $_ENV['AUTH_AUDIENCE'] ?? self::DEFAULT_AUTH_AUDIENCE;
    }

    /**
     * @throws Exception .env 파일 생성 실패시
     * @throws \Random\RandomException Rand 함수 호출 실패시
     */
    public function createEnvFileIfNotExists(): void
    {
        $file_path = G5_PATH . '/' . $this->env_file;
        if (!file_exists($file_path)) {
            $env_content = 'API_VERSION=' . self::DEFAULT_API_VERSION . PHP_EOL;
            $env_content .= 'ACCESS_TOKEN_SECRET_KEY=' . self::createSecretTokenValue() . PHP_EOL;
            $env_content .= 'ACCESS_TOKEN_EXPIRE_MINUTES=' . self::DEFAULT_ACCESS_TOKEN_EXPIRE_MINUTES . PHP_EOL;
            $env_content .= 'REFRESH_TOKEN_SECRET_KEY=' . self::createSecretTokenValue() . PHP_EOL;
            $env_content .= 'REFRESH_TOKEN_EXPIRE_MINUTES=' . self::DEFAULT_REFRESH_TOKEN_EXPIRE_MINUTES . PHP_EOL;
            $env_content .= '# 이메일 등 정보를 암호화하는데 쓰입니다.' . PHP_EOL;
            $env_content .= 'ENCRYPTION_KEY=' . self::createSecretTokenValue() . PHP_EOL;
            $env_content .= 'AUTH_ISSUER=' . self::DEFAULT_AUTH_ISSUER . PHP_EOL;
            $env_content .= 'AUTH_AUDIENCE=' . self::DEFAULT_AUTH_AUDIENCE . PHP_EOL;
            $env_content .= '# CORS 설정' . PHP_EOL;
            $env_content .= '# 허용할 도메인을 , 로 구분하여 입력하세요.' . PHP_EOL;
            $env_content .= 'CORS_ALLOW_ORIGIN=' . G5_URL . PHP_EOL;
            $env_content .= 'CORS_ALLOW_METHODS="*"' . PHP_EOL;
            $env_content .= 'CORS_ALLOW_CREDENTIALS="true"' . PHP_EOL;
            $env_content .= 'FIREBASE_PROJECT_ID=""' . PHP_EOL;
            $env_content .= 'FIREBASE_KEY_PATH=""' . PHP_EOL;


            $result = file_put_contents($file_path, $env_content);
            if ($result === false) {
                throw new Exception('Unable to create .env file');
            }
        }
    }

    /**
     * 랜덤 비밀 토큰 값 생성
     * @throws Exception
     * @throws \Random\RandomException (php8.2 부터)
     */
    private static function createSecretTokenValue(): string
    {
        return bin2hex(random_bytes(32));
    }
}
