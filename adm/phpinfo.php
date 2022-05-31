<?php
$sub_menu = "100500";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'r');

phpinfo();
