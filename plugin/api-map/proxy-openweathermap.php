<?php
require_once("_common.php");

function get_weather($lat, $lon) {
    require_once('./config-map.php');
    
    $url = "https://api.openweathermap.org/data/2.5/weather?lat=${lat}&lon=${lon}&appid=${OPENWEATHERMAP_API_KEY}&units=metric";
    
    try {
        // cURL 세션 초기화
        $curl = curl_init();
        
        // cURL 옵션 설정
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        
        // URL로부터 콘텐츠 가져오기
        $response = curl_exec($curl);
        
        // 오류 체크
        if (curl_errno($curl)) {
            throw new Exception(curl_error($curl));
        }

        // cURL 세션 종료
        curl_close($curl);

        // 응답 디코드 및 반환
        return json_decode($response, true);

    } catch (Exception $e) {
        // 에러 로깅
        error_log($e->getMessage());
        // 에러 응답 반환
        header('Content-Type: application/json');
        echo json_encode(array("error" => "An error occurred"), JSON_PRETTY_PRINT);
        http_response_code(500);
    }
}

// URL 파라미터로부터 위도와 경도 값을 추출합니다. 이 부분은 실제 PHP 스크립트에서 요청 처리 방식에 따라 달라질 수 있습니다.
$lat = $_GET['lat'] ?? 0;
$lon = $_GET['lon'] ?? 0;

// 함수 호출 및 결과 출력
$result = get_weather($lat, $lon);
echo json_encode($result, JSON_PRETTY_PRINT);
?>