<?php

namespace API\Service;

use API\Database\Db;

/**
 * Qa (1:1 문의)
 */
class QaService
{
    private $qa_table;
    private $qa_config_table;
    private $qa_folder = 'qa';

    public function __construct()
    {
        $this->qa_config_table = $GLOBALS['g5']['qa_config_table'];
        $this->qa_table = $GLOBALS['g5']['qa_content_table'];
    }


    /**
     * 1:1 문의 목록 조회
     * @param string $mb_id
     * @param ?string $category
     * @param int $page
     * @param int $per_page
     * @return array
     */
    public function getQaList($mb_id, $category, $stx, $sfl, $page, $per_page)
    {
        if ($category === null) {
            $category = '';
        }

        $query = "SELECT * FROM $this->qa_table WHERE mb_id = :mb_id AND qa_category = :qa_category ORDER BY qa_id DESC LIMIT :offset, :limit";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'qa_category' => $category,
            'offset' => ($page - 1) * $per_page,
            'limit' => $per_page
        ]);

        return $stmt->fetchAll() ?: [];
    }

    /**
     * @param string $mb_id
     * @param int $qa_id
     * @param string $reply_content
     * @return void
     */
    public function reply($mb_id, $qa_id, $reply_content)
    {
        Db::getInstance()->insert($this->qa_table, [
            'qa_id' => $qa_id,
            'mb_id' => $mb_id,
            'qa_type' => 1,
            'qa_content' => $reply_content,
            'qa_datetime' => G5_TIME_YMDHIS
        ]);
    }

    /**
     * @param string $mb_id
     * @param string $category
     * @param string $stx
     * @param string $sfl
     * @return int
     */
    public function fetchCountQaList($mb_id, $category, $stx, $sfl)
    {
        if ($category === null) {
            $category = '';
        }

        $query = "SELECT count(*) FROM $this->qa_table WHERE mb_id = :mb_id AND qa_category = :qa_category";
        $stmt = Db::getInstance()->run($query, [
            'mb_id' => $mb_id,
            'qa_category' => $category
        ]);

        return $stmt->fetchColumn() ?: 0;
    }

    public function getCategory()
    {
        $qa_config = $this->fetchQaConfig();
        return explode('|', $qa_config['qa_category']);
    }

    /**
     * QA 1:1 문의 설정 조회
     * @return array
     */
    public function fetchQaConfig()
    {
        $query = "SELECT * FROM {$this->qa_config_table}";
        $stmt = Db::getInstance()->run($query);
        return $stmt->fetch() ?: [];
    }

    /**
     * 답변 달렸는지 체크
     * @param $qa_id
     * @return bool
     */
    public function checkIsReplied($qa_id)
    {
        $result = $this->fetchQa($qa_id);
        if ($result['qa_type'] == 0 && $result['qa_status'] == 1) {
            return true;
        }

        return false;
    }

    public function fetchQa($qa_id)
    {
        $query = "SELECT * FROM $this->qa_table WHERE qa_id = :qa_id";
        $stmt = Db::getInstance()->run($query, ['qa_id' => $qa_id]);
        return $stmt->fetch();
    }

    public function fetchlastQaNum()
    {
        $query = "SELECT qa_num FROM $this->qa_table ORDER BY qa_num asc LIMIT 1";
        $stmt = Db::getInstance()->run($query);
        return $stmt->fetchColumn();
    }

    public function createQa(array $data)
    {
        $qa_num = (int)$this->fetchlastQaNum();
        --$qa_num;

        $last_insert_id = Db::getInstance()->insert($this->qa_table, [
            'qa_category' => $data['qa_category'],
            'qa_num' => $qa_num,
            'qa_hp' => $data['qa_hp'],
            'qa_email' => $data['qa_email'],
            'qa_name' => $data['qa_name'],
            'qa_html' => $data['qa_html'],
            'qa_type' => $data['qa_type'] ?? 0,
            'qa_subject' => $data['qa_subject'],
            'qa_content' => $data['qa_content'],
            'qa_email_recv' => $data['qa_email_recv'],
            'qa_sms_recv' => $data['qa_sms_recv'],
            'qa_datetime' => G5_TIME_YMDHIS
        ]);

        return $last_insert_id;
    }

    public function updateQa($qa_id, array $update_data)
    {
        $params = [];

        foreach ($update_data as $key => $value) {
            $params["{$key}"] = $value;
        }

        $params['qa_id'] = $qa_id;
        Db::getInstance()->update($this->qa_table, $params, ['qa_id' => $qa_id]);

    }

    public function updateQaRelate($qa_id)
    {
        Db::getInstance()->update($this->qa_table, ['qa_relate' => $qa_id], ['qa_parent' => $qa_id]);
    }

    public function fileUpload(array $data)
    {
        $qa_config = $this->fetchQaConfig();
        $files = [];

        if (isset($data['qa_file1']) && $data['qa_file1']) {
            $files[] = $data['qa_file1'];
        }

        if (isset($data['qa_file2']) && $data['qa_file2']) {
            $files[] = $data['qa_file2'];
        }

        foreach ($files as $file) {
            $mime_type = $file->getClientMediaType();

            if (strpos($mime_type, 'image') !== false) {
                $image_size = getimagesize($file->getFilePath());
                if (!$image_size) {
                    continue;
                }

                if ($file->getSize() > $qa_config['qa_upload_size']) {
                    continue;
                }
            }

            $directory = G5_DATA_PATH . '/file/' . $this->qa_folder;
            $file_name = moveUploadedFile($directory, $file);

            if ($data['qa_file1'] === $file) {
                $data['qa_source1'] = $file_name;
            }

            if ($data['qa_file2'] === $file) {
                $data['qa_source2'] = $file_name;
            }

            $this->recordFileUpload($data);
        }
    }

    public function deleteFiles(array $data)
    {
        $files = [];
        if (isset($data['qa_file_del1']) && $data['qa_file_del1']) {
            $files = ['qa_file1'];
        }

        if (isset($data['qa_file_del1']) && $data['qa_file_del1']) {
            $files = ['qa_file2'];
        }

        foreach ($files as $file) {
            $directory = G5_DATA_PATH . '/file/' . $this->qa_folder;
            unlink($directory . '/' . $file);
        }

    }

    public function recordFileUpload($data)
    {
        $update_param = [];

        if (isset($data['qa_file1']) && $data['qa_file1']) {
            $update_param['qa_file1'] = $data['qa_file1'];
            $update_param['qa_source1'] = $data['qa_source1'];
        }

        if (isset($data['qa_file2']) && $data['qa_file2']) {
            $update_param['qa_file2'] = $data['qa_file2'];
            $update_param['qa_source2'] = $data['qa_source2'];
        }

        if ($update_param) {
            Db::getInstance()->update($this->qa_table, $update_param,
                ['qa_id' => $data['qa_id']]
            );
        }
    }

    /**
     * 파일 DB 레코드 삭제
     * @param $qa_data
     * @return void
     */
    public function deleteFileRecord($qa_data)
    {
        $where_param = [
            'qa_id' => $qa_data['qa_id'],
        ];

        $update_param = [];

        if ($qa_data['qa_file_del1']) {
            $update_param['qa_file1'] = '';
            $update_param['qa_source1'] = '';
        }

        if ($qa_data['qa_file_del2']) {
            $update_param['qa_file2'] = '';
            $update_param['qa_source2'] = '';
        }

        if ($update_param) {
            Db::getInstance()->update($this->qa_table, $update_param, $where_param);
        }
    }

    public function deleteQa(array $qa)
    {
        Db::getInstance()->delete($this->qa_table, ['qa_id' => $qa['qa_id']]);
    }
}                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               