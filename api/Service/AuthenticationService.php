<?php

namespace API\Service;

use API\Database\Db;
use stdClass;

class AuthenticationService
{
    /**
     * Refresh Token 조회
     * @param string $refresh_token  Refresh Token
     * @return mixed
     */
    public function fetchRefreshToken(string $refresh_token): mixed
    {
        global $g5;

        $query = "SELECT * FROM {$g5['member_refresh_token_table']} WHERE refresh_token = :refresh_token";
        $stmt = Db::getInstance()->run($query, ["refresh_token" => $refresh_token]);

        return $stmt->fetch();
    }

    /**
     * Refresh Token 저장
     * @param string $mb_id  회원 아이디
     * @param string $refresh_token  Refresh Token
     * @param stdClass $decode  Refresh Token 디코딩 정보
     */
    public function insertRefreshToken(string $mb_id, string $refresh_token, stdClass $decode): void
    {
        global $g5;

        $data = [
            "mb_id" => $mb_id,
            "refresh_token" => $refresh_token,
            "expires_at" => date('Y-m-d H:i:s', $decode->exp),
            "created_at" => date('Y-m-d H:i:s', $decode->iat),
            "updated_at" => date('Y-m-d H:i:s', $decode->iat),
        ];
        Db::getInstance()->insert($g5['member_refresh_token_table'], $data);
    }

    /**
     * Refresh Token 갱신
     * @param string $mb_id  회원 아이디
     * @param string $refresh_token  새로운 Refresh Token
     * @param stdClass $decode  Refresh Token 디코딩 정보
     * @return void
     */
    public function updateRefreshToken(string $mb_id, string $refresh_token, stdClass $decode): void
    {
        global $g5;

        $data = [
            "refresh_token" => $refresh_token,
            "expires_at" => date('Y-m-d H:i:s', $decode->exp),
            "updated_at" => date('Y-m-d H:i:s', $decode->iat),
        ];
        Db::getInstance()->update($g5['member_refresh_token_table'], ["mb_id" => $mb_id], $data);
    }

    /**
     * Refresh Token 삭제
     * @param string $mb_id  회원 아이디
     * @return void
     */
    public function deleteRefreshToken(string $mb_id): void
    {
        global $g5;

        Db::getInstance()->delete($g5['member_refresh_token_table'], ["mb_id" => $mb_id]);
    }
}