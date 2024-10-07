<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\File;

class BoardFileService
{
    public string $bo_table;
    public array $board;
    public string $table;

    public function __construct()
    {
        global $g5;
        $this->table = $g5['board_file_table'];
    }

    /**
     * BoardMiddleware 미들웨어에서 지정합니다.
     * @param array $board
     * @return void
     */
    public function setBoard(array $board): void
    {
        $this->board = $board;
        $this->setBoTable($board['bo_table']);
    }

    /**
     * 게시판 테이블명 지정
     * @param string $bo_table
     * @return void
     */
    public function setBoTable(string $bo_table): void
    {
        $this->bo_table = $bo_table;
    }

    /**
     *  게시글 파일 목록을 이미지와 파일로 분리하여 반환 url 을 생성합니다.
     * @param int $wr_id
     * @param string $type
     * @return File[]
     */
    public function getFilesByType(int $wr_id, string $type)
    {
        static $fetch_files = [];
        if (!isset($fetch_files[$wr_id])) {
            $result = $this->fetchWriteFiles($wr_id);
            if (!$result) {
                return [];
            }
            $fetch_files[$wr_id] = $result;
        }

        $images = [];
        $files = [];
        $allow_images = allow_images();

        foreach ($fetch_files as $file) {
            if (isset($allow_images[$file['bf_type']])) {
                $images[] = $file;
            } else {
                $files[] = $file;
            }
        }
        return $type === 'image' ? $images : $files;
    }

    /**
     *  게시글 파일 목록 조회
     */
    public function fetchWriteFiles(int $wr_id): array
    {
        $values = ['bo_table' => $this->bo_table, 'wr_id' => $wr_id];
        $query = "SELECT * FROM {$this->table} WHERE bo_table = :bo_table AND wr_id = :wr_id ORDER BY bf_no";
        return Db::getInstance()->run($query, $values)->fetchAll() ?: [];
    }

    /**
     * 게시글 파일 조회 (bf_no)
     */
    public function fetchWriteFileByNo(int $wr_id, int $bf_no): array
    {
        $values = ['bo_table' => $this->bo_table, 'wr_id' => $wr_id, 'bf_no' => $bf_no];
        $query = "SELECT * FROM {$this->table} WHERE bo_table = :bo_table AND wr_id = :wr_id AND bf_no = :bf_no";
        $stmt = Db::getInstance()->run($query, $values);

        return $stmt->fetch() ?: [];
    }

    /**
     * 게시글 파일 목록 조회 (bf_no)
     */
    public function fetchWriteFilesByIn(int $wr_id, array $bf_no): array
    {
        if (empty($bf_no)) {
            return [];
        }

        $values = ['bo_table' => $this->bo_table, 'wr_id' => $wr_id];
        $placeholders = implode(',', array_map(function ($key) {
            return ':bf_no_' . $key;
        }, array_keys($bf_no)));

        foreach ($bf_no as $key => $value) {
            $values['bf_no_' . $key] = $value;
        }

        $query = "SELECT * FROM {$this->table} WHERE bo_table = :bo_table AND wr_id = :wr_id AND bf_no IN ($placeholders)";
        $stmt = Db::getInstance()->run($query, $values);

        return $stmt->fetchAll() ?: [];
    }

    /**
     *  게시글 파일 업로드
     * @param int $wr_id
     * @param array $upload_files
     * @return void
     * @throws \Random\RandomException
     */
    public function uploadFiles(int $wr_id, array $upload_files)
    {
        $this->createDirectoryIfNotExists();

        $files = $upload_files['files'] ?? [];
        foreach ($files as $key => $file) {
            if ($file->getError()) {
                continue;
            }

            $mime_type = $file->getClientMediaType();
            if (strpos($mime_type, 'image') !== false) {
                $timg = getimagesize($file->getFilePath());
                if (!$timg) {
                    continue;
                }

                // 이미지 확장자 속여서 악성코드 업로드 하는 경우를 방지
                if ($timg[2] < 1 || $timg[2] > 18) {
                    continue;
                }
            }

            $exists_file = $this->fetchWriteFileByNo($wr_id, $key);
            if ($exists_file) {
                $this->removeFileAndThumbnail($exists_file);
            }

            $directory = G5_DATA_PATH . '/file/' . $this->bo_table;
            $filename = moveUploadedFile($directory, $file);

            // 올라간 파일의 퍼미션을 변경합니다.
            chmod($directory . '/' . $filename, G5_FILE_PERMISSION);

            if ($exists_file) {
                $this->updateBoardFile($wr_id, $key, $file, $filename, $upload_files['file_contents'][$key] ?? '');
            } else {
                $this->insertBoardFile($wr_id, $key, $file, $filename, $upload_files['file_contents'][$key] ?? '');
            }
        }
    }

    /**
     * 파일 업로드 경로 생성
     * @return void
     */
    public function createDirectoryIfNotExists()
    {
        $directory = G5_DATA_PATH . '/file/' . $this->bo_table;
        if (!is_dir($directory)) {
            @mkdir($directory, G5_DIR_PERMISSION);
            @chmod($directory, G5_DIR_PERMISSION);
        }
    }

    /**
     * 파일 업로드 데이터 삽입
     * @return void
     */
    public function insertBoardFile(int $wr_id, int $bf_no, object $file, string $filename, string $file_content)
    {
        $values = [
            'bo_table' => $this->bo_table,
            'wr_id' => $wr_id,
            'bf_no' => $bf_no,
            'bf_source' => $file->getClientFilename(),
            'bf_file' => $filename,
            'bf_content' => $file_content,
            'bf_filesize' => $file->getSize(),
            'bf_width' => 0,
            'bf_height' => 0,
            'bf_type' => $file->getClientMediaType(),
            'bf_datetime' => G5_TIME_YMDHIS,
        ];
        Db::getInstance()->insert($this->table, $values);
    }

    /**
     * 파일 업로드 데이터 갱신
     * return void
     */
    public function updateBoardFile(int $wr_id, int $bf_no, object $file, string $filename, string $file_content)
    {
        $values = [
            'bf_source' => $file->getClientFilename(),
            'bf_file' => $filename,
            'bf_content' => $file_content,
            'bf_filesize' => $file->getSize(),
            'bf_width' => 0,
            'bf_height' => 0,
            'bf_type' => $file->getClientMediaType(),
            'bf_datetime' => G5_TIME_YMDHIS,
        ];
        $where = ['bo_table' => $this->bo_table, 'wr_id' => $wr_id, 'bf_no' => $bf_no];
        Db::getInstance()->update($this->table, $values, $where);
    }

    /**
     * 게시글의 모든 파일&데이터 삭제
     * return void
     */
    public function deleteWriteFiles(array $write)
    {
        $files = $this->fetchWriteFiles($write['wr_id']);
        foreach ($files as $file) {
            $this->removeFileAndThumbnail($file);
        }
        delete_editor_thumbnail($write['wr_content']);
        $this->deleteBoardFile($write['wr_id']);
    }

    /**
     * 파일 업로드 시 기존 파일 삭제
     * return void
     */
    public function deleteWriteFilesByNo(int $wr_id, array $file_dels)
    {
        $files = $this->fetchWriteFilesByIn($wr_id, $file_dels);
        foreach ($files as $file) {
            $this->removeFileAndThumbnail($file);
            $this->deleteBoardFileByNo($wr_id, $file['bf_no']);
        }
    }

    /**
     * 게시글 파일 삭제
     * return void
     */
    public function deleteBoardFile(int $wr_id): void
    {
        $values = ['bo_table' => $this->bo_table, 'wr_id' => $wr_id];
        Db::getInstance()->delete($this->table, $values);
    }

    /**
     * 게시글 파일 삭제(bf_no)
     * return void
     */
    public function deleteBoardFileByNo(int $wr_id, int $bf_no): void
    {
        $values = ['bo_table' => $this->bo_table, 'wr_id' => $wr_id, 'bf_no' => $bf_no];
        Db::getInstance()->delete($this->table, $values);
    }

    /**
     * 파일 삭제 시 썸네일 삭제
     * return void
     */
    private function removeFileAndThumbnail(array $bf_file)
    {
        $delete_file = G5_DATA_PATH . '/file/' . $this->bo_table . '/' . str_replace('../', '', $bf_file['bf_file']);
        if (file_exists($delete_file)) {
            @unlink($delete_file);
        }

        $allow_images = allow_images();
        if (isset($allow_images[$bf_file['bf_type']])) {
            delete_editor_thumbnail($bf_file['bf_content']);
        }
    }
}
