<?php

namespace API\Handlers;

use API\Exceptions\HttpInternalServerErrorException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\ResponseEmitter;

class ShutdownHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var HttpErrorHandler
     */
    private $errorHandler;

    /**
     * @var bool
     */
    private $displayErrorDetails;

    /**
     * ShutdownHandler constructor.
     *
     * @param Request $request
     * @param HttpErrorHandler $errorHandler
     * @param bool $displayErrorDetails
     */
    public function __construct(Request $request, HttpErrorHandler $errorHandler, bool $displayErrorDetails)
    {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    public function __invoke()
    {
        $error = error_get_last();

        if ($error) {
            // notice, warning, deprecated 는 로그로 확인
            if ($error['type'] === E_NOTICE || $error['type'] === E_WARNING || $error['type'] === E_DEPRECATED) {
                return;
            }

            $errorType = $error['type'];
            $errorMessage = $error['message'];
            $errorFile = $error['file'];
            $errorLine = $error['line'];
            $message = 'An error while processing your request. Please try again later.';

            if ($this->displayErrorDetails) {
                switch ($errorType) {
                    case E_USER_ERROR:
                        $message = "FATAL ERROR: {$errorMessage} on line {$errorLine} in file {$errorFile}.";
                        break;

                    case E_USER_WARNING:
                        $message = "WARNING: {$errorMessage}";
                        break;

                    case E_USER_NOTICE:
                        $message = "NOTICE: {$errorMessage}";
                        break;

                    default:
                        $message = "ERROR: {$errorMessage} on line {$errorLine} in file {$errorFile}.";
                        break;
                }
            }

            $exception = new HttpInternalServerErrorException($this->request, $message);
            $response = $this->errorHandler->__invoke($this->request, $exception, $this->displayErrorDetails, false, false);

            if (ob_get_length()) {
                ob_clean();
            }

            $responseEmitter = new ResponseEmitter();
            $responseEmitter->emit($response);
        }
    }
}