<?php

namespace API;

use OpenApi\Annotations\OpenApi;
use OpenApi\Generator;
use Exception;

/**
 * OpenAPI (Open API Specification) 문서 생성 클래스
 */
class OASGenerator
{
    /**
     * $env_config 환경 설정
     */
    private EnvironmentConfig $env_config;

    /**
     * API 버전
     */
    private string $version;

    /**
     * OpenAPI 문서 파일명
     */
    private string $filename = 'openapi.yaml';

    private string $api_file_base_path = G5_DATA_PATH . '/api_doc';
    private string $api_file_base_url = G5_DATA_URL . '/api_doc';

    /**
     * OASGenerator 생성자
     *
     * @param string|null $version API 버전 (null 일 경우 환경 설정의 기본값 사용)
     */
    public function __construct(?string $version)
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
        $api_dir = "{$root_path}/api/{$this->version}";
        $plugin_dir = glob(G5_PATH . '/api/Plugin/*');
        $exception_dir = "{$root_path}/api/Exceptions";
        $handler_dir = "{$root_path}/api/Handlers";
        $openapi_file_path = "{$this->api_file_base_path}/{$this->filename}";
        if (!is_dir($this->api_file_base_path)) {
            mkdir($this->api_file_base_path, G5_DIR_PERMISSION);
        }

        try {
            $openapi = Generator::scan(
                [
                    $api_dir,
                    $plugin_dir,
                    $exception_dir,
                    $handler_dir,
                ],
                [
                    'version' => OpenApi::VERSION_3_1_0
                ]
            );

            file_put_contents($openapi_file_path, $openapi->toYaml());
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * 생성된 OpenAPI 문서의 URL 을 반환합니다.
     *
     * @return string OpenAPI 파일의 전체 URL
     */
    public function getOASUrl(): string
    {
        return "{$this->api_file_base_url}/{$this->filename}";
    }
}
