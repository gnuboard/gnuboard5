<?php
require_once './_common.php';

set_session('ss_admin_token', '');

$error = admin_referer_check(true);
if ($error) {
    die(json_encode(array('error' => $error, 'url' => G5_URL)));
}

$token = get_admin_token();

die(json_encode(array('error' => '', 'token' => $token, 'url' => '')));
