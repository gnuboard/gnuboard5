<?php

namespace API\v1\Controller;

use API\Auth\JwtTokenManager;
use API\Exceptions\HttpForbiddenException;
use API\Exceptions\HttpNotFoundException;
use API\Exceptions\HttpUnauthorizedException;
use API\Service\AuthenticationService;
use API\Service\MemberService;
use API\v1\Model\Request\Authentication\GenerateTokenRequest;
use API\v1\Model\Request\Authentication\RefreshTokenRequest;
use API\v1\Model\Response\Authentication\GenerateGuestTokenResponse;
use API\v1\Model\Response\Authentication\GenerateTokenResponse;
use Firebase\JWT\ExpiredException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AuthenticationController
{
    private AuthenticationService $auth_service;
    private MemberService $member_service;
    private JwtTokenManager $token_manager;

    public function __construct(
        AuthenticationService $auth_service,
        MemberService $member_service,
        JwtTokenManager $token_manager
    ) {
        $this->auth_service = $auth_service;
        $this->member_service = $member_service;
        $this->token_manager = $token_manager;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/token",
     *     summary="Access/Refresh Token 발급",
     *     tags={"인증"},
     *     description="Access Token & Refresh Token을 발급합니다.
    - Access Token은 API 요청에 사용되며, 일정 시간 후 만료됩니다.
    - Refresh Token은 Access Token 재발급에 필요하며 데이터베이스에 저장됩니다.
    ",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/GenerateTokenRequest"),
     *          )
     *      ),
     *     @OA\Response(response="200", description="Access/Refresh Token 발급 성공", @OA\JsonContent(ref="#/components/schemas/GenerateTokenResponse")),
     *     @OA\Response(response="401", ref="#/components/responses/401"),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500"),
     * )
     */
    public function generateToken(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        $request_data = new GenerateTokenRequest($request_body);

        $member = $this->member_service->fetchMemberById($request_data->username);
        if (!$member || !check_password($request_data->password, $member['mb_password'])) {
            throw new HttpUnauthorizedException($request, '아이디 또는 비밀번호가 일치하지 않습니다.');
        }

        if (($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date('Ymd', G5_SERVER_TIME))
            || ($member['mb_leave_date'] && $member['mb_leave_date'] <= date('Ymd', G5_SERVER_TIME))
        ) {
            throw new HttpForbiddenException($request, '탈퇴 또는 차단된 회원이므로 로그인하실 수 없습니다.');
        }

        // 메일인증 설정이 되어 있다면
        if (is_use_email_certify() && !preg_match('/[1-9]/', $member['mb_email_certify'])) {
            throw new HttpForbiddenException($request, "{$member['mb_email']} 메일로 메일인증을 받으셔야 로그인 가능합니다.");
        }

        // 토큰 생성
        $claim = ['sub' => $member['mb_id']];
        $access_token = $this->token_manager->createToken('access', $claim);
        $refresh_token = $this->token_manager->createToken('refresh', $claim);

        // 토큰 디코딩
        $access_token_decode = $this->token_manager->decodeToken('access', $access_token);
        $refresh_token_decode = $this->token_manager->decodeToken('refresh', $refresh_token);

        // 기존 토큰 삭제 후 새로운 토큰 저장
        $this->auth_service->deleteRefreshToken($member['mb_id']);
        $this->auth_service->insertRefreshToken($member['mb_id'], $refresh_token, $refresh_token_decode);

        $response_data = new GenerateTokenResponse(
            [
                'access_token' => $access_token,
                'access_token_expire_at' => date('c', $access_token_decode->exp),
                'refresh_token' => $refresh_token,
                'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
                'token_type' => 'Bearer',
            ]
        );

        return api_response_json($response, $response_data);
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
    public function refreshToken(Request $request, Response $response): Response
    {
        $request_body = $request->getParsedBody();
        $request_data = new RefreshTokenRequest($request_body);
        $token_info = $this->auth_service->fetchRefreshToken($request_data->refresh_token);

        if (!$token_info || !isset($token_info['expires_at'])) {
            throw new HttpNotFoundException($request, '토큰이 존재하지 않습니다.');
        }

        // 토큰 디코딩
        try {
            $old_refresh_token_decode = $this->token_manager->decodeToken('refresh', $request_data->refresh_token);
        } catch (ExpiredException $e) {
            $this->auth_service->deleteRefreshToken($token_info['mb_id']);
            throw $e; // Refresh Token 만료
        }

        // 토큰 재생성
        $claim = ['sub' => $token_info['mb_id']];
        $new_access_token = $this->token_manager->createToken('access', $claim);
        $new_refresh_token = $this->token_manager->createTokenWithTime('refresh', $claim, $old_refresh_token_decode->exp);
        $new_access_token_decode = $this->token_manager->decodeToken('access', $new_access_token);

        // 기존 토큰 갱신
        $this->auth_service->updateRefreshToken($token_info['mb_id'], $new_refresh_token, $old_refresh_token_decode);

        $response_data = new GenerateTokenResponse(
            [
                'access_token' => $new_access_token,
                'access_token_expire_at' => date('c', $new_access_token_decode->exp),
                'refresh_token' => $new_refresh_token,
                'refresh_token_expire_at' => date('c', $old_refresh_token_decode->exp),
                'token_type' => 'Bearer',
            ]
        );
        return api_response_json($response, $response_data);
    }

    /**
     * @OA\Post(
     *     path="/api/v1/token/guest",
     *     summary="Access Token 발급",
     *     tags={"인증"},
     *     description="일회성 Access Token  발급합니다.
    - Access Token은 API 요청에 사용되며, 일정 시간 후 만료됩니다.
    ",
     *     @OA\Response(response="200", description="Access Token 발급 성공", @OA\JsonContent(ref="#/components/schemas/GenerateGuestTokenResponse")),
     *     @OA\Response(response="401", ref="#/components/responses/401"),
     *     @OA\Response(response="403", ref="#/components/responses/403"),
     *     @OA\Response(response="422", ref="#/components/responses/422"),
     *     @OA\Response(response="500", ref="#/components/responses/500")
     * )
     */
    public function generateTokenByGuest(Request $request, Response $response): Response
    {
        // 토큰 생성
        $claim = ['sub' => '', 'role' => 'guest'];
        $access_token = $this->token_manager->createToken('access', $claim);

        // 토큰 디코딩
        $access_token_decode = $this->token_manager->decodeToken('access', $access_token);

        $response_data = new GenerateGuestTokenResponse(
            [
                'access_token' => $access_token,
                'access_token_expire_at' => date('c', $access_token_decode->exp),
                'token_type' => 'Bearer',
            ]);

        return api_response_json($response, $response_data);
    }
}
