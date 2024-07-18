<?php

namespace API\v1\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use API\Service\AuthenticationService;
use API\v1\Model\Request\Authentication\GenerateTokenRequest;
use API\v1\Model\Request\Authentication\RefreshTokenRequest;
use API\v1\Model\Response\Authentication\GenerateTokenResponse;


class AuthenticationController
{
    /**
     * @OA\Post(
     *     path="/api/v1/token",
     *     summary="Access/Refresh Token 발급",
     *     tags={"인증"},
     *     description="
Access Token & Refresh Token을 발급합니다.
- Access Token은 API 요청에 사용되며, 일정 시간 후 만료됩니다.
- Refresh Token은 Access Token 재발급에 필요하며 데이터베이스에 저장됩니다.
",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(ref="#/components/schemas/GenerateTokenRequest"),
     *          )
     *      ),
     *     @OA\Response(response="200", description="Access/Refresh Token 발급 성공", @OA\JsonContent(ref="#/components/schemas/GenerateTokenResponse")),
     *     @OA\Response(response="400", ref="#/components/responses/400"),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function generateToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $auth_service = new AuthenticationService();

        $request_body = $request->getParsedBody();
        $data = new GenerateTokenRequest($request_body);

        if (empty($data->username) || empty($data->password)) {
            return api_response_json($response, array(
                'message' => '아이디 또는 비밀번호가 입력되지 않았습니다.'
            ), 400);
        }

        $member = get_member($data->username);
        if (!$member || !login_password_check($member, $data->password, $member['mb_password'])) {
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

        // 기존 토큰 삭제 후 새로운 토큰 저장
        $auth_service->deleteRefreshToken($member['mb_id']);
        $auth_service->insertRefreshToken($member['mb_id'], $refresh_token, $refresh_token_decode);

        $response_data = new GenerateTokenResponse(
            [
                'access_token' => $access_token,
                'access_token_expire_at' => date('c', $access_token_decode->exp),
                'refresh_token' => $refresh_token,
                'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
                'token_type' => 'Bearer',
            ]
        );
        return api_response_json($response, (array)$response_data);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/token/refresh",
     *     summary="Access Token 재발급",
     *     tags={"인증"},
     *     description="
Refresh Token을 사용하여 새로운 Access Token을 발급합니다.
- Refresh Token도 함께 갱신되며 데이터베이스에 저장됩니다.
",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(ref="#/components/schemas/RefreshTokenRequest"),
     *          )
     *      ),
     *     @OA\Response(response="200", description="Access Token 재발급 성공", @OA\JsonContent(ref="#/components/schemas/GenerateTokenResponse")),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function refreshToken(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $auth_service = new AuthenticationService();

        $request_body = $request->getParsedBody();
        $data = new RefreshTokenRequest($request_body);

        $row = $auth_service->fetchRefreshToken($data->refresh_token);
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

        // 기존 토큰 갱신
        $auth_service->updateRefreshToken($row['mb_id'], $refresh_token, $refresh_token_decode);

        $response_data = new GenerateTokenResponse(
            [
                'access_token' => $access_token,
                'access_token_expire_at' => date('c', $access_token_decode->exp),
                'refresh_token' => $refresh_token,
                'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
                'token_type' => 'Bearer',
            ]
        );
        return api_response_json($response, (array)$response_data);
    }
}