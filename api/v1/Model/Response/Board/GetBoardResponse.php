<?php

namespace API\v1\Model\Response\Board;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판 정보 응답 모델",
 * )
 */
class GetBoardResponse
{
    /**
     * 게시판 코드
     * @var string
     * @OA\Property(example="string")
     */
    public string $bo_table = '';

    /**
     * 게시판 그룹 ID
     * @var string
     * @OA\Property(example="string")
     */
    public string $gr_id = '';

    /**
     * 게시판 제목
     * @var string
     * @OA\Property(example="string")
     */
    public string $bo_subject = '';

    /**
     * 모바일 게시판 제목
     * @var string
     * @OA\Property(example="string")
     */
    public string $bo_mobile_subject = '';

    /**
     * 게시판 접속 허용 디바이스
     * @var string
     * @OA\Property(example="string")
     */
    public string $bo_device = '';

    /**
     * 게시판 관리자
     * @var string
     * @OA\Property(example="string")
     */
    public string $bo_admin = '';

    /**
     * 목록 보기 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_list_level = 0;

    /**
     * 읽기 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_read_level = 0;

    /**
     * 쓰기 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_write_level = 0;

    /**
     * 답글 쓰기 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_reply_level = 0;

    /**
     * 댓글 쓰기 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_comment_level = 0;

    /**
     * 업로드 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_upload_level = 0;

    /**
     * 다운로드 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_download_level = 0;

    /**
     * HTML 사용 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_html_level = 0;

    /**
     * 링크 사용 권한 레벨
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_link_level = 0;

    /**
     * 게시글 삭제가 제한될 댓글 갯수
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_count_delete = 0;

    /**
     * 게시글 수정이 제한될 댓글 갯수
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_count_modify = 0;

    /**
     * 읽기 포인트
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_read_point = 0;

    /**
     * 쓰기 포인트
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_write_point = 0;

    /**
     * 댓글 포인트
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_comment_point = 0;

    /**
     * 다운로드 포인트
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_download_point = 0;

    /**
     * 카테고리 사용 여부
     * @var int
     * @OA\Property(example=0)
     */
    public int $bo_use_category = 0;

    /**
     * 카테고리 목록 (,로 구분)
     * @var string
     * @OA\Property(example="string")
     */
    public string $bo_category_list = '';

    public function __construct($data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key) && $value) {
                $this->$key = $value;
            }
        }
    }
}
