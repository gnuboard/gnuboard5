<?php

namespace API\v1\Model\Request\Comment;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     type="object",
 *     description="댓글 작성 모델",
 * )
 */

#[\AllowDynamicProperties]
class CreateCommentRequest
{
    use SchemaHelperTrait;

    /**
     * 댓글 내용
     * @OA\Property(example="내용")
     */
    public string $wr_content = '';

    /**
     * 댓글 작성자 (비회원일 경우 필수)
     * @OA\Property(example="작성자")
     */
    public string $wr_name = '';

    /**
     * 댓글 비밀번호 (비회원일 경우 필수)
     * @OA\Property(example="비밀번호")
     */
    public string $wr_password = '';

    /**
     * 댓글 옵션(비밀글)
     * @OA\Property(example="옵션")
     */
    public string $wr_option = '';

    /**
     * 부모댓글 ID(대댓글일 경우)
     * @OA\Property(example=0)
     */
    public int $comment_id = 0;

    /**
     * 대댓글 여부
     * @OA\Property(example='')
     */
    public string $wr_comment_reply = '';

    /**
     * @OA\Property(example="홈페이지")
     */
    public string $wr_homepage = '';

    /**
     * @OA\Property(example="email")
     */
    public string $wr_email = '';

    public function __construct(array $board, array $member, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $this->validateContent();
        $this->validateOption();
        $this->validateName($member);
        $this->validatePassword($member);

        $this->initializeMemberData($board, $member);
    }

    /**
     * 내용 유효성 검사
     */
    public function validateContent(): void
    {
        $this->wr_content = sanitize_input($this->wr_content, 65536);

        if ($this->wr_content === '') {
            $this->throwException('내용을 입력하세요.');
        }
        if (substr_count($this->wr_content, '&#') > 50) {
            $this->throwException('내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
        }
    }

    /**
     * 옵션 - 비밀댓글 설정
     */
    public function validateOption(): void
    {
        if ($this->wr_option !== 'secret') {
            $this->wr_option = '';
        }
    }

    /**
     * 이름 유효성 검사
     */
    public function validateName(array $member): void
    {
        $this->wr_name = sanitize_input($this->wr_name, 20);

        if (!$member['mb_id'] && $this->wr_name === '') {
            $this->throwException('비회원은 이름은 필수로 입력해야 합니다.');
        }
    }

    /**
     * 비밀번호 유효성 검사
     */
    public function validatePassword(array $member): void
    {
        if (!$member['mb_id'] && $this->wr_password === '') {
            $this->throwException('비회원은 비밀번호는 필수로 입력해야 합니다.');
        }
    }

    /**
     * 회원 데이터 설정
     */
    public function initializeMemberData(array $board, array $member): void
    {
        if ($member['mb_id']) {
            $this->wr_name = addslashes(clean_xss_tags($board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick']));
            $this->wr_password = '';
        } else {
            $this->wr_password = get_encrypt_string($this->wr_password);
        }
    }
}
