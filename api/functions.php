<?php
use Psr\Http\Message\ResponseInterface as Response;

/**
 * API Response JSON
 * 
 * @param Response $response
 * @param array $data
 * @return Response
 */
function api_response_json(Response $response, array $data)
{
    $json = json_encode($data, JSON_UNESCAPED_UNICODE);
    $response->getBody()->write($json);
    return $response->withAddedHeader('Content-Type', 'application/json');
}

/**
 * Select only the data you want and return it as an array
 * 
 * @param array $data
 * @param array $select
 * @return array
 */
function generate_select_array(array $data, array $select)
{
    $select_array = [];
    foreach ($select as $key) {
        $select_array[$key] = $data[$key];
    }
    return $select_array;
}
