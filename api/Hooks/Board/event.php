<?php


use API\Hooks\Board\BoardHooks;

add_event('api_create_comment_after', [BoardHooks::class, 'sendMailAfterComment'], 10, 4);

add_event('api_create_write_after', [BoardHooks::class, 'sendMailAfterWrite'], 10, 4);
