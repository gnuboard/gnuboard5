<?php
$sub_menu = '600500';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '정기결제일정';
include_once G5_ADMIN_PATH.'/admin.head.php';

add_javascript('<script src="'.G5_JS_URL.'/fullcalendar/index.global.js"></script>', 2);
// add_stylesheet('<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css">', 2);

?>
<div class="calendar-wrap">
    <div class="calendar-main">
        <div id="calendar" class="fullcalendar"></div>
    </div>
    <div class="calendar-side">
        
    </div>
</div>
<script>
jQuery(function($){
    $(document).ready(function() {
        // var calendarEl = document.getElementById('calendar');
        var calendarEl = $('#calendar')[0]; // jQuery 객체에서 DOM 요소로 변환
        
        var calendar = new FullCalendar.Calendar(calendarEl, {
          headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            // right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            right: ''
          },
          // initialDate: '2023-01-12',
          // navLinks: true, // can click day/week names to navigate views
          businessHours: true, // display business hours
          editable: true,
          selectable: true,
          dayMaxEvents: true,
            eventClick: function (info) {
                alert('이벤트 클릭됨: ' + info.event.title);
            },
            datesSet: function () {
                setTimeout(() => {
                    document.querySelectorAll('.fc-more-link').forEach((btn) => {
                        btn.addEventListener('click', function () {
                            console.log('More 버튼이 클릭되었습니다!');
                        });
                    });
                }, 100); // 일정이 렌더링될 때까지 약간의 지연을 줌
            },
          locale: 'ko', // 한국어 설정
          events: function(fetchInfo, successCallback, failureCallback) {
              
              console.log(fetchInfo.startStr.split("T")[0]);
              console.log(fetchInfo.endStr.split("T")[0]);
                  
              // AJAX 요청
              $.ajax({
                url: g5_admin_url + '/subscription_admin/ajax.subscription_search.php', // 서버 API 엔드포인트 (예: 백엔드 URL)
                method: 'GET',
                data: {
                  start: fetchInfo.startStr.split("T")[0], // 현재 뷰의 시작 날짜 
                  end: fetchInfo.endStr.split("T")[0] // 현재 뷰의 종료 날짜 
                },
                success: function(response) {
                  // 서버에서 받은 데이터를 FullCalendar 이벤트 형식으로 변환
                  var events = response.map(function(item) {
                    return {
                      title: item.title,
                      start: item.start,
                      end: item.end || null, // end가 없는 경우 null 처리
                      backgroundColor: item.color || '#3788d8' // 기본 색상 지정
                    };
                  });
                  successCallback(events); // 캘린더에 이벤트 전달
                },
                error: function(xhr, status, error) {
                  console.error('이벤트 로드 실패:', error);
                  failureCallback(error); // 오류 처리
                }
              });
          
          },
            eventTimeFormat: { // 시간 형식 설정
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
            }
          /*
          events: [
            {
              title: 'Business Lunch',
              start: '2023-01-03T13:00:00',
              constraint: 'businessHours'
            },
            {
              title: 'Meeting',
              start: '2023-01-13T11:00:00',
              constraint: 'availableForMeeting', // defined below
              color: '#257e4a'
            },
            {
              title: 'Conference',
              start: '2023-01-18',
              end: '2023-01-20'
            },
            {
              title: 'Party',
              start: '2023-01-29T20:00:00'
            },

            // areas where "Meeting" must be dropped
            {
              groupId: 'availableForMeeting',
              start: '2023-01-11T10:00:00',
              end: '2023-01-11T16:00:00',
              display: 'background'
            },
            {
              groupId: 'availableForMeeting',
              start: '2023-01-13T10:00:00',
              end: '2023-01-13T16:00:00',
              display: 'background'
            },

            // red areas where no events can be dropped
            {
              start: '2023-01-24',
              end: '2023-01-28',
              overlap: false,
              display: 'background',
              color: '#ff9f89'
            },
            {
              start: '2023-01-06',
              end: '2023-01-08',
              overlap: false,
              display: 'background',
              color: '#ff9f89'
            }
          ]
          */
        });

        calendar.render();
      });
  });
</script>

<?php
include_once G5_ADMIN_PATH.'/admin.tail.php';