<?php

namespace API\Service;

use API\Database\Db;
use API\v1\Model\Response\Write\File;

class BoardFileService
{
    private $table;

    public function __construct()
    {
        global $g5;

        $this->table = $g5['board_file_table'];
    }
    /**
     * 게시글 파일 목록을 이미지와 파일로 분리하여 반환
     */
    public function getFilesByType(string $bo_table, int $wr_id, string $type)
    {
        $fetch_files = $this->fetchWriteFiles($bo_table, $wr_id);

        $images = [];
        $files = [];
        foreach ($fetch_files as $file) {
            if (preg_match("/\.(gif|jpg|jpeg|png|webp)$/i", $file['bf_file'])) {
                $images[] = new File($file);
            } else {
                $files[] = new File($file);
            }
        }
        return $type === 'image' ? $images : $files;
    }

    /**
     * 게시글 업로드 관련 파일&데이터 삭제
     */
    public function removeWriteFiles(string $bo_table, array $write)
    {
        $files = $this->fetchWriteFiles($bo_table, $write['wr_id']);

        foreach ($files as $file) {
            $delete_file = G5_DATA_PATH . '/file/' . $bo_table . '/' . str_replace('../', '', $file['bf_file']);
            if (file_exists($delete_file)) {
                @unlink($delete_file);
            }

            if (preg_match("/\.(gif|jpg|jpeg|png|webp)$/i", $file['bf_file'])) {
                delete_board_thumbnail($bo_table, $file['bf_file']);
            }
        }
        delete_editor_thumbnail($write['wr_content']);

        $this->deleteBoardFiles($bo_table, $write['wr_id']);
    }

    /**
     * 게시글 파일 목록 조회
     */
    public function fetchWriteFiles(string $bo_table, int $wr_id): array
    {
        $query = "SELECT * FROM {$this->table} WHERE bo_table = :bo_table AND wr_id = :wr_id ORDER BY bf_no";
        $stmt = Db::getInstance()->run($query, ['bo_table' => $bo_table, 'wr_id' => $wr_id]);

        return $stmt->fetchAll();
    }

    /**
     * 게시글 파일 삭제
     */
    public function deleteBoardFiles(string $bo_table, int $wr_id)
    {
        Db::getInstance()->delete($this->table, ['bo_table' => $bo_table, 'wr_id' => $wr_id]);
    }
}