<?php

namespace API\v1\Model\Response\Layer;

/**
 * @OA\Schema(
 *     schema="PopupResponse",
 *     type="object",
 *     description="Popup 창 목록 응답 모델"
 * )
 */
class PopupResponse
{
    
    public function __construct($data)
    {
        $this->nw_id = $data['nw_id'] ?? 0;
        $this->nw_division = $data['nw_division'] ?? '';
        $this->nw_device = $data['nw_device'] ?? '';
        $this->nw_begin_time = $data['nw_begin_time'] ?? '';
        $this->nw_end_time = $data['nw_end_time'] ?? '';
        $this->nw_disable_hours = $data['nw_disable_hours'] ?? 0;
        $this->nw_left = $data['nw_left'] ?? 0;
        $this->nw_top = $data['nw_top'] ?? 0;
        $this->nw_height = $data['nw_height'] ?? 0;
        $this->nw_width = $data['nw_width'] ?? 0;
        $this->nw_subject = $data['nw_subject'] ?? '';
        $this->nw_content = $data['nw_content'] ?? '';
    }
    /**
     * @OA\Property(
     *     description="New popup id"
     * )
     */
    public int $nw_id = 0;

    /**
     * @OA\Property(
     *     description="Popup 종류 구분 (comm:커뮤니티, shop:쇼핑, both: 전체)"
     * )
     */
    public string $nw_division = '';

    /**
     * @OA\Property(
     *     description="Popup Widget 접속 기기 (pc, mobile, both)"
     * )
     */
    public string $nw_device = '';

    /**
     * @OA\Property(
     *     format="date-time",
     *     description="Popup Widget 시작 시간"
     * )
     */
    public string $nw_begin_time = '';

    /**
     * @OA\Property(
     *     format="date-time",
     *     description="Popup Widget 종료 시간"
     * )
     */
    public string $nw_end_time = '';

    /**
     * @OA\Property(
     *     description="Popup Widget 팝업 비활성화 시간 (시간)"
     * )
     */
    public int $nw_disable_hours = 0;

    /**
     * @OA\Property(
     *     description="Popup Widget Left Position"
     * )
     */
    public int $nw_left = 0;

    /**
     * @OA\Property(
     *     description="Popup Widget Top Position"
     * )
     */
    public int $nw_top = 0;

    /**
     * @OA\Property(
     *     description="Popup 위젯 높이"
     * )
     */
    public int $nw_height = 0;

    /**
     * @OA\Property(
     *     description="Popup 위젯 가로"
     * )
     */
    public int $nw_width = 0;

    /**
     * @OA\Property(
     *     description="Popup Subject"
     * )
     */
    public string $nw_subject = '';

    /**
     * @OA\Property(
     *     description="Popup Content"
     * )
     */
    public string $nw_content = '';
}