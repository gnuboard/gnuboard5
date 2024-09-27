<?php


use API\Hooks\Alarm\AlarmHooks;

add_event('api_create_comment_after', [AlarmHooks::class, 'sendFcmAfterCreatComment'], 10, 4);