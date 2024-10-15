<?php

namespace API\v1\Controller;

use API\Service\MenuService;
use API\v1\Model\Response\Menu\MenuItem;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class MenuController
{


    private MenuService $menu;

    public function __construct(MenuService $menu)
    {
        $this->menu = $menu;
    }

    /**
     * @OA\Get(
     *     path="/api/v1/menus",
     *     summary="메뉴 목록 조회",
     *     tags={"메뉴"},
     *     @OA\Response(
     *       response=200,
     *       description="Successful response",
     *       @OA\JsonContent(
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/MenuItem")
     *       )
     *     )
     * )
     */
    public function index(Request $request, Response $response, $args)
    {
        $result = $this->menu->getMenu();
        $response_data = [];
        foreach ($result as $row) {
            $menu = new MenuItem($row);
            $response_data[] = $menu;
        }
        return api_response_json($response, $response_data);
    }
}