<?php

namespace API;

use OpenApi\Generator;
use API\EnvironmentConfig;
use Exception;

/**
 * OpenAPI (Open API Specification) 문서 생성 클래스
 */
class OASGenerator
{
    /** @var EnvironmentConfig 환경 설정 객체 */
    private EnvironmentConfig $env_config;

    /** @var string API 버전 */
    private string $version;

    /** @var string OpenAPI 문서 파일명 */
    private string $filename = "openapi.yaml";

    /**
     * OASGenerator 생성자
     * 
     * @param string|null $version API 버전 (null일 경우 환경 설정의 기본값 사용)
     */
    public function __construct(string $version = null)
    {
        $this->env_config = new EnvironmentConfig();
        $this->version = $version ?? $this->env_config->api_version;
    }

    /**
     * OpenAPI 문서를 요청한 API 버전 경로에 생성/갱신 합니다.
     *
     * @param string $root_path (선택적) API 디렉토리의 루트 경로 (기본값은 G5_PATH).
     * @throws Exception OpenAPI 생성 중 오류 발생 시
     */
    public function generate(string $root_path = G5_PATH): void
    {
        try {
            $api_dir = "{$root_path}/api/{$this->version}";
            $openapi = Generator::scan([$api_dir]);
            file_put_contents("{$api_dir}/{$this->filename}", $openapi->toYaml());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 생성된 OpenAPI 문서의 URL을 반환합니다.
     * 
     * @param string $base_url 기본 URL (기본값: G5_URL)
     * @return string OpenAPI 파일의 전체 URL
     */
    public function getOASUrl(string $base_url = G5_URL): string
    {
        return "{$base_url}/api/{$this->version}/{$this->filename}";
    }
}
