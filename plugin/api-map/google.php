<?php
require_once("_common.php");

$g5['title'] = "구글 날씨 지도";
require_once("../../head.php");
?>

<script>
    function loadScript() {
        var script = document.createElement('script');
        // 비동기 로드 설정
        // script.async = true;
        // script.defer = true;
        script.type = 'text/javascript';
        script.src = './proxy-google-maps.php'; // PHP 프록시 엔드포인트를 가리킵니다.
        document.body.appendChild(script);
    }

    window.onload = loadScript;
    // document.addEventListener('DOMContentLoaded', loadScript);

    let map;

    function initMap() {
        // 최상위 id, 여기서는 <div id="container"></div>를 찾아서 구글 지도를 생성
        // 하위 id에 넣으면 지도가 노출 안됨
        var container = document.getElementById("container");
        container.style.setProperty('height', '800px', 'important');
        map = new google.maps.Map(container, {
            center: { lat: 36.1196, lng: 128.3446 }, // 구미시의 위도와 경도
            zoom: 7.0, // 줌 레벨을 조정하여 전국을 볼 수 있게 함
        });
        fetchWeather();
    }

    async function fetchWeather() {
        const cities = [
            { name: "서울", lat: 37.5665, lng: 126.9780 },
            { name: "부산", lat: 35.1796, lng: 129.0756 },
            { name: "대구", lat: 35.8714, lng: 128.6014 },
            { name: "광주", lat: 35.1595, lng: 126.8526 },
            { name: "대전", lat: 36.3504, lng: 127.3845 },
            { name: "울산", lat: 35.5384, lng: 129.3114 },
            { name: "속초", lat: 38.2044, lng: 128.5911 },
            { name: "동해", lat: 37.5262, lng: 129.1142 },
            { name: "제천", lat: 37.1499, lng: 128.2122 },
            { name: "여수", lat: 34.7604, lng: 127.6622 },
            { name: "제주", lat: 33.4890, lng: 126.4983 },
            { name: "독도", lat: 37.2420, lng: 131.8690 },
            { name: "울릉도", lat: 37.4840, lng: 130.8980 },
        ];

        cities.forEach(async (city) => {
            const response = await fetch(`./proxy-openweathermap.php?lat=${city.lat}&lng=${city.lng}`);
            const data = await response.json();
            const weatherInfo = `<div class="info-window-content">${city.name} ${data.main.temp}°C</div>`;
            const infowindow = new google.maps.InfoWindow({
                content: weatherInfo,
                position: { lat: city.lat, lng: city.lng },
            });
            infowindow.open(map);
        });
    }
</script>
<style>
    #map {
        height: 100%;
    }
    html, body {
        height: 100%;
        margin: 0;
        padding: 0;
    }
</style>
<style>
    .info-window-content {
        /* background-color: #333; 어두운 배경색 */
        color: #000; /* 밝은 글자색 */
        /* padding: 5px; 적당한 패딩 */
        /* border-radius: 5px; 경계 모서리 둥글게 */
    }
    /* 기타 필요한 스타일 */
</style>
<div id="map" style="height: 500px; width: 100%;"></div>
<?php
require_once("../../tail.php");
?>