<?php

use API\Middleware\ConfigMiddleware;
use API\v1\Controller\GroupController;

$app->get('/groups/{gr_id}/boards', [GroupController::class, 'getBoards'])
    ->add(ConfigMiddleware::class);