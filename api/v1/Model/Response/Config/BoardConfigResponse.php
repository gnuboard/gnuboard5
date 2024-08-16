<?php

namespace API\v1\Model\Response\Config;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="기본환경설정 > 게시판 설정 응답 모델",
 * )
 */
class BoardConfigResponse
{
    use SchemaHelperTrait;

    /**
     * 포인트 사용 여부
     * @OA\Property(example=1)
     */
    public int $cf_use_point = 0;

    /**
     * 	포인트 유효 기간 (일)
     * @OA\Property(example=0)
     */
    public int $cf_point_term = 0;

    /**
     * 게시글복사 로그 사용여부
     * @OA\Property(example=1)
     */
    public int $cf_use_copy_log = 0;

    /**
     * 표시할 이름(닉네임)자리 수
     * @OA\Property(example=15)
     */
    public int $cf_cut_name = 0;

    /**
     * 최근 게시물 표시할 목록 수
     * @OA\Property(example=15)
     */
    public int $cf_new_rows = 0;

    /**
     * 게시글읽기 포인트
     * @OA\Property(example=1)
     */
    public int $cf_read_point = 0;

    /**
     * 게시글쓰기 포인트
     * @OA\Property(example=5)
     */
    public int $cf_write_point = 0;

    /**
     * 댓글쓰기 포인트
     * @OA\Property(example=2)
     */
    public int $cf_comment_point = 0;

    /**
     * 파일 다운로드 포인트
     * @OA\Property(example=10)
     */
    public int $cf_download_point = 0;

    /**
     * 페이징 번호 표시 수
     * @OA\Property(example=10)
     */
    public int $cf_write_pages = 0;

    /**
     * 페이징 번호 표시 수 (모바일)
     * @OA\Property(example=5)
     */
    public int $cf_mobile_pages = 0;

    /**
     * 새창 링크 (_self, _blank 등)
     * @OA\Property(example="_blank")
     */
    public string $cf_link_target = "";

    /**
     * 짧은주소 사용 여부
     * @OA\Property(example=0)
     */
    public int $cf_bbs_rewrite = 0;

    /**
     * 	게시글 작성 간격(초)
     * @OA\Property(example=30)
     */
    public int $cf_delay_sec = 0;

    /**
     * 필터할 단어 (, 구분자)
     * @OA\Property(example="금지어,욕설")
     */
    public string $cf_filter = "";

    /**
     * 접근 가능 IP
     * @OA\Property(example="192.168.0.1")
     */
    public string $cf_possible_ip = "";

    /**
     * 접근 차단 IP
     * @OA\Property(example="192.168.0.2")
     */
    public string $cf_intercept_ip = "";

    public function __construct($config = [])
    {
        $this->mapDataToProperties($this, $config);
    }
}
