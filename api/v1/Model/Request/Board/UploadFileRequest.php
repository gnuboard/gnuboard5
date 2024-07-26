<?php

namespace API\v1\Model\Request\Board;

use API\Service\BoardFileService;
use API\v1\Traits\SchemaHelperTrait;
use Exception;

/**
 * @OA\Schema(
 *     type="object",
 *     description="게시글 파일 업로드 모델",
 * )
 */
class UploadFileRequest
{
    use SchemaHelperTrait;

    /**
     * 업로드 파일
     * @OA\Property(property="files[]", type="array", @OA\Items(type="string", format="binary", example=""))
     */
    public array $files;

    /**
     * 파일 설명
     * @OA\Property(property="file_contents[]", type="array", @OA\Items(type="string", example=""))
     */
    public array $file_contents;

    /**
     * 기존 파일 삭제여부 (true: 삭제, false: 유지)
     * @OA\Property(property="file_dels[]", type="array", @OA\Items(type="boolean", example="false"))
     */
    public array $file_dels;

    private const ERROR_NO_UPLOAD_WRITE_FILES = '기존 파일을 삭제하신 후 첨부파일을 %s개 이하로 업로드 해주십시오.';
    private const ERROR_NO_UPLOAD_COUNT = '파일은 %s개 까지만 업로드 가능합니다.';
    private const ERROR_FILE_SIZE = '\"%s\" 파일의 용량(%s 바이트)이 게시판에 설정(%s 바이트)된 값보다 크므로 업로드 하지 않습니다.';
    private const ERROR_FILE_EXT = '\"%s\" 파일의 확장자가 허용된 확장자가 아니므로 업로드 하지 않습니다.';

    private array $disallowed_ext = [
        'exe', 'bat', 'sh', 'php', 'com', 'cmd', 'vbs', 'vbe', 'js', 'jse', 'wsf', 'wsh', 'msc', 'scr',
        'pif', 'cpl', 'jar', 'vb', 'vbe', 'ws', 'bas', 'prg', 'scpt', 'scptd', 'app', 'ipa', 'apk', 'dll',
        'sys', 'drv', 'bin', 'inf', 'reg', 'gadget'
    ];
    private array $board;
    private array $write;

    /**
     * @param array $board 게시판 정보
     * @param array $write 게시글 정보
     * @param array $uploaded_files 업로드된 파일
     * @param array $data 파일관련 데이터
     * @throws Exception 파일 업로드 오류, 크기, 확장자 오류
     * @return void
     */
    public function __construct(
        BoardFileService $file_service,
        array $board,
        array $write,
        array $uploaded_files,
        array $data
    ) {
        $this->board = $board;
        $this->write = $write;

        $this->files = $uploaded_files['files'] ?? [];
        // 업로드되지 않은 파일은 Request Body에 포함되기 때문에 제거한다.
        unset($data['files']);

        $this->mapDataToProperties($this, $data);

        $this->setDefaultFileContents();
        $this->setDefaultFileDels();
        $this->checkWriteFilesCount($file_service);
        $this->checkFilesCount();
        $this->validateFile();
    }

    /**
     * 파일 설명 기본값 설정
     */
    public function setDefaultFileContents()
    {
        foreach ($this->file_contents as $key => $content) {
            if (empty($content)) {
                $this->file_contents[$key] = "";
            }
        }
    }

    /**
     * 파일 삭제여부 기본값 설정
     */
    public function setDefaultFileDels()
    {
        $file_no = [];
        foreach ($this->file_dels as $key => $del) {
            if ($del && ($del === true || $del === "true")) {
                $file_no[] = $key;
            }
        }

        $this->file_dels = $file_no;
    }

    /**
     * 파일 유효성 검사(오류, 크기, 확장자)
     */
    public function validateFile(): void
    {
        foreach ($this->files as $file) {
            $filename = $file->getClientFilename();
            $filesize = $file->getSize();
            // 파일 업로드 오류
            if ($file->getError()) {
                continue;
            }
            // 크기 체크
            if ($filesize > $this->board['bo_upload_size']) {
                $this->throwException(
                    sprintf(self::ERROR_FILE_SIZE, $filename, number_format($filesize), number_format($this->board['bo_upload_size']))
                );
            }
            // 확장자 체크
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (in_array(strtolower($ext), array_map('strtolower', $this->disallowed_ext))) {
                $this->throwException(sprintf(self::ERROR_FILE_EXT, $filename));
            }
        }
    }

    /**
     * 게시글 첨부파일 갯수 체크
     */
    public function checkWriteFilesCount(BoardFileService $file_service): void
    {
        $write_files = $file_service->fetchWriteFiles($this->write['wr_id']);

        if (count($write_files) > $this->board['bo_upload_count']) {
            $this->throwException(sprintf(self::ERROR_NO_UPLOAD_WRITE_FILES, $this->board['bo_upload_count']));
        }
    }

    /**
     * 업로드 파일 갯수 체크
     */
    public function checkFilesCount(): void
    {
        if (count($this->files) > $this->board['bo_upload_count']) {
            $this->throwException(sprintf(self::ERROR_NO_UPLOAD_COUNT, $this->board['bo_upload_count']));
        }
    }
}
