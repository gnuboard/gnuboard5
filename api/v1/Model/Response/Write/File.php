<?php

namespace API\v1\Model\Response\Write;

/**
 * @OA\Schema(
 *     type="object",
 *     description="파일 정보",
 * )
 */
class File
{
    /**
     * 원본 파일명
     * @OA\Property()
     */
    public string $bf_source = "";

    /**
     * 파일 크기
     * @OA\Property()
     */
    public int $bf_filesize = 0;

    /**
     * 다운로드 수
     * @OA\Property()
     */
    public int $bf_download = 0;

    /**
     * 업로드 일시
     * @OA\Property(format="date-time")
     */
    public string $bf_datetime = "";

    /**
     * 파일 경로
     * @OA\Property()
     */
    public string $bf_file = "";

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->bf_file = G5_DATA_URL . '/file/' . $data['bo_table'] . '/' . $this->bf_file;
    }
}
