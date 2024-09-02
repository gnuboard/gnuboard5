<?php

namespace API\v1\Model\Request\Board;

use API\Service\BoardPermission;
use API\v1\Traits\SchemaHelperTrait;
use Exception;

/**
 * @OA\Schema(
 *     type="object",
 *     description="게시글 수정 모델",
 * )
 */
class UpdateWriteRequest
{
    use SchemaHelperTrait;

    /**
     * 게시글 제목
     * @OA\Property(example="제목")
     */
    public string $wr_subject = '';

    /**
     * 게시글 내용
     * @OA\Property(example="내용")
     */
    public string $wr_content = '';

    /**
     * 게시글 작성자
     * @OA\Property(example="작성자")
     */
    public string $wr_name = '';

    /**
     * 게시글 비밀번호
     * @OA\Property(example="비밀번호")
     */
    public string $wr_password = '';

    /**
     * 게시글 이메일
     * @var string
     * @OA\Property(example="이메일")
     */
    public string $wr_email = '';

    /**
     * 홈페이지
     * @OA\Property(example="홈페이지")
     */
    public string $wr_homepage = '';

    /**
     * 링크1
     * @OA\Property(example="링크1")
     */
    public string $wr_link1 = '';

    /**
     * 링크2
     * @OA\Property(example="링크2")
     */
    public string $wr_link2 = '';

    /**
     * 옵션
     * @OA\Property(example="옵션")
     */
    public string $wr_option = '';

    /**
     * HTML
     * @OA\Property(example="HTML")
     */
    public string $html = '';

    /**
     * 메일
     * @OA\Property(example="메일")
     */
    public string $mail = '';

    /**
     * 비밀글
     * @OA\Property(example="비밀글")
     */
    public string $secret = '';

    /**
     * 카테고리
     * @OA\Property(example="카테고리")
     */
    public string $ca_name = '';

    /**
     * 공지
     * @OA\Property(example=false)
     */
    public bool $notice = false;

    /**
     * @param BoardPermission $permission  게시판 권한
     * @param array $write  게시글 정보
     * @param array $member  회원 정보
     * @param array $data  요청 데이터
     * @throws Exception  유효성 검사 실패시 예외 발생
     * @return void
     */
    public function __construct(BoardPermission $permission, array $write, array $member, array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        $board = $permission->board;
        $is_board_manager = $permission->isBoardManager($member['mb_id']);

        $this->validateCategory($board, $is_board_manager);
        $this->validateSubject();
        $this->validateContent();
        $this->validateOption($board, $is_board_manager);
        $this->validateName($write);

        $this->sanitizeLink();
        $this->initializeWriterData($write);

        // 공지여부는 게시판 정보에만 일괄저장되므로 입력만 받도록 처리한다.
        unset($this->notice);
    }

    /**
     * 분류 유효성 검사
     */
    public function validateCategory(array $board, bool $is_admin = false): void
    {
        $categories = array_map('trim', explode("|", $board['bo_category_list'] . ($is_admin ? '|공지' : '')));
        if (!$board['bo_use_category'] || empty($categories)) {
            $this->ca_name = '';
            return;
        }
        if (!$this->ca_name) {
            $this->throwException('분류를 선택하세요.');
        }
        if (!in_array($this->ca_name, $categories)) {
            $this->throwException('분류를 올바르게 입력하세요.');
        }
    }

    /**
     * 제목 유효성 검사
     */
    public function validateSubject(): void
    {
        $this->wr_subject = sanitize_input($this->wr_subject, 255);

        if ($this->wr_subject === '') {
            $this->throwException('제목을 입력하세요.');
        }
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
     * 링크 변환
     */
    public function sanitizeLink(): void
    {
        $this->wr_link1 = sanitize_input($this->wr_link1, 1000, true);
        $this->wr_link2 = sanitize_input($this->wr_link2, 1000, true);
    }

    /**
     * 비밀글, HTML, 메일, 공지 설정
     */
    public function validateOption(array $board, bool $is_admin = false): void
    {
        if (!$is_admin && !$board['bo_use_secret']) {
            if (stripos($this->html, 'secret') !== false || stripos($this->secret, 'secret') !== false || stripos($this->mail, 'secret') !== false) {
                $this->throwException('비밀글 미사용 게시판 이므로 비밀글로 등록할 수 없습니다.');
            }
        }

        if (!$is_admin && $board['bo_use_secret'] == 2) {
            $this->secret = 'secret';
        } else {
            if (preg_match('#secret#', strtolower($this->secret), $matches)) {
                $this->secret = $matches[0];
            }
        }

        if (preg_match('#html(1|2)#', strtolower($this->html), $matches)) {
            $this->html = $matches[0];
        }

        if (preg_match('#mail#', strtolower($this->mail), $matches)) {
            $this->mail = $matches[0];
        }

        $options = array($this->html, $this->secret, $this->mail);
        $this->wr_option = implode(',', array_filter(array_map('trim', $options)));
        unset($this->html, $this->secret, $this->mail);
    }

    /**
     * 이름 유효성 검사
     */
    public function validateName(array $write): void
    {
        // 외국인 이름 로마자 표기 37자
        if (preg_match('/^[A-Za-z\-]+$/', $this->wr_name)) {
            $this->wr_name = sanitize_input($this->wr_name, 37);
        } else {
            $this->wr_name = sanitize_input($this->wr_name, 20);
        }

        if (!$write['mb_id'] && $this->wr_name === '') {
            $this->throwException('비회원 게시글은 이름은 필수로 입력해야 합니다.');
        }
    }

    /**
     * 작성자 정보 데이터 설정
     */
    public function initializeWriterData(array $write): void
    {
        if ($write['mb_id']) {
            unset($this->wr_name, $this->wr_password, $this->wr_email, $this->wr_homepage);
        } else {
            $this->wr_name = addslashes(clean_xss_tags($this->wr_name));
            $this->wr_password = get_encrypt_string($this->wr_password);
            $this->wr_email = addslashes($this->wr_email);
            $this->wr_homepage = addslashes(clean_xss_tags($this->wr_homepage));
        }
    }
}
