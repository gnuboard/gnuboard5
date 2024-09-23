<?php

namespace API\v1\Controller;

use API\Exceptions\HttpForbiddenException;
use API\Exceptions\HttpUnprocessableEntityException;
use API\Service\AlarmService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AlarmController
{

    private AlarmService $alarm_service;

    public function __construct(AlarmService $alramService)
    {
        $this->alarm_service = $alramService;
    }

    /**
     * @OA\Post (
     *     path="/api/v1/alarm/test",
     *     summary="FCM 테스트",
     *     tags={"알림"},
     *     description="FCM 테스트",
     *     @OA\RequestBody (
     *       required=true,
     *     @OA\MediaType(
     *     mediaType="application/json",
     *          @OA\Schema(
     *     @OA\Property(
     *     property="type",
     *     type="string",
     *     default="token",
     *     description="타입"
     *    ),
     *     @OA\Property(
     *     property="value",
     *     type="string",
     *     default="",
     *     description="값"
     *   ),
     *     @OA\Property(
     *      property="title",
     *      type="string",
     *      default="title",
     *      description="title"
     *    ),
     *     @OA\Property(
     *      property="body",
     *      type="string",
     *      default="body",
     *      description="내용"
     *    ),
     *     @OA\Property(
     *      property="image",
     *      type="string",
     *      default="",
     *      description="이미지주소"
     *    )
     * )
     * )
     * ),
     *     @OA\Response(response="200", description="FCM 테스트 성공", @OA\JsonContent(type="object", @OA\Property(property="result", type="string", example="success"))),
     *     @OA\Response(response="422", ref="#/components/responses/422")
     *    )
     *
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    //todo remove
    public function test(Request $request, Response $response): Response
    {
        if (!G5_DEBUG) {
            throw new HttpForbiddenException($request, '접근 불가합니다.');
        }
        $type = $request->getParsedBody()['type'];
        $value = $request->getParsedBody()['value'];
        $title = $request->getParsedBody()['title'];
        $body = $request->getParsedBody()['body'];
        $image = $request->getParsedBody()['image'];
        $target_data = [$type, $value];

        $alarm_message = $this->alarm_service->createMessage($target_data, $title, $body . date('Y-m-d H:i:s'), $image);
        $result = $this->alarm_service->sendMessage($alarm_message);
        $response_data = [
            'result' => $result,
        ];
        return api_response_json($response, $response_data);
    }

    /**
     * FCM 토큰 등록
     * @OA\Post (
     *     path="/api/v1/alarm",
     *     summary="FCM 토큰 등록",
     *     tags={"알림"},
     *     description="FCM 토큰을 등록합니다.",
     *     @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *      mediaType="application/json",
     *      @OA\Schema(
     *          @OA\Property(
     *          property="fcm_token",
     *          type="string",
     *          description="FCM 토큰"
     *          ),
     *          @OA\Property(
     *          property="platform",
     *          type="string",
     *          default="web,android,ios 중에 선택하세요",
     *          description="platform"
     *          )
     *     )
     *    )
     *  ),
     *     @OA\Response(response="200", description="FCM 토큰 등록 성공", @OA\JsonContent(type="object", @OA\Property(property="message", type="string", example="토큰이 등록되었습니다."))),
     *     @OA\Response(response="422", ref="#/components/responses/422")
     *     )
     *
     *
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function register(Request $request, Response $response): Response
    {
        $parsed_data = $request->getParsedBody();
        if (!isset($parsed_data['fcm_token'])) {
            throw new HttpUnprocessableEntityException($request, 'fcm 토큰이 없습니다.');
        }
        
        if (!isset($parsed_data['platform'])) {
            throw new HttpUnprocessableEntityException($request, 'platform 이 없습니다.');
        }

        $member = $request->getAttribute('member');
        if (isset($parsed_data['fcm_token'], $parsed_data['platform'])) {
            $ip = $request->getServerParams()['REMOTE_ADDR'];
            $this->alarm_service->registerFcmToken($member['mb_id'], $parsed_data['fcm_token'], $parsed_data['platform'], $ip);
        }

        return api_response_json($response, ['message' => '토큰이 등록되었습니다.']);
    }
}