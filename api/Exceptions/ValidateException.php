<?php

namespace API\Exceptions;

use RuntimeException;

/**
 * Request validation exception.
 * 요청에서 유효성 검사후 맞지 않을때 발생시키는 예외.
 */
class ValidateException extends RuntimeException
{
    
}