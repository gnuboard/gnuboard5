<?php

namespace API\Service;

use Psr\Http\Message\UploadedFileInterface;
use Exception;

require_once G5_LIB_PATH . '/thumbnail.lib.php';

class MemberImageService
{
    private const IMAGE_DIR = '/member_image';
    private const ICON_DIR = '/member';
    private array $allowed_media_types = ['image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/x-png', 'image/png'];

    /**
     * 회원 이미지 파일을 체크 후 경로 반환
     * @param string $mb_id 회원 아이디
     * @param string $type 이미지 타입 (icon, image)
     * @return string 파일이 없으면 ''
     */
    public function getMemberImagePath(string $mb_id, string $type = 'image')
    {
        if(!$mb_id) {
            return '';
        }
        $dir = ($type === 'icon') ? self::ICON_DIR : self::IMAGE_DIR;
        $mb_dir = substr($mb_id, 0, 2);
        $path = G5_DATA_PATH . "{$dir}/{$mb_dir}/{$mb_id}.gif";
        if(file_exists($path)) {
            return G5_DATA_URL . "{$dir}/{$mb_dir}/{$mb_id}.gif";
        }
        return '';
    }

    /**
     * 회원 이미지 업로드
     * @param array $config
     * @param string $mb_id 회원 아이디
     * @param string $image_type 이미지 용도 (icon, image)
     * @param UploadedFileInterface $file 업로드 파일
     * @return void
     * @throws \RandomException
     */
    public function updateMemberImage(array $config, string $mb_id, string $image_type, UploadedFileInterface $file)
    {
        if ($file->getError()) {
            return;
        }

        if ($image_type === "icon") {
            $type_string = "아이콘";
            $base_dir = self::ICON_DIR;
            $limit_size = $config['cf_member_icon_size'];
            $limit_width = $config['cf_member_icon_width'];
            $limit_height = $config['cf_member_icon_height'];
        } else {
            $type_string = "이미지";
            $base_dir = self::IMAGE_DIR;
            $limit_size = $config['cf_member_img_size'];
            $limit_width = $config['cf_member_img_width'];
            $limit_height = $config['cf_member_img_height'];
        }

        // 이미지파일 확장자 검사
        if (!in_array($file->getClientMediaType(), $this->allowed_media_types)) {
            throw new Exception("gif, jpeg, png 이미지 파일만 업로드 가능합니다.", 404);
        }

        // 이미지 크기 검사 (byte)
        if ($file->getSize() > $limit_size) {
            $limit = number_format($limit_size);
            throw new Exception("회원{$type_string}은(는) {$limit}바이트까지 업로드 가능합니다.", 404);
        }

        // 이미지 경로 생성
        $file_dir = G5_DATA_PATH . $base_dir . "/" . substr($mb_id, 0, 2);
        $filename = $mb_id;
        $file_fullname = $filename . "." . 'gif'; // 그누보드 5 와 호환성 유지를 위해 gif 확장자 사용
        if (!is_dir($file_dir)) {
            @mkdir($file_dir, G5_DIR_PERMISSION);
            @chmod($file_dir, G5_DIR_PERMISSION);
        }

        if ($file->getError() === UPLOAD_ERR_OK) {
            $file->moveTo($file_dir . '/' . $file_fullname);
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
     * @param string $mb_id 회원 아이디
     * @param string $image_type 이미지 타입 (icon, image)
     * @return void
     */
    public function deleteMemberImage(string $mb_id, string $image_type)
    {
        $base_dir = ($image_type === "icon") ? self::ICON_DIR : self::IMAGE_DIR;
        $path = G5_DATA_PATH . $base_dir . "/" . substr($mb_id, 0, 2) . "/{$mb_id}.*";

        foreach (glob($path) as $filename) {
            @unlink($filename);
        }
    }
}
