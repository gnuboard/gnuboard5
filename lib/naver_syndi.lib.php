<?php
if (!defined('_GNUBOARD_')) exit;

// http://kr1.php.net/manual/en/function.curl-setopt-array.php 참고
if (!function_exists('curl_setopt_array')) {
   function curl_setopt_array(&$ch, $curl_options)
   {
       foreach ($curl_options as $option => $value) {
           if (!curl_setopt($ch, $option, $value)) {
               return false;
           } 
       }
       return true;
   }
}


// 네이버 신디케이션에 ping url 을 curl 로 전달합니다.
function naver_syndi_ping($bo_table, $wr_id)
{
    global $config;

    $token = trim($config['cf_syndi_token']);

    // 토큰값이 없다면 네이버 신디케이션 사용안함
    if ($token == '') return 0;

    // 신디케이션 수집 제외게시판
    if (preg_match('#^('.$config['cf_syndi_except'].')$#', $bo_table)) return -2;

    // curl library 가 지원되어야 합니다.
    if (!function_exists('curl_init')) return -3;

    $ping_auth_header = "Authorization: Bearer " . $token;
    $ping_url = urlencode( G5_SYNDI_URL . "/ping.php?bo_table={$bo_table}&wr_id={$wr_id}" );
    $ping_client_opt = array( 
        CURLOPT_URL => "https://apis.naver.com/crawl/nsyndi/v2", 
        CURLOPT_POST => true, 
        CURLOPT_POSTFIELDS => "ping_url=" . $ping_url, 
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10, 
        CURLOPT_TIMEOUT => 10, 
        CURLOPT_HTTPHEADER => array("Host: apis.naver.com", "Pragma: no-cache", "Accept: */*", $ping_auth_header)
    ); 

    //print_r2($ping_client_opt); exit;
    $ping = curl_init(); 
    curl_setopt_array($ping, $ping_client_opt); 
    $response = curl_exec($ping); 
    curl_close($ping);

    return $response;
}
?>