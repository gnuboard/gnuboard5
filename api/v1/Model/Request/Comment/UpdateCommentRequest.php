<?php

namespace API\v1\Model\Request\Comment;

use Exception;

/**
 * @OA\Schema(
 *     type="object",
 *     description="게시글 수정 모델",
 * )
 */
class UpdateCommentRequest
{
    /**
     * 댓글 내용
     * @OA\Property(example="내용")
     */
    public string $wr_content = '';

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
     * @param array $member 회원 정보
     * @param array $data 댓글 데이터
     * @throws Exception 내용, 비밀번호 오류
     * @return void
     */
    public function __construct(array $member, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->validateContent();
        $this->validatePassword($member);

        $this->initializeOption();
        $this->initializeMemberData($member);
    }

    /**
     * 내용 유효성 검사
     */
    public function validateContent(): void
    {
        $this->wr_content = sanitize_input($this->wr_content, 65536);

        if ($this->wr_content === '') {
            throw new Exception('내용을 입력하세요.');
        }
        if (substr_count($this->wr_content, '&#') > 50) {
            throw new Exception('내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
        }
    }

    /**
     * 비밀번호 유효성 검사
     */
    public function validatePassword(array $member): void
    {
        if (!$member['mb_id'] && $this->wr_password === '') {
            throw new Exception('비회원은 비밀번호는 필수로 입력해야 합니다.');
        }
    }

    /**
     * 옵션 - 비밀댓글 설정
     */
    public function initializeOption(): void
    {
        $this->wr_option = $this->wr_option !== 'secret' ? '' : 'secret';
    }

    /**
     * 회원 데이터 설정
     */
    public function initializeMemberData(array $member): void
    {
        $this->wr_password = $member['mb_id'] ? '' : get_encrypt_string($this->wr_password);
    }
}
