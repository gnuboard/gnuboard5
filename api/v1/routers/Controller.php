<?php

namespace API\v1\routers;

use OpenApi\Annotations as OA;

/**
     * @OA\Info(
     *      version="1.0.0",
     *      title="GnuBoard5 API Test Documentation",
     *      description="Swagger OpenApi description Test",
     *      @OA\Contact(
     *          email="admin@admin.com"
     *      ),
     *      @OA\License(
     *          name="Apache 2.0",
     *          url="http://www.apache.org/licenses/LICENSE-2.0.html"
     *      )
     * )

     *
     * @OA\Tag(
     *     name="Projects",
     *     description="API Endpoints of Projects"
     * )
    */
class Controller
{
    /**
     * @OA\Get(
     *     path="/api/users",
     *     @OA\Response(response="200", description="An example endpoint")
     * )
     */
    public function getUsers() {
        echo "asdf";
    }
}