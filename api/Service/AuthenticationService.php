<?php

namespace API\Service;

use API\Auth\JwtTokenManager;
use API\Database\Db;
use stdClass;


class AuthenticationService
{
    private string $table;
    private JwtTokenManager $token_manager;

    public function __construct(JwtTokenManager $token_manager)
    {
        $this->token_manager = $token_manager;
        $this->table = $GLOBALS['g5']['member_refresh_token_table'] ?? G5_TABLE_PREFIX . 'member_refresh_token';
    }

    /**
     * 로그인시 토큰 발행에 사용됩니다
     *
     * ! 비밀번호, 소셜 로그인등 인증이 완료된 회원의 아이디로 토큰을 발행해야됩니다
     *
     * @param string $auth_mb_id 승인된 회원 아이디
     * @return array
     */
    public function generateLoginTokenByAuthMemberId(string $auth_mb_id): array
    {
        $claim = ['sub' => $auth_mb_id];
        $login_access_token = $this->token_manager->create_token('access', $claim);
        $access_token_decode = $this->token_manager->decode_token('access', $login_access_token);
        $login_refresh_token = $this->token_manager->create_token('refresh', $claim);
        $refresh_token_decode = $this->token_manager->decode_token('refresh', $login_refresh_token);

        $this->insertRefreshToken($auth_mb_id, $login_refresh_token, $refresh_token_decode);
        return [
            'access_token' => $login_access_token,
            'access_token_expire_at' => date('c', $access_token_decode->exp),
            'refresh_token' => $login_refresh_token,
            'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
            'token_type' => 'Bearer',
        ];
    }

    /**
     * Refresh Token 조회
     * @param string $refresh_token Refresh Token
     * @return array|false
     */
    public function fetchRefreshToken(string $refresh_token)
    {
        $query = "SELECT * FROM {$this->table} WHERE refresh_token = :refresh_token";
        $stmt = Db::getInstance()->run($query, ['refresh_token' => $refresh_token]);

        return $stmt->fetch();
    }

    /**
     * Refresh Token 저장
     * @param string $mb_id 회원 아이디
     * @param string $refresh_token Refresh Token
     * @param stdClass $decode Refresh Token 디코딩 정보
     */
    public function insertRefreshToken(string $mb_id, string $refresh_token, stdClass $decode): void
    {
        $data = [
            'mb_id' => $mb_id,
            'refresh_token' => $refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $decode->exp),
            'created_at' => date('Y-m-d H:i:s', $decode->iat),
            'updated_at' => date('Y-m-d H:i:s', $decode->iat),
        ];
        Db::getInstance()->insert($this->table, $data);
    }

    /**
     * Refresh Token 갱신
     * @param string $mb_id 회원 아이디
     * @param string $refresh_token 새로운 Refresh Token
     * @param stdClass $decode Refresh Token 디코딩 정보
     * @return void
     */
    public function updateRefreshToken(string $mb_id, string $refresh_token, stdClass $decode): void
    {
        $data = [
            'refresh_token' => $refresh_token,
            'expires_at' => date('Y-m-d H:i:s', $decode->exp),
            'updated_at' => date('Y-m-d H:i:s', $decode->iat),
        ];
        Db::getInstance()->update($this->table, $data, ['mb_id' => $mb_id]);
    }

    /**
     * Refresh Token 삭제
     * @param string $mb_id 회원 아이디
     * @return void
     */
    public function deleteRefreshToken(string $mb_id): void
    {
        Db::getInstance()->delete($this->table, ['mb_id' => $mb_id]);
    }
}
