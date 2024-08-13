<?php

namespace API\v1\Model\Response\Write;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시글 정보",
 * )
 */
class Write
{
    use SchemaHelperTrait;

    /**
     * 글 ID
     * @OA\Property
     */
    public int $wr_id = 0;

    /**
     * 글 번호
     * @OA\Property(example=-2)
     */
    public int $wr_num = 0;

    /**
     * 답글
     * @OA\Property(example="")
     */
    public string $wr_reply = "";

    /**
     * 글 제목
     * @OA\Property(example="제목입니다")
     */
    public string $wr_subject = "";

    /**
     * 작성자 이름
     * @OA\Property(example="홍길동")
     */
    public string $wr_name = "";

    /**
     * 회원 아이디
     * @OA\Property(example="test")
     */
    public string $mb_id = "";

    /**
     * 회원 이미지 경로
     * @OA\Property(example="/data/member_image/te/test.gif?1712194038")
     */
    public string $mb_image_path = "";

    /**
     * 회원 아이콘 경로
     * @OA\Property(example="/data/member/te/test.gif?1712194038")
     */
    public string $mb_icon_path = "";

    /**
     * 작성일시
     * @OA\Property(format="date-time")
     */
    public string $wr_datetime = "";

    /**
     * 작성자 이메일
     * @OA\Property(example="test@test.com")
     */
    public string $wr_email = "";

    /**
     * 글 내용
     * @OA\Property(example="<p>내용입니다.</p>")
     */
    public string $wr_content = "";

    /**
     * 링크1
     * @OA\Property(example="")
     */
    public string $wr_link1 = "";

    /**
     * 링크2
     * @OA\Property(example="")
     */
    public string $wr_link2 = "";

    /**
     * 댓글 수
     * @OA\Property(example=2)
     */
    public int $wr_comment = 0;

    /**
     * 조회 수
     * @OA\Property(example=3)
     */
    public int $wr_hit = 0;

    /**
     * 작성자 IP
     * @OA\Property(example="127.0.0.1")
     */
    public string $wr_ip = "";

    /**
     * 추천 수
     * @OA\Property(example=0)
     */
    public int $good = 0;

    /**
     * 비추천 수
     * @OA\Property(example=0)
     */
    public int $nogood = 0;

    /**
     * 썸네일 정보
     * @OA\Property(ref="#/components/schemas/Thumbnail")
     */
    public Thumbnail $thumbnail;

    /**
     * 옵션
     * @OA\Property(example="html1")
     */
    public string $wr_option = "";

    /**
     * 이미지 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/File")
     * )
     * 
     * @var \API\v1\Model\Response\Write\File[]
     */
    public array $images = [];

    /**
     * 일반 파일 목록
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/File")
     * )
     * 
     * @var \API\v1\Model\Response\Write\File[]
     */
    public array $normal_files = [];

    /**
     * 이전 글 정보
     * @OA\Property(ref="#/components/schemas/NeighborWrite")
     */
    public array $prev = [];

    /**
     * 다음 글 정보
     * @OA\Property(ref="#/components/schemas/NeighborWrite")
     */
    public array $next = [];

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);

        // Thumbnail 초기화
        if (empty($this->thumbnail)) {
            $this->thumbnail = new Thumbnail();
        }
    }
}
