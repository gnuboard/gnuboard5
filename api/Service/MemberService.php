<?php

namespace API\Service;

use API\Database\Db;
use Psr\Http\Message\UploadedFileInterface;
use Exception;

class MemberService
{
    private const IMAGE_DIR = '/member_image';
    private const ICON_DIR = '/member';
    private $allowed_media_types = ['image/gif', 'image/jpeg', 'image/jpg', 'image/pjpeg', 'image/x-png', 'image/png'];

    private string $table;
    private ConfigService $config_service;

    public function __construct(ConfigService $config_service)
    {
        global $g5;
        $this->table = $g5['member_table'];
        $this->config_service = $config_service;
    }

    /**
     * 회원가입 처리
     * @param object $data 회원가입 데이터
     * @throws Exception 회원가입 실패시 Exception 발생
     * @return int 회원번호
     */
    public function createMember(object $data): int
    {
        $config = $this->config_service->getConfig();

        if ($this->fetchMemberById($data->mb_id)) {
            throw new Exception("이미 사용중인 회원아이디 입니다.", 409);
        }
        if ($this->existsMemberByNick($data->mb_nick, $data->mb_id)) {
            throw new Exception("이미 사용중인 닉네임 입니다.", 409);
        }
        if ($this->existsMemberByEmail($data->mb_email, $data->mb_id)) {
            throw new Exception("이미 사용중인 이메일 입니다.", 409);
        }
        if ($config['cf_use_recommend'] && $data->mb_recommend) {
            if (!$this->fetchMemberById($data->mb_recommend)) {
                throw new Exception("추천인이 존재하지 않습니다.", 404);
            }
        }

        return $this->insertMember((array)$data);
    }

    /**
     * 회원정보 갱신 처리
     * @param string $mb_id 회원아이디
     * @param object $data 갱신 데이터
     * @throws Exception 갱신 실패시 Exception 발생
     * @return void
     */
    public function updateMemberProfile(string $mb_id, object $data): void
    {
        if ($this->existsMemberByNick($data->mb_nick, $data->mb_id)) {
            throw new Exception("이미 사용중인 닉네임 입니다.", 409);
        }
        if ($this->existsMemberByEmail($data->mb_email, $data->mb_id)) {
            throw new Exception("이미 사용중인 이메일 입니다.", 409);
        }

        $this->updateMember($mb_id, (array)$data);
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
    public function updateMemberImage(array $config, string $mb_id, string $image_type, UploadedFileInterface $file = null)
    {
        if ($file->getError()) {
            return;
        }

        if ($image_type == "icon") {
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
     * 회원정보 조회 검증
     * @param array $member 회원정보
     * @param array $login_member 로그인 회원정보
     * @throws Exception 조회 실패시 Exception 발생
     */
    public function verifyMemberProfile(array $member, array $login_member): void
    {
        if (!$member) {
            throw new Exception("회원정보가 존재하지 않습니다.", 404);
        }
        if ($login_member['mb_id'] != $member['mb_id']) {
            if (!$login_member['mb_open']) {
                throw new Exception("자신의 정보를 공개하지 않으면 다른분의 정보를 조회할 수 없습니다.", 403);
            }
            if (!$member['mb_open']) {
                throw new Exception("해당 회원은 정보공개를 하지 않았습니다.", 403);
            }
        }
    }
    
    /**
     * 인증 이메일 변경정보 검증
     * @param array|bool $member 회원정보
     * @param object $data 변경정보
     * @throws Exception 변경정보 검증 실패시 Exception 발생
     */
    public function verifyEmailCertification($member, object $data): void
    {
        if (!$member) {
            throw new Exception("회원정보가 존재하지 않습니다.", 404);
        }
        if (!check_password($data->password, $member['mb_password'])) {
            throw new Exception("비밀번호가 일치하지 않습니다.", 403);
        }
        if (substr($member["mb_email_certify"], 0, 1) != '0') {
            throw new Exception("이미 메일인증 하신 회원입니다.", 409);
        }
        if ($this->existsMemberByEmail($data->email, $member['mb_id'])) {
            throw new Exception("이미 사용중인 이메일 입니다.", 409);
        }
    }

    public function verifyPasswordResetEmail(string $email): array
    {
        $members = $this->fetchAllMemberByEmail($email);
        $count = count($members);

        if ($count > 1) {
            throw new Exception("동일한 메일주소가 2개 이상 존재합니다. 관리자에게 문의하여 주십시오.", 409);
        } elseif ($count == 0) {
            throw new Exception("입력한 정보로 등록된 회원을 찾을 수 없습니다.", 404);
        }
        
        return $members[0];
    }

    public function fetchAllMemberByEmail(string $mb_email)
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_email = :mb_email";

        $stmt = Db::getInstance()->run($query, ["mb_email" => $mb_email]);
        
        return $stmt->fetchAll();
    }

    /**
     * @param string $mb_id
     * @return array|false
     */
    public function fetchMemberById(string $mb_id)
    {
        $query = "SELECT * FROM {$this->table} WHERE mb_id = :mb_id";

        $stmt = Db::getInstance()->run($query, ["mb_id" => $mb_id]);
        
        return $stmt->fetch();
    }

    public function existsMemberByNick(string $mb_nick, string $mb_id): bool
    {
        $query = "SELECT count(*) as cnt
                    FROM {$this->table}
                    WHERE mb_nick = :mb_nick
                    AND mb_id <> :mb_id";

        $stmt = Db::getInstance()->run($query, [
            "mb_nick" => $mb_nick,
            "mb_id" => $mb_id
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function existsMemberByEmail(string $mb_email, string $mb_id): bool
    {
        $query = "SELECT count(*) as cnt
                    FROM {$this->table}
                    WHERE mb_email = :mb_email
                    AND mb_id <> :mb_id";

        $stmt = Db::getInstance()->run($query, [
            "mb_email" => $mb_email,
            "mb_id" => $mb_id
        ]);

        return $stmt->fetchColumn() > 0;
    }

    public function insertMember(array $data): int
    {
        $insert_id = Db::getInstance()->insert($this->table, $data);

        return $insert_id;
    }

    public function updateMember(string $mb_id, array $data): int
    {
        $update_count = Db::getInstance()->update($this->table, ["mb_id" => $mb_id], $data);

        return $update_count;
    }
}
