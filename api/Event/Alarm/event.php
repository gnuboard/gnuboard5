<?php


use API\Event\Alarm\AlarmHooks;

add_event('api_create_comment_after', [AlarmHooks::class, 'sender_after_comment'], 10, 4);