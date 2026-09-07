<?php
header('HTTP/1.1 410 Gone');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode(array('error' => '이 결제 방식은 더 이상 지원하지 않습니다. 다른 결제수단을 선택해 주십시오.'));
exit;
