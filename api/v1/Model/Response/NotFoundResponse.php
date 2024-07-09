<?php

namespace API\v1\Model\Response;

/**
 * @OA\Response(
 *    response="404",
 *    description="리소스를 찾을 수 없음",
 *    @OA\JsonContent(ref="#/components/schemas/BaseResponseModel")
 * )
 */
class NotFoundResponse extends BaseResponseModel
{
	// 이 클래스는 BaseResponseModel에서 모든 것을 상속받으며, 리소스를 찾을 수 없는 상황에 특화된 로직이나 속성을 추가할 수 있습니다.
}