<?php
// DB를 초기화하거나 PG 승인·취소를 요청하지 않는 이전 URL 응답.
header('HTTP/1.1 410 Gone');
header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');
?>
<!doctype html>
<html lang="ko"><head><meta charset="utf-8"><title>결제 지원 종료 안내</title></head>
<body><h1>결제 지원 종료 안내</h1>
<p>이 결제 방식은 더 이상 지원하지 않습니다.</p>
<p>결제를 진행 중이었다면 다시 결제하지 마시고, 쇼핑몰 고객센터에 승인 여부 확인과 취소·환불을 문의해 주십시오.</p>
</body></html>
<?php exit;
