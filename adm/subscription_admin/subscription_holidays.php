<?php
$sub_menu = '600510';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'w');

add_javascript('<script src="' . G5_JS_URL . '/fullcalendar/index.global.js"></script>', 2);
add_javascript('<script src="' . G5_JS_URL . '/fullcalendar/popper.min.js"></script>', 3);
add_javascript('<script src="' . G5_JS_URL . '/fullcalendar/tooltip.min.js"></script>', 3);

add_javascript('<script src="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.css">', 10);

$api_holidays = get_subscription_holidays(1);

$holidays_json = json_encode($api_holidays, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$g5['title'] = '정기구독 공휴일 설정';
include_once G5_ADMIN_PATH . '/admin.head.php';
?>
<style>
  /* 날짜 셀 내 버튼 스타일 */
  .add-holiday-btn {
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    padding: 4px 8px;
    font-size: 12px;
    cursor: pointer;
    margin-top: 4px;
  }

  .add-holiday-btn:hover {
    background: #0056b3;
  }

  .fc-event-title-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .fc-event.w {
    background: #FF6F00;
    border: #8D3D00
  }

  .delete-btn {
    background: red;
    color: white;
    border: none;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 10px;
    cursor: pointer;
    margin-left: 8px;
  }
</style>

<div class="calendar-wrap">
  <div class="calendar-main">
    <div id="calendar" class="fullcalendar holiday-settings"></div>
    <!-- 로딩 오버레이와 스피너 -->
    <div class="loading-overlay" id="loadingOverlay">
      <div class="spinner"></div>
      <div class="loading-text">로딩 중...</div>
    </div>
  </div>
  <div class="calendar-side">
    <div class="fc">
      <div class="description-holidays">
        <h3>api로 지정된 휴일</h3>

        <div class="api-holidays"></div>

        <h3>관리자가 지정한 영업일/휴무일</h3>

        <div class="admin-set-holidays"></div>
      </div>
    </div>

  </div>

</div>

<a href="#subscription_modal1" data-oid="" data-pid="" id="subscription-modal1-btn" data-modal class="sound_only">상세보기</a>

<!-- 모달 -->
<div class="modal" id="subscription_modal1">
  <div class="modal-content">
    <h3>공휴일 / 영업일 관리</h3>

    <div style="margin-bottom:10px;">
      <label>날짜: <input type="date" name="holiday-date" id="holiday-date" /></label>
    </div>
    <div style="margin-bottom:10px;">
      <label>제목: <input type="text" name="holiday-title" id="holiday-title" placeholder="예: 설날, 임시영업 등" /></label>
    </div>
    <div style="margin-bottom:10px;">
      <label><input type="radio" name="holiday-type" value="h" title="holiday" checked> 휴무일</label>
      <label><input type="radio" name="holiday-type" value="w" title="workday"> 영업일 지정</label>
    </div>


    <button id="holiday-add-btn">추가</button>
    <button onclick="closeModal()">취소</button>
  </div>
</div>

<script>
  var holidays = <?php echo $holidays_json; ?>

  jQuery(function($) {

    $(document).ready(function() {

      // var calendarEl = document.getElementById('calendar');
      var calendarEl = $('#calendar')[0]; // jQuery 객체에서 DOM 요소로 변환
      var $loadingOverlay = $('#loadingOverlay'); // 로딩 오버레이 jQuery 객체

      $loadingOverlay.hide();

      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        locale: 'ko',
        selectable: true,
        editable: false,
        businessHours: true, // display business hours
        buttonText: {
          today: '오늘'
        },
        customButtons: {
          myToday: { // "오늘" 버튼 정의
            text: '오늘',
            click: function() {
              calendar.today(); // "today" 기능 그대로 실행
            }
          }
        },
        events: function(fetchInfo, successCallback, failureCallback) {

          const start = new Date(fetchInfo.startStr.split("T")[0]);
          const end = new Date(fetchInfo.endStr.split("T")[0]);

          $.ajax({
            url: './holiday_events.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
              successCallback(response); // FullCalendar에 이벤트 전달

              const filtered = response.filter(item => {
                const itemDate = new Date(item.start);
                return itemDate >= start && itemDate <= end;
              });

              if (filtered) {

                // 출력용 <ul> 생성
                const ul = document.createElement('ul');

                filtered.forEach(h => {
                  const li = document.createElement('li');

                  li.textContent = `${h.start} - ${h.title}`;

                  if (Array.isArray(h.classNames)) {
                    li.className = h.classNames.join(' ');
                  }

                  ul.appendChild(li);

                });

                $(".admin-set-holidays").html(ul);

              }


            },
            error: function(jqXHR, textStatus, errorThrown) {
              console.error('공휴일 데이터를 불러오는 중 오류 발생:', textStatus, errorThrown);
              failureCallback(errorThrown); // 실패 콜백
            }
          });

          const result = [];

          for (const year in holidays) {
            holidays[year].forEach(h => {
              const holidayDate = new Date(h.dateymd);
              if (holidayDate >= start && holidayDate <= end) {
                result.push(h);
              }
            });
          }

          // 출력용 <ul> 생성
          const ul = document.createElement('ul');

          result.forEach(h => {
            const li = document.createElement('li');
            li.textContent = `${h.dateymd} - ${h.name}`;
            ul.appendChild(li);

            setTimeout(() => {
              var $dayNumber = $('td[data-date="' + h.dateymd + '"]').find('.fc-daygrid-day-number');

              // console.log( $dayNumber, 'td[data-date="' + h.dateymd + '"]' );

              if ($dayNumber.length) {
                var originalText = $dayNumber.html();

                if (!originalText.includes(h.name)) {
                  $dayNumber.addClass("holiday").prepend('<span class="ellipsis" title="' + h.name + '">' + h.short + '</span>');
                }
              }
            }, "100");
          });

          $(".api-holidays").html(ul);

          /*
          // 예시: 특정 div에 출력
          const outputDiv = document.getElementById('holiday-list');
          if (outputDiv) {
            outputDiv.innerHTML = ''; // 기존 내용 초기화
            outputDiv.appendChild(ul);
          }
          */


        },

        /*
        dayCellContent: function(info) {
          const dateStr = info.date.toISOString().split('T')[0];
          const container = document.createElement('div');
          container.innerHTML = `<div>${info.dayNumberText}</div>
                                <button class="add-holiday-btn" onclick="openModal('${dateStr}')">공휴일 추가</button>`;
          return { domNodes: [container] };
        },
        */

        dayCellDidMount: function(info) {

          requestAnimationFrame(() => {
            const dayNumberEl = info.el.querySelector('.fc-daygrid-day-number');

            // + 버튼 생성
            const addBtn = document.createElement('button');
            addBtn.innerText = '+';
            addBtn.title = '공휴일 추가';
            addBtn.style.cssText = `
        margin-left: 4px;
        font-size: 10px;
        padding: 1px 5px;
        border: none;
        background: #28a745;
        color: white;
        border-radius: 3px;
        cursor: pointer;
        `;

            addBtn.onclick = function(e) {
              e.stopPropagation();
              openModal(info.el.getAttribute('data-date')); // 해당 날짜 전달
            };

            // 날짜 숫자 옆에 버튼 삽입
            dayNumberEl.appendChild(addBtn);
          });

          // 정확한 셀 날짜 가져오기
          const elDateStr = info.el.getAttribute('data-date');

          // 공휴일 여부 확인
          let isHoliday = false;

          for (const year in holidays) {
            holidays[year].forEach(h => {
              if (h.dateymd === elDateStr) {
                isHoliday = true;
              }
            });
          }

          if (isHoliday) {

            const $cell = $(info.el);

            let $bg = $cell.find('.fc-daygrid-day-bg');

            // .fc-daygrid-bg-harness가 없으면 생성
            if ($bg.length && $bg.find('.fc-daygrid-bg-harness').length === 0) {
              const $harness = $('<div class="fc-daygrid-bg-harness" style="left: 0px; right: 0px;"></div>');
              $bg.append($harness);
            }

            const $harness = $bg.find('.fc-daygrid-bg-harness');

            if ($harness.length && $harness.find('.fc-non-business').length === 0) {
              $('<div class="fc-non-business"></div>').appendTo($harness);
            }
          }
        },


        eventDidMount: function(info) {
          const deleteBtn = document.createElement('button');
          deleteBtn.innerText = '삭제';
          deleteBtn.className = 'delete-btn';
          deleteBtn.onclick = function(e) {
            e.stopPropagation();
            if (confirm('정말 삭제하시겠습니까?')) {
              $.post('./holiday_action.php', {
                mode: 'delete',
                id: info.event.id
              }, function() {
                calendar.refetchEvents();
              });
            }
          };
          info.el.querySelector('.fc-event-title').appendChild(deleteBtn);
        }

      });

      calendar.render();


      $('#holiday-add-btn').on('click', function() {
        const title = $('#holiday-title').val().trim();
        const date = $('#holiday-date').val();
        const type = $('input[name="holiday-type"]:checked').val();

        if (!title || !date) {
          alert('날짜와 제목을 모두 입력하세요.');
          return;
        }

        $.post('./holiday_action.php', {
          mode: 'add',
          title,
          date,
          type
        }, function(res) {
          if (res === 'ok') {

            // loadHolidayList();

            $('#holiday-title').val('');
            $('#holiday-date').val('');

            closeModal();
            calendar.refetchEvents();

          } else {
            alert('추가 실패: ' + res);
          }
        });
      });


    });
  });

  function openModal(dateStr) {

    $("#holiday-date").val(dateStr);
    $('#subscription_modal1').modal();

  }

  function closeModal() {
    $.modal.close();
  }
</script>

<?php
include_once G5_ADMIN_PATH . '/admin.tail.php';
