<?php
// /var/www/gnuboard/api/auth_check.php
include_once '../common.php';

// CORS 헤더 설정
header('Content-Type: application/json');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Origin: http://localhost:3001');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = array(
    'isLoggedIn' => false,
    'user' => null
);

if ($is_member) {
    $response = array(
        'isLoggedIn' => true,
        'user' => array(
            'id' => $member['mb_id'],
            'name' => $member['mb_name'],
            'nick' => $member['mb_nick'],
            'email' => $member['mb_email'],
            'level' => $member['mb_level']
        )
    );
}

echo json_encode($response);
exit;