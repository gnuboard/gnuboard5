<?php

namespace API\v1\Model\Request\Qa;

use API\Service\QaService;
use API\v1\Traits\SchemaHelperTrait;
use Slim\Psr7\UploadedFile;

/**
 * upload file QaFileUploadRequest
 *
 * @OA\Schema(
 *   title="QaFileUploadRequest",
 *   description="QaFileUploadRequest",
 * )
 *
 */
class QaFileUploadRequest
{
    use SchemaHelperTrait;

    private const ERROR_FILE_SIZE = '\"%s\" 파일의 용량(%s 바이트)이 게시판에 설정(%s 바이트)된 값보다 크므로 업로드 하지 않습니다.';
    private const ERROR_FILE_EXT = '\"%s\" 파일의 확장자가 허용된 확장자가 아니므로 업로드 하지 않습니다.';


    public function __construct($data)
    {
        $this->mapDataToProperties($this, $data);
    }

    private array $disallowed_ext = [
        'exe', 'bat', 'sh', 'php', 'com', 'cmd', 'vbs', 'vbe', 'ts', 'mjs', 'js', 'jse', 'wsf', 'wsh', 'msc', 'scr',
        'pif', 'cpl', 'jar', 'vb', 'vbe', 'ws', 'bas', 'prg', 'scpt', 'scptd', 'app', 'ipa', 'apk', 'dll',
        'sys', 'drv', 'bin', 'inf', 'reg', 'gadget', 'html', 'htm', 'xml', 'xsl', 'xslt', 'xhtml'
    ];


    public int $qa_id = 0;

    /**
     * @OA\Property(
     *     description="첨부파일1",
     *     type="string",
     *     format="binary"
     * )
     */
    public UploadedFile $qa_file1;

    /**
     * @OA\Property(
     *     description="첨부파일2",
     *     type="string",
     *     format="binary"
     * )
     */
    public UploadedFile $qa_file2;

    /**
     * @OA\Property(
     *     description="파일1 삭제 여부",
     *     type="integer",
     *     enum={0, 1}
     * )
     */
    public int $qa_file_del1 = 0;

    /**
     * @OA\Property(
     *     description="파일2 삭제 여부",
     *     type="integer",
     *     enum={0, 1}
     * )
     */
    public int $qa_file_del2 = 0;


    /**
     * 파일 유효성 검사(오류, 크기, 확장자)
     */
    public function validateFile($file): void
    {
        $filename = $file->getClientFilename();
        $filesize = $file->getSize();
        // 파일 업로드 오류
        if ($file->getError()) {
            $this->throwException('파일 업로드 오류');
        }

        // 크기 체크
        $qa_service = new QaService();
        $qa_config = $qa_service->fetchQaConfig();
        if ($filesize > $qa_config['qa_upload_size']) {
            $this->throwException(
                sprintf(self::ERROR_FILE_SIZE, $filename, number_format($filesize), number_format($this->board['bo_upload_size']))
            );
        }

        // 확장자 체크
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        //php, php3, php4 ..php7 등 금지
        if (stripos($filename, '.php') !== false) {
            $this->throwException(sprintf(self::ERROR_FILE_EXT, $filename));
        }

        if (in_array($ext, $this->disallowed_ext)) {
            $this->throwException(sprintf(self::ERROR_FILE_EXT, $filename));
        }
    }

}