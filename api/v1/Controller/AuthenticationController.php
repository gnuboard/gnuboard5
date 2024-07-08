<?php

namespace API\v1\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;


class AuthenticationController
{
    /**
     * @OA\Post(
     *     path="/api/v1/token",
     *     summary="Access/Refresh Token 발급",
     *     tags={"인증"},
     *     description="Access Token & Refresh Token을 발급합니다.",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function generateToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        global $g5; 

        # 로그인 인증
        $data = $request->getParsedBody();
        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!(isset($username) && isset($password))) {
            return api_response_json($response, array(
                'message' => '아이디 또는 비밀번호가 입력되지 않았습니다.'
            ), 400);
        }

        $member = get_member($username);
        if (!$member || !login_password_check($member, $password, $member['mb_password'])) {
            return api_response_json($response, array(
                'message' => '아이디 또는 비밀번호가 일치하지 않습니다.'
            ), 403);
        }

        // 탈퇴 또는 차단된 아이디인가?
        if (($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME))
            || ($member['mb_leave_date'] && $member['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME))
        ) {
            return api_response_json($response, array(
                'message' => '현재 로그인 회원은 탈퇴 또는 차단된 회원입니다.'
            ), 403);
        }

        // 메일인증 설정이 되어 있다면
        if (is_use_email_certify() && !preg_match("/[1-9]/", $member['mb_email_certify'])) {
            return api_response_json($response, array(
                'message' => "{$member['mb_email']} 메일로 메일인증을 받으셔야 로그인 가능합니다."
            ), 403);
        }

        // 토큰 생성
        $claim = array('sub' => $member['mb_id']);
        $access_token = create_token('access', $claim);
        $refresh_token = create_token('refresh', $claim);

        // 토큰 디코딩
        $access_token_decode = decode_token('access', $access_token);
        $refresh_token_decode = decode_token('refresh', $refresh_token);

        # 기존 토큰 삭제
        $sql = "DELETE FROM {$g5['member_refresh_token_table']} WHERE mb_id = '{$member['mb_id']}'";
        sql_query($sql);

        # 새로운 토큰 저장
        $sql = "INSERT INTO {$g5['member_refresh_token_table']} SET
                    mb_id = '{$member['mb_id']}',
                    refresh_token = '{$refresh_token}',
                    expires_at = '" . date('Y-m-d H:i:s', $refresh_token_decode->exp) . "',
                    created_at = '" . date('Y-m-d H:i:s', $refresh_token_decode->iat) . "',
                    updated_at = '" . date('Y-m-d H:i:s', $refresh_token_decode->iat) . "'";
        sql_query($sql);

        return api_response_json($response, array(
            'access_token' => $access_token,
            'access_token_expire_at' => date('c', $access_token_decode->exp),
            'refresh_token' => $refresh_token,
            'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
            'token_type' => 'Bearer',
        ));
    }

    /**
     * @OA\Post(
     *     path="/api/v1/token/refresh",
     *     summary="Refresh Token 재발급",
     *     tags={"인증"},
     *     description="Refresh Token을 이용하여 Access Token을 재발급합니다.",
     *     @OA\Response(response="200", description="")
     * )
     */
    public function refreshToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        
        global $g5;

        $data = $request->getParsedBody();
        $refresh_token = $data['refresh_token'] ?? null;
        $refresh_token_decode = decode_token('refresh', $refresh_token);

        $sql = "SELECT * FROM {$g5['member_refresh_token_table']} WHERE refresh_token = '{$data['refresh_token']}'";
        $row = sql_fetch($sql);
        if (!$row) {
            return api_response_json($response, array('message' => '토큰이 존재하지 않습니다.'), 403);
        }

        // 토큰 재생성
        $claim = array('sub' => $row['mb_id']);
        $access_token = create_token('access', $claim);
        $refresh_token = create_token('refresh', $claim);

        // 토큰 디코딩
        $access_token_decode = decode_token('access', $access_token);
        $refresh_token_decode = decode_token('refresh', $refresh_token);

        # 기존 토큰 갱신
        $sql = "UPDATE {$g5['member_refresh_token_table']} SET
                    refresh_token = '{$refresh_token}',
                    expires_at = '" . date('Y-m-d H:i:s', $refresh_token_decode->exp) . "',
                    updated_at = '" . date('Y-m-d H:i:s', $refresh_token_decode->iat) . "'
                WHERE mb_id = '{$row['mb_id']}'";
        sql_query($sql);

        return api_response_json($response, array(
            'access_token' => $access_token,
            'access_token_expire_at' => date('c', $access_token_decode->exp),
            'refresh_token' => $refresh_token,
            'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
            'token_type' => 'Bearer',
        ));
    }
}