<?php

namespace API\v1\Model\Request\Comment;

use Exception;

/**
 * @OA\Schema(
 *     type="object",
 *     description="댓글 작성 모델",
 * )
 */
class CreateCommentRequest
{
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

    public function __construct(array $board, array $member, array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->validate_content();
        $this->validate_option();
        $this->validate_name($member);
        $this->validate_password($member);

        $this->set_member_data($board, $member);
    }

    /**
     * 내용 유효성 검사
     */
    public function validate_content(): void
    {
        $this->wr_content = $this->sanitizeInput($this->wr_content, 65536);

        if ($this->wr_content === '') {
            throw new Exception('내용을 입력하세요.');
        }
        if (substr_count($this->wr_content, '&#') > 50) {
            throw new Exception('내용에 올바르지 않은 코드가 다수 포함되어 있습니다.');
        }
    }

    /**
     * 옵션 - 비밀댓글 설정
     */
    public function validate_option(): void
    {
        if ($this->wr_option !== 'secret') {
            $this->wr_option = '';
        }
    }

    /**
     * 이름 유효성 검사
     */
    public function validate_name(array $member): void
    {
        $this->wr_name = $this->sanitizeInput($this->wr_name, 20);

        if (!$member['mb_id'] && $this->wr_name === '') {
            throw new Exception('비회원은 이름은 필수로 입력해야 합니다.');
        }
    }

    /**
     * 비밀번호 유효성 검사
     */
    public function validate_password(array $member): void
    {
        if (!$member['mb_id'] && $this->wr_password === '') {
            throw new Exception('비회원은 비밀번호는 필수로 입력해야 합니다.');
        }
    }

    /**
     * 회원 데이터 설정
     */
    public function set_member_data(array $board, array $member): void
    {
        if ($member['mb_id']) {
            $this->wr_name = addslashes(clean_xss_tags($board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick']));
            $this->wr_password = '';
        } else {
            $this->wr_password = get_encrypt_string($this->wr_password);
        }
    }

    /**
     * 입력 값을 정리하고 제한 길이만큼 자름
     */
    private function sanitizeInput(string $input, int $maxLength, bool $stripTags = false): string
    {
        $input = substr(trim($input), 0, $maxLength);
        if ($stripTags) {
            $input = trim(strip_tags($input));
        }
        return preg_replace("#[\\\]+$#", "", $input);
    }
}
