<?php

use API\Hooks\Member\MemberHooks;


add_event('api_create_comment_after', [MemberHooks::class, 'sendMailResetPassword'], 10, 4);