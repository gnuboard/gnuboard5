<?php

namespace API\v1\Model\Response\Menu;

/**
 * @OA\Schema(
 *     type="object",
 *     description="메뉴 목록"
 * )
 */
class MenuItem
{
    /**
     * 메뉴 ID
     * @OA\Property(type="integer")
     */
    public int $me_id;

    /**
     * 이름
     * @OA\Property(type="string")
     */
    public string $me_name;

    /**
     * @OA\Property(type="string")
     */
    public string $me_link;

    /**
     * 메뉴 순서
     * @OA\Property(type="integer")
     */
    public int $me_order;

    /**
     * 모바일에서 사용여부
     * @OA\Property(type="integer")
     */
    public int $me_mobile_use;

    /**
     * 새창으로 열지 여부
     * @OA\Property(type="string")
     */
    public string $me_target;

    /**
     * Menu 구분코드
     * @OA\Property(type="string")
     */
    public string $me_code;

    /**
     * 메뉴 사용여부
     * @OA\Property(type="integer")
     */
    public int $me_use;

    /**
     * 하위 메뉴
     * @OA\Property(type="array", @OA\Items(type="object"))
     */
    public array $sub;

    public function __construct(array $data)
    {
        $this->me_id = $data['me_id'] ?? 0;
        $this->me_name = $data['me_name'] ?? '';
        $this->me_link = $data['me_link'] ?? '';
        $this->me_order = $data['me_order'] ?? 0;
        $this->me_mobile_use = $data['me_mobile_use'] ?? 0;
        $this->me_target = $data['me_target'] ?? '';
        $this->me_code = $data['me_code'] ?? '';
        $this->me_use = $data['me_use'] ?? 0;

        if (isset($data['sub']) && is_array($data['sub'])) {
            $this->sub = array_map(fn($item) => new MenuItem($item), $data['sub']);
        }
    }
}