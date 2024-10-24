<?php

namespace API\v1\Model\Response\Qa;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *     title="QaConfig",
 *     description="QaConfig",
 *     type="object"
 * )
 */
class QaConfigResponse
{

    use SchemaHelperTrait;

    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }

    /**
     * Q&A 제목
     * @OA\Property(example="")
     */
    public string $qa_title;

    /**
     * Q&A 카테고리
     * @OA\Property(example="")
     */
    public string $qa_category;

    /**
     * Q&A 스킨 (사용 안함)
     * @OA\Property(example="")
     */
    private string $qa_skin;

    /**
     * 모바일용 Q&A 스킨 (사용 안함)
     * @OA\Property(example="")
     */
    private string $qa_mobile_skin;

    /**
     * 이메일 사용 여부 (0: 사용 안 함, 1: 사용)
     * @OA\Property(example="0")
     */
    public int $qa_use_email;

    /**
     * 이메일 필수 여부 (0: 선택, 1: 필수)
     * @OA\Property(example="0")
     */
    public int $qa_req_email;

    /**
     * 휴대폰 번호 사용 여부 (0: 사용 안 함, 1: 사용)
     * @OA\Property(example="0")
     */
    public int $qa_use_hp;

    /**
     * 휴대폰 번호 필수 여부 (0: 선택, 1: 필수)
     * @OA\Property(example="0")
     */
    public int $qa_req_hp;

    /**
     * SMS 사용 여부 (0: 사용 안 함, 1: 사용)
     * @OA\Property(example="0")
     */
    public int $qa_use_sms;

    /**
     * SMS 발신 번호
     * @OA\Property(example="")
     */
    public string $qa_send_number;
    /**
     * 관리자 휴대폰 번호
     * @OA\Property(example="")
     */
    public string $qa_admin_hp;

    /**
     * 관리자 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $qa_admin_email;

    /**
     * 에디터 사용 여부
     * @OA\Property(example="0")
     */
    public int $qa_use_editor;

    /**
     * 제목 길이 제한
     * @OA\Property(example="")
     */
    public int $qa_subject_len;

    /**
     * 모바일용 제목 길이 제한
     * @OA\Property(example="")
     */
    public int $qa_mobile_subject_len;

    /**
     * 페이지당 행 수
     * @OA\Property(example="")
     */
    public int $qa_page_rows;

    /**
     * 모바일용 페이지당 행 수
     * @OA\Property(example="")
     */
    public int $qa_mobile_page_rows;

    /**
     * 이미지 너비
     * @OA\Property(example="")
     */
    public int $qa_image_width;

    /**
     * 업로드 가능한 파일 크기
     * @OA\Property(example="")
     */
    public int $qa_upload_size;

    /**
     * 콘텐츠 삽입 부분
     * @OA\Property(example="")
     */
    public string $qa_insert_content;

    /**
     * 상단 파일 경로
     * @OA\Property(example="")
     */
    private string $qa_include_head;

    /**
     * 하단 파일 경로
     * @OA\Property(example="")
     */
    private string $qa_include_tail;

    /**
     * 상단 내용
     * @OA\Property(example="")
     */
    public string $qa_content_head;

    /**
     * 하단 내용
     * @OA\Property(example="")
     */
    public string $qa_content_tail;

    /**
     * 모바일 상단 파일 경로
     * @OA\Property(example="")
     */
    public string $qa_mobile_content_head;

    /**
     * 모바일 하단 파일 경로
     * @OA\Property(example="")
     */
    public string $qa_mobile_content_tail;

    /**
     * 추가 필드 1 제목
     * @OA\Property(example="")
     */
    public string $qa_1_subj;

    /**
     * 추가 필드 2 제목
     * @OA\Property(example="")
     */
    public string $qa_2_subj;

    /**
     * 추가 필드 3 제목
     * @OA\Property(example="")
     */
    public string $qa_3_subj;

    /**
     * 추가 필드 4 제목
     * @OA\Property(example="")
     */
    public string $qa_4_subj;

    /**
     * 추가 필드 5 제목
     * @OA\Property(example="")
     */
    public string $qa_5_subj;

    /**
     * 추가 필드 1
     * @OA\Property(example="")
     */
    public string $qa_1;

    /**
     * 추가 필드 2
     * @OA\Property(example="")
     */
    public string $qa_2;

    /**
     * 추가 필드 3
     * @OA\Property(example="")
     */
    public string $qa_3;

    /**
     * 추가 필드 4
     * @OA\Property(example="")
     */
    public string $qa_4;

    /**
     * 추가 필드 5
     * @OA\Property(example="")
     */
    public string $qa_5;
}