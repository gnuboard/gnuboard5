<?php

namespace API\ResponseEmitter;

use Psr\Http\Message\ResponseInterface;
use Slim\ResponseEmitter as SlimResponseEmitter;

class ResponseEmitter extends SlimResponseEmitter
{
    /**
     * {@inheritdoc}
     */
    public function emit(ResponseInterface $response): void
    {
        $allow_origin = explode(',', trim($_ENV['CORS_ALLOW_ORIGIN'] ?? ''));
        $allow_methds = explode(',', trim($_ENV['CORS_ALLOW_METHODS'] ?? ''));
        $response = $response
            ->withHeader('Access-Control-Allow-Credentials', trim($_ENV['CORS_ALLOW_CREDENTIALS'] ?? 'false'))
            ->withHeader('Access-Control-Allow-Origin', $allow_origin)
            ->withHeader(
                'Access-Control-Allow-Headers',
                'X-Requested-With, Content-Type, Accept, Origin, Authorization',
            )
            ->withHeader('Access-Control-Allow-Methods', $allow_methds)
            ->withHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withAddedHeader('Cache-Control', 'post-check=0, pre-check=0');

        if (ob_get_contents()) {
            ob_clean();
        }

        parent::emit($response);
    }
}