<?php

namespace API\Exceptions;

use RuntimeException;

/**
 * Request, Response validation exception.
 * 요청, 응답 객체에서 유효성 검증후 안맞을 때 발생되는 예외
 */
class ValidateException extends RuntimeException
{

}