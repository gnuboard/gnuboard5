<?php

namespace API\v1\Model\Response;

use API\v1\Model\Response\BaseResponseModel;

/**
 * @OA\Response(
 *    response="403",
 *    description="권한 없음",
 *    @OA\JsonContent(ref="#/components/schemas/BaseResponseModel")
 * )
 */
class ForbiddenResponse extends BaseResponseModel
{
	// 이 클래스는 BaseResponseModel에서 모든 것을 상속받으며, 권한 없음에 특화된 로직이나 속성을 추가할 수 있습니다.
}