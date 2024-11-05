<?php

namespace API\v1\Model\Response\Board;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판 정보",
 * )
 */
class Board
{
    use SchemaHelperTrait;

    /**
     * 게시판 아이디
     * @OA\Property(example="free")
     */
    public string $bo_table = '';

    /**
     * 그룹 아이디
     * @OA\Property(example="community")
     */
    public string $gr_id = '';

    /**
     * 게시판 제목
     * @OA\Property(example="자유게시판")
     */
    public string $bo_subject = '';

    /**
     * 게시판 모바일 제목
     * @OA\Property(example="자유게시판 모바일")
     */
    public string $bo_mobile_subject = '';

    /**
     * 게시판 스킨
     * @OA\Property(example="basic")
     */
    public string $bo_skin = '';
    
    /**
     * 게시판 모바일 스킨
     * @OA\Property(example="basic")
     */
    public string $bo_mobile_skin = '';

    /**
     * 접속 허용 디바이스
     * @OA\Property(example="both")
     */
    public string $bo_device = '';

    /**
     * 게시글 공지 번호
     * @OA\Property
     */
    public string $bo_notice = '';
    
    /**
     * 게시판 관리자
     * @OA\Property(example="admin")
     */
    public string $bo_admin = '';

    /**
     * 목록 접근 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_list_level = 0;

    /**
     * 읽기 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_read_level = 0;

    /**
     * 쓰기 권한 레벨
     * @OA\Property(example=2)
     */
    public int $bo_write_level = 0;

    /**
     * 답글 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_reply_level = 0;

    /**
     * 댓글 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_comment_level = 0;

    /**
     * 업로드 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_upload_level = 0;

    /**
     * 다운로드 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_download_level = 0;

    /**
     * HTML 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_html_level = 0;

    /**
     * 링크 권한 레벨
     * @OA\Property(example=1)
     */
    public int $bo_link_level = 0;

    /**
     * 게시글 삭제를 제한할 댓글 갯수
     * @OA\Property(example=1)
     */
    public int $bo_count_delete = 0;

    /**
     * 게시글 수정을 제한할 댓글 갯수
     * @OA\Property(example=1)
     */
    public int $bo_count_modify = 0;

    /**
     * 읽기 포인트
     * @OA\Property
     */
    public int $bo_read_point = 0;

    /**
     * 쓰기 포인트
     * @OA\Property
     */
    public int $bo_write_point = 0;

    /**
     * 댓글 포인트
     * @OA\Property
     */
    public int $bo_comment_point = 0;

    /**
     * 다운로드 포인트
     * @OA\Property
     */
    public int $bo_download_point = 0;

    /**
     * 카테고리 사용 여부
     * @OA\Property(example=1)
     */
    public int $bo_use_category = 0;

    /**
     * 전체검색 사용 여부
     * @OA\Property(example=1)
     */
    public int $bo_use_search = 0;

    /**
     * 파일 업로드 갯수
     * @OA\Property(example=2)
     */
    public int $bo_upload_count = 0;

    /**
     * 카테고리 목록
     * @OA\Property(example="게시판|취미|소모임|그누보드")
     */
    public string $bo_category_list = '';

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
