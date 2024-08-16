<?php

namespace API\v1\Model\Response\Group;

use API\v1\Traits\SchemaHelperTrait;

/**
 * @OA\Schema(
 *      type="object",
 *      description="게시판그룹 정보",
 * )
 */
class Group
{
    use SchemaHelperTrait;

    /**
     * 그룹 아이디
     * @OA\Property(example="string")
     */
    public string $gr_id = '';

    /**
     * 그룹 제목
     * @OA\Property(example="string")
     */
    public string $gr_subject = '';

    /**
     * 접속 허용 디바이스
     * @OA\Property(example="string")
     */
    public string $gr_device = '';

    /**
     * 그룹 관리자
     * @OA\Property(example="string")
     */
    public string $gr_admin = '';

    /**
     * 접근 권한 사용 여부
     * @OA\Property(example=0)
     */
    public int $gr_use_access = 0;

    /**
     * 그룹 순서
     * @OA\Property(example=0)
     */
    public int $gr_order = 0;

    public function __construct(array $data = [])
    {
        $this->mapDataToProperties($this, $data);
    }
}
