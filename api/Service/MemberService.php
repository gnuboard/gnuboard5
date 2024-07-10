<?php

namespace API\Service;

use API\Database\Db;
use Psr\Http\Message\UploadedFileInterface;
use Exception;


class MemberService
{
    private const IMAGE_DIR = '/member_image';
    private const ICON_DIR = '/member';
    private $config;
    private $allowed_media_types = ['image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/x-png', 'image/png'];


    public function __construct($config)
    {
        $this->config = $config;
    }

    /**
     * 회원 이미지 경로 반환
     * TODO: 확장자에 대한 처리를 고민해봐야함.
     * @param string $mb_id 회원 아이디
     * @param string $type  이미지 타입 (icon, image)
     * @return string
     */
    public function getMemberImagePath(string $mb_id, string $type = 'image')
    {
        $dir = ($type == 'icon') ? self::ICON_DIR : self::IMAGE_DIR;
        $mb_dir = substr($mb_id, 0, 2);
        return G5_DATA_URL . $dir . '/' . $mb_dir . '/' . get_mb_icon_name($mb_id) . '.gif';
    }

    /**
     * 회원 이미지 업로드
     * @param string $mb_id
     * @param string $image_type
     * @param UploadedFileInterface $file
     * @return void
     * @throws Exception
     */
    public function updateMemberImage(string $mb_id, string $image_type, UploadedFileInterface $file = null)
    {
        if ($file->getError()) {
            return;
        }

        if ($image_type == "icon") {
            $type_string = "아이콘";
            $base_dir = self::ICON_DIR;
            $limit_size = $this->config['cf_member_icon_size'];
            $limit_width = $this->config['cf_member_icon_width'];
            $limit_height = $this->config['cf_member_icon_height'];
        } else {
            $type_string = "이미지";
            $base_dir = self::IMAGE_DIR;
            $limit_size = $this->config['cf_member_img_size'];
            $limit_width = $this->config['cf_member_img_width'];
            $limit_height = $this->config['cf_member_img_height'];
        }

        // 이미지파일 타입 검사
        if (!in_array($file->getClientMediaType(), $this->allowed_media_types)) {
            throw new Exception("gif, jpeg, png 이미지 파일만 업로드 가능합니다.");
        }

        // 이미지 크기 검사 (byte)
        if ($file->getSize() > $limit_size) {
            $limit = number_format($limit_size);
            throw new Exception("회원{$type_string}은(는) {$limit}바이트까지 업로드 가능합니다.");
        }

        // 이미지 경로 생성
        $file_dir = G5_DATA_PATH . $base_dir . "/" . substr($mb_id, 0, 2);
        $ext = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        $filename = get_mb_icon_name($mb_id);
        $file_fullname = $filename . "." . $ext;
        if (!is_dir($file_dir)) {
            @mkdir($file_dir, G5_DIR_PERMISSION);
            @chmod($file_dir, G5_DIR_PERMISSION);
        }

        if ($file->getError() === UPLOAD_ERR_OK) {
            moveUploadedFile($file_dir, $file, $filename);
        }

        // 이미지 가로 or 세로가 설정값보다 크면 썸네일 생성
        $origin_path = $file_dir . '/' . $file_fullname;
        if (file_exists($origin_path)) {
            $size = getimagesize($origin_path);
            if ($size[0] > $limit_width || $size[1] > $limit_height) {
                $thumb = thumbnail($file_fullname, $file_dir, $file_dir, $limit_width, $limit_height, true, true);
                $thumbnail_path = $file_dir . '/' . $thumb;

                if ($thumb) {
                    @unlink($origin_path);
                    @rename($thumbnail_path, $origin_path);
                } else {
                    @unlink($thumbnail_path);
                }
            }
        }
    }

    /**
     * 회원 이미지 삭제
     * @param string $mb_id
     * @param string $image_type
     * @return void
     */
    public function deleteMemberImage(string $mb_id, string $image_type)
    {
        $base_dir = ($image_type == "icon") ? self::ICON_DIR : self::IMAGE_DIR;
        $path = G5_DATA_PATH . $base_dir . "/" . substr($mb_id, 0, 2) . "/{$mb_id}.*";

        foreach (glob($path) as $filename) {
            @unlink($filename);
        }
    }

    /**
     * 회원탈퇴
     * - 실제로 삭제하지 않고 탈퇴일자 및 회원메모를 업데이트한다.
     * @param array $member
     * @throws Exception
     * @return void
     */
    public function leaveMember(array $member)
    {
        if ($this->config['cf_admin'] == $member['mb_id']) {
            throw new Exception("최고 관리자는 탈퇴할 수 없습니다.");
        }

        $update_data = [
            "mb_leave_date" => date("Ymd"),
            "mb_memo" => date('Ymd', G5_SERVER_TIME) . " 탈퇴함\n" . sql_real_escape_string($member['mb_memo']),
            "mb_certify" => '',
            "mb_adult" => 0,
            "mb_dupinfo" => ''
        ];
        $this->updateMember($member['mb_id'], $update_data);

        // Hook - 회원탈퇴
        run_event('member_leave', $member);

        //소셜로그인 해제
        if (function_exists('social_member_link_delete')) {
            social_member_link_delete($member['mb_id']);
        }
    }

    public function fetchMemberById(string $mb_id)
    {
        global $g5;

        $query = "SELECT * FROM {$g5['member_table']} WHERE mb_id = :mb_id";

        $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);

        return $stmt->fetch();
    }

    public function fetchAllMemberByEmail(string $mb_email)
    {
        global $g5;

        $query = "SELECT * FROM {$g5['member_table']} WHERE mb_email = :mb_email";

        $stmt = Db::getInstance()->run($query, ["mb_email" => $mb_email]);

        return $stmt->fetchAll();
    }

    public function insertMember(object $data)
    {
        global $g5;

        $data->mb_datetime = date('Y-m-d H:i:s', G5_SERVER_TIME);

        $insert_id = Db::getInstance()->insert($g5['member_table'], (array)$data);

        return $insert_id;
    }

    public function updateMember(string $mb_id, array $data): int
    {
        global $g5;

        $update_count = Db::getInstance()->update(
            $g5['member_table'],
            ["mb_id" => $mb_id],
            $data
        );

        return $update_count;
    }
}