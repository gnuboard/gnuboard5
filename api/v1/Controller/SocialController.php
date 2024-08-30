<?php

namespace API\v1\Controller;

use API\Auth\JwtTokenManager;
use API\Exceptions\HttpBadRequestException;
use API\Service\ConfigService;
use API\Service\Social\SocialService;
use API\v1\Model\Request\Member\CreateSocialMemberRequest;
use API\v1\Model\Response\Authentication\GenerateTokenResponse;
use Hybridauth\Exception\NotImplementedException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * 그누보드 5 API 소셜로그인
 */
class SocialController
{
    /**
     * @var SocialService
     */
    public SocialService $socialService;
    private JwtTokenManager $token_manager;

    public function __construct(SocialService $socialService, JwtTokenManager $jwtTokenManager)
    {
        $this->socialService = $socialService;
        $this->token_manager = $jwtTokenManager;
    }

    /**
     * 인앱브라우저, 웹뷰에서 소셜 로그인 페이지로 이동합니다.
     * @OA\Get (
     *     path="/api/v1/social/login/{provider}",
     *     tags={"소셜로그인"},
     *     summary="소셜 로그인 - 웹뷰에서 쓰는 API",
     *     description="소셜 로그인",
     *     @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     description="소셜 로그인 제공자",
     *     required=true,
     *     @OA\Schema(
     *     type="string"
     *    )
     *  ),
     *     @OA\Response(
     *     response=200,
     *     description="소셜 로그인 페이지로 이동"
     *  )
     * )
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws \Hybridauth\Exception\InvalidArgumentException
     * @throws \Hybridauth\Exception\UnexpectedValueException
     */
    public function socialLogin(Request $request, Response $response, $args)
    {
        $provider = $args['provider'] ?? '';

        if (!$provider) {
            throw new HttpBadRequestException($request, '소셜 로그인 provider 를 입력해주세요.');
        }

        $callback_base_url = G5_URL . '/api/v1/social/login-callback';
        $this->socialService->setProviderConfig($callback_base_url);
        $is_use = $this->socialService->socialUseCheck($provider);

        if (!$is_use) {
            return api_response_json($response, ['message' => '해당 소셜 로그인 설정이 비활성화 되어 있습니다.'], 400);
        }

        $this->socialService->setProvider($provider);
        $this->socialService->current_provider_instance->authenticate();

        return api_response_json($response, ['message' => '소셜 로그인을 진행 해주세요'], 200);
    }


    /**
     * 웹뷰에서 쓰는 소셜 로그인 콜백
     * @OA\Get (
     *     path="/api/v1/social/callback/{provider}",
     *     tags={"소셜로그인"},
     *     summary="소셜 로그인 콜백 - 웹뷰에서 쓰는 API",
     *     description="소셜 로그인 콜백",
     *     @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     description="소셜 로그인 제공자",
     *     required=true,
     *     @OA\Schema(
     *     type="string"
     *    )
     *  ),
     *     @OA\Response(response="200", description="그누보드 서버로그인을 위한 Access/Refresh Token 발급 성공",
     *          @OA\JsonContent(ref="#/components/schemas/GenerateTokenResponse")
     *   ),
     *     @OA\Response(
     *      response=400,
     *      description="해당 소셜 로그인 설정이 비활성화 되어 있습니다."
     *  ),
     *     @OA\Response(
     *     response=403,
     *     description="탈퇴 또는 차단된 회원이므로 로그인 하실 수 없습니다."
     *  ),
     * )
     *
     * @param Response $response
     * @param Request $request
     * @param $args
     * @return Response
     * @throws NotImplementedException
     * @throws HttpBadRequestException
     */
    public function socialLoginCallback(Request $request, Response $response, $args)
    {
        $provider = $args['provider'] ?? '';
        $callback_base_url = G5_URL . '/api/v1/social/login-callback';
        $this->socialService->setProviderConfig($callback_base_url);
        $is_use = $this->socialService->socialUseCheck($provider);

        if (!$is_use) {
            throw new HttpBadRequestException($request, '해당 소셜 로그인 설정이 비활성화 되어 있습니다.');
        }

        try {
            $this->socialService->setProvider($provider);
            $this->socialService->current_provider_instance->authenticate();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw new HttpBadRequestException($request, '소셜 로그인에 실패하였습니다.');
        }

        // config 에서 엑세스 토큰 얻기
        $social_access_token = $this->socialService->current_provider_instance->getAccessToken();

        // 프로필조회
        $profile = $this->socialService->current_provider_instance->getUserProfile();

        // 중복가입 체크
        $is_exist = $this->socialService->checkExistSocialMember($provider, $profile->identifier);

        // 회원가입시 필요한 소셜 토큰 전송
        if (!$is_exist) {
            $token = $this->token_manager->create_token('access', [
                'process_type' => 'social_register',
                'social_token' => $social_access_token,
                'provider' => $provider,
                'identifier' => $profile->identifier
            ]);
            $response->withoutHeader('Authorization');
            $response = $response->withHeader('Set-Cookie', 'Authorization=' . urlencode($token) . '; expires=' . (time() + 60 * 10) . '; path=/; httponly');

            $data = [
                'statusCode' => 404,
                'error' => [
                    'type' => 'user not found',
                    'description' => '소셜 회원가입을 진행 해주세요',
                ]
            ];
            return api_response_json($response, $data, 404);
        }

        if ($this->socialService->checkDisallowMember($provider, $profile->identifier)) {
            return api_response_json($response, ['message' => '탈퇴 또는 차단된 회원이므로 로그인 하실 수 없습니다.'], 403);
        }

        return api_response_json($response, ['message' => ''], 200);
    }

    /**
     * @OA\Post (
     *     path="/api/v1/social/register/{provider}",
     *     tags={"소셜로그인"},
     *     summary="소셜 회원가입",
     *     description="소셜 회원가입",
     *     @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     description="소셜 로그인 제공자",
     *     required=true,
     *     @OA\Schema(
     *     type="string"
     *   )
     * ),
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *      mediaType="application/json",
     *     @OA\Schema(ref="#/components/schemas/CreateSocialMemberRequest")
     *     )
     *  ),
     *     @OA\Response(response="200", description="소셜 회원가입 후 로그인 Access/Refresh Token 발급 성공", @OA\JsonContent(ref="#/components/schemas/GenerateTokenResponse")),
     *     @OA\Response(
     *     response=409,
     *     description="이미 가입된 회원입니다."
     *  )
     * )
     *
     *
     *
     * 발행된 토큰 정보로 회원가입
     *
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     * @throws NotImplementedException
     * @throws \Hybridauth\Exception\InvalidArgumentException
     * @throws \Hybridauth\Exception\UnexpectedValueException
     */
    public function socialRegister(Request $request, Response $response, $args)
    {
        $provider = $args['provider'] ?? '';
        if (!$provider) {
            throw new HttpBadRequestException($request, '소셜 로그인 provider 를 입력해주세요.');
        }

        //jwt 가져오기
        $jwt_token = $request->getHeader('Authorization')[0] ?? '' || $request->getCookieParams()['Authorization'] ?? '';
        $jwt_data = $this->token_manager->decode_token('access', $jwt_token);
        $process_type = $jwt_data->process_type ?? '';

        if ($process_type !== 'social_register') {
            throw new HttpBadRequestException($request, '잘못된 접근입니다.');
        }

        $callback_base_url = G5_URL . '/api/v1/social/login-callback';
        $this->socialService->setProviderConfig($callback_base_url);
        $storage_data = [
            'access_token' => $jwt_data->social_token->access_token,
            'refresh_token' => $jwt_data->social_token->refresh_token,
            'expires_at' => $jwt_data->social_token->expires_at,
            'expires_in' => $jwt_data->social_token->expires_in,
            'token_type' => $jwt_data->social_token->token_type,
        ];
        $this->socialService->setProvider($provider, $storage_data);
        $profile = $this->socialService->current_provider_instance->getUserProfile();
        $identifier = $profile->identifier;

        if (!$identifier) {
            return api_response_json($response, ['message' => '소셜 정보를 가져오는데 실패하였습니다.'], 400);
        }

        $is_exist = $this->socialService->checkExistSocialMember($provider, $profile->identifier);

        if ($is_exist) {
            return api_response_json($response, ['message' => '이미 가입된 회원입니다.'], 409);
        }

        $request_data = $request->getParsedBody() ?? [];

        if (!$request_data) {
            return api_response_json($response, ['message' => '회원가입 정보를 입력해주세요'], 400);
        }

        $config = ConfigService::getConfig();
        $create_member_data = new CreateSocialMemberRequest($config, $request_data);

        try {
            $this->socialService->signUpSocialMember($provider, $profile, $create_member_data);
        } catch (\Exception $e) {
            if ($e->getCode() === 409) {
                return api_response_json($response, ['message' => '이미 가입된 회원입니다.'], 409);
            }

            return api_response_json($response, ['message' => $e->getMessage()], 400);
        }

        // login
        $member = $this->socialService->fetchMemberByIdentifier($provider, $profile->identifier);
        $claim = ['sub' => $member['mb_id']];
        $login_access_token = $this->token_manager->create_token('access', $claim);
        $access_token_decode = $this->token_manager->decode_token('access', $login_access_token);
        $login_refresh_token = $this->token_manager->create_token('refresh', $claim);
        $refresh_token_decode = $this->token_manager->decode_token('refresh', $login_refresh_token);

        $response_data = new GenerateTokenResponse(
            [
                'access_token' => $login_access_token,
                'access_token_expire_at' => date('c', $access_token_decode->exp),
                'refresh_token' => $login_refresh_token,
                'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
                'token_type' => 'Bearer',
            ]
        );
        $response->withoutHeader('Authorization');

        return api_response_json($response, $response_data);
    }

    /**
     *
     *
     * @OA\Post (
     *     path="/api/v1/social/token-login/{provider}",
     *     tags={"소셜로그인"},
     *     summary="엑세스 토큰으로 소셜 로그인",
     *     description="엑세스 토큰으로 소셜 로그인",
     *     @OA\Parameter(
     *     name="provider",
     *     in="path",
     *     description="소셜 로그인 제공자",
     *     required=true,
     *     @OA\Schema(
     *     type="string"
     *   )
     * ),
     *     @OA\RequestBody(
     *     required=true,
     *     @OA\MediaType(
     *     mediaType="application/json",
     *     @OA\Schema(
     *      type="object",
     *      required={"access_token"},
     *        @OA\Property(
     *        property="access_token",
     *        type="string",
     *        description="소셜 로그인 access token"
     *        )
     *      )
     *   )
     * ),
     *     @OA\Response(response="200", description="그누보드 서버로그인을 위한 Access/Refresh Token 발급 성공",
     *          @OA\JsonContent(ref="#/components/schemas/GenerateTokenResponse")
     *    ),
     *     @OA\Response(
     *     response=404,
     *     description="회원이 없습니다. 회원가입이 필요합니다."
     *   ),
     *     @OA\Response(
     *      response=403,
     *      description="탈퇴 또는 차단 회원입니다."
     *    )
     * )
     *
     * 엑세스 토큰을 앱에서 전달받아서 처리합니다.
     * @param Response $response
     * @param Request $request
     * @param $args
     * @return Response
     * @throws \Hybridauth\Exception\InvalidArgumentException
     * @throws \Hybridauth\Exception\UnexpectedValueException
     */
    public function socialLoginWithAccessToken(Request $request, Response $response, $args)
    {
        //provider 체크
        $provider = $args['provider'] ?? '';

        if (!$provider) {
            throw new HttpBadRequestException($request, '소셜 로그인 provider 를 입력해주세요.');
        }

        $is_use = $this->socialService->socialUseCheck($provider);

        if (!$is_use) {
            throw new HttpBadRequestException($request, '해당 소셜 로그인 설정이 비활성화 되어 있습니다.');
        }

        // access token 체크
        $social_access_token = $request->getParsedBody()['access_token'] ?? '';

        if (!$social_access_token) {
            throw new HttpBadRequestException($request, '토큰이 없습니다.');
        }

        $callback_base_url = G5_URL . '/api/v1/social/login-callback';
        $this->socialService->setProviderConfig($callback_base_url);
        $this->socialService->setProvider($provider);
        $tokens = [
            'access_token' => $social_access_token,
            'refresh_token' => '',
        ];
        $this->socialService->current_provider_instance->setAccessToken($tokens);

        // 프로필 조회
        try {
            $profile = $this->socialService->current_provider_instance->getUserProfile();
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            throw new HttpBadRequestException($request, '인증이 실패하였습니다.');
        }

        $is_exist = $this->socialService->checkExistSocialMember($provider, $profile->identifier);

        // 회원이 없을 경우 회원가입을 위한 소셜 토큰 전달
        if (!$is_exist) {
            $token = $this->token_manager->create_token('access', ['process_type' => 'social_register', 'provider' => $provider, 'identifier' => $profile->identifier]);
            $response->withoutHeader('Authorization');
            $response = $response->withHeader('Set-Cookie', 'Authorization=' . urlencode($token) . '; expires=' . (time() + 60 * 10) . '; path=/; httponly');

            return api_response_json($response, ['message' => '소셜 회원가입을 진행 해주세요'], 404);
        }

        // 로그인
        $member = $this->socialService->fetchMemberByIdentifier($provider, $profile->identifier);
        $claim = ['sub' => $member['mb_id']];
        $login_access_token = $this->token_manager->create_token('access', $claim);
        $access_token_decode = $this->token_manager->decode_token('access', $login_access_token);
        $login_refresh_token = $this->token_manager->create_token('refresh', $claim);
        $refresh_token_decode = $this->token_manager->decode_token('refresh', $login_refresh_token);

        $response_data = new GenerateTokenResponse(
            [
                'access_token' => $login_access_token,
                'access_token_expire_at' => date('c', $access_token_decode->exp),
                'refresh_token' => $login_refresh_token,
                'refresh_token_expire_at' => date('c', $refresh_token_decode->exp),
                'token_type' => 'Bearer',
            ]
        );

        return api_response_json($response, $response_data);
    }

}