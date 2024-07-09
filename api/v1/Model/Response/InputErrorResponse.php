<?php

namespace API\v1\Model\Response;

use API\v1\Model\Response\BaseResponse;

/**
 * @OA\Response(
 *    response="422",
 *    description="입력값 오류",
 *    @OA\JsonContent(ref="#/components/schemas/BaseResponse")
 * )
 */
class InputErrorResponse extends BaseResponse
{
    // 이 클래스는 BaseResponse에서 모든 것을 상속받으며, 중복 데이터에 특화된 로직이나 속성을 추가할 수 있습니다.
}
