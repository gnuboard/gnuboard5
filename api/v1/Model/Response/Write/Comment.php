<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="댓글 정보",
 * )
 */
class Comment
{
    use SchemaHelperTrait;

    /**
     * 댓글 ID
     * @OA\Property
     */
    public int $wr_id = 0;

    /**
     * 부모 글 ID
     * @OA\Property
     */
    public int $wr_parent = 0;

    /**
     * 작성자 이름
     * @OA\Property(example="댓글 작성자")
     */
    public string $wr_name = "";

    /**
     * 회원 ID
     * @OA\Property(example="test")
     */
    public string $mb_id = "";

    /**
     * 회원 이미지 경로
     * @OA\Property(example="/data/member_image\\te\\test.gif?1712194038")
     */
    public string $mb_image_path = "";

    /**
     * 회원 아이콘 경로
     * @OA\Property(example="/data/member\\te\\test.gif?1712194038")
     */
    public string $mb_icon_path = "";

    /**
     * 댓글 내용
     * @OA\Property(example="댓글 내용입니다")
     */
    public string $save_content = "";

    /**
     * 작성일시
     * @OA\Property(format="date-time")
     */
    public string $wr_datetime = "";

    /**
     * 마지막 수정일시
     * @OA\Property(format="date-time")
     */
    public string $wr_last = "";

    /**
     * 옵션
     * @OA\Property(example="html1")
     */
    public string $wr_option = "";

    /**
     * 작성자 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $wr_email = "";

    /**
     * 댓글 수
     * @OA\Property
     */
    public int $wr_comment = 0;

    /**
     * 대댓글
     * @OA\Property
     */
    public string $wr_comment_reply = "";

    /**
     * 답글 여부
     * @OA\Property
     */
    public bool $is_reply = false;

    /**
     * 수정 가능 여부
     * @OA\Property
     */
    public bool $is_edit = false;

    /**
     * 삭제 가능 여부
     * @OA\Property
     */
    public bool $is_del = false;

    /**
     * 비밀글 여부
     * @OA\Property
     */
    public bool $is_secret = false;

    /**
     * 비밀글 내용 여부
     * @OA\Property
     */
    public bool $is_secret_content = false;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
