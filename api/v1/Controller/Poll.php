<?php

namespace API\v1\Controller;


use Slim\Psr7\Request;
use Slim\Psr7\Response;
use function API\Service\Poll\get_latest_poll;

class PollController
{
    
    
    /**
     * 최신 투표 1건 조회
     * @param Request $request
     * @param Response $response
     * @return \Psr\Http\Message\ResponseInterface|void
     */
    public function latest(Request $request, Response $response)
    {
        //@todo cache 추가
        $poll = get_latest_poll();
        if ($poll === null) {
            return api_response_json($response, ['message' => '최신 투표가 없습니다.'], 404);
        }
        
        return api_response_json($response, $poll);
    }
}