<?php
$sub_menu = '600500';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '정기결제일정';
include_once G5_ADMIN_PATH . '/admin.head.php';

add_javascript('<script src="' . G5_JS_URL . '/fullcalendar/index.global.js"></script>', 2);
add_javascript('<script src="' . G5_JS_URL . '/fullcalendar/popper.min.js"></script>', 3);
add_javascript('<script src="' . G5_JS_URL . '/fullcalendar/tooltip.min.js"></script>', 3);
add_javascript('<script src="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.css">', 10);

?>
<div class="calendar-wrap">
  <div class="calendar-main">
    <div id="calendar" class="fullcalendar"></div>
    <!-- 로딩 오버레이와 스피너 -->
    <div class="loading-overlay" id="loadingOverlay">
      <div class="spinner"></div>
      <div class="loading-text">로딩 중...</div>
    </div>
  </div>
  <div class="calendar-side">
    <ul class="calendar-description">
      <li class="fc-daygrid-dot-event">
        <div class="fc-daygrid-event-dot" style="border-color: #ed0707;"></div> 결제된 정기결제내역입니다.
      </li>
      <li class="fc-daygrid-dot-event">
        <div class="fc-daygrid-event-dot" style="border-color: #3788d8;"></div> 앞으로 결제예정인 구독내역입니다.
      </li>
      <li class="fc-daygrid-dot-event">
        <div class="fc-daygrid-event-dot" style="border-color: #c3d4d4;"></div> 실패된 정기결제내역입니다.
      </li>
    </ul>
  </div>

</div>

<a href="#subscription_modal1" data-oid="" data-pid="" id="subscription-modal1-btn" data-modal class="sound_only">상세보기</a>
<div id="subscription_modal1" class="modal">
  <div class="modal_contents">
  </div>
</div>

<script>
  jQuery(function($) {
    $(document).on("click", "a[data-modal]", function(e) {
      e.preventDefault();

      var $this = $(this),
        pay_id = $this.attr("data-pid"),
        od_id = $this.attr("data-oid"),
        oDate = new Date(),
        action_url = g5_admin_url + "/subscription_admin/ajax.subscription_orders.php";

      if (pay_id) {
        formData = "pay_id=" + pay_id;
      } else if (od_id) {
        formData = "od_id=" + od_id;
      }

      var contentEl = $(".modal_contents");

      contentEl.html('');

      var ajax_var = $.ajax({
          type: "POST",
          url: action_url + "?t=" + oDate.getTime(),
          data: formData,
          dataType: 'json', // xml, html, script, json
          cache: false,
          success: function(data, status, xhr) {

            if (data === null) {
              alert("받아온 데이터가 없습니다.");
              return false;
            }

            if (data.error) { //실패

              alert(data.error);

            } else { //성공

              $this.modal();

              /*
              var obj = {
                  msg : sir_cm.cm_success_msg,
                  type : "success"
              }
              sir_cm.fn_load_comment( data.url, obj );

              if( $(".client-info button[id^='request']").length ){
                  $(".client-info button[id^='request']").attr("data-view", "1");
              }

              $("#fcomment").trigger("request_reset", 'write');
              */

              // 새로운 ul 요소 생성
              var innerEl = $("<div class='user-subscription-pay'></div>"),
                ulEl = $("<ul class='user-subscription-inner'></ul>");

              // JSON 데이터 순회하며 li 요소 생성 후 ul에 추가
              $.each(data, function(key, value) {
                // ulEl.append("<li><strong>" + key + ":</strong> " + value + "</li>");
              });

              /*
              var keys = {
                  "py_receipt_time": "결제시간",
                  "영수증출력": "",
                  "py_receipt_price": "결제금액",
                  };
              */

              var html = "",
                cartHTML = "";

              if (pay_id && data.pay_id) {
                html += '<div><a href="' + g5_admin_url + '/subscription_admin/payform.php?pay_id=' + data.pay_id + ' " class="btn btn_02">정기결제내역 바로가기</a></div>';

                var od_name = data.py_name,
                  od_hp = data.py_hp,
                  od_b_name = data.py_b_name,
                  od_b_tel = data.py_b_tel,
                  od_b_hp = data.py_b_hp,
                  od_b_full_address = data.py_b_full_address,
                  od_invoice = data.py_invoice,
                  od_delivery_company = data.py_delivery_company,
                  od_delivery_full_info = data.py_delivery_full_info,
                  od_invoice_time = data.py_invoice_time,
                  od_cart_price = data.py_cart_price,
                  od_send_cost = data.py_send_cost,
                  od_tot_price = data.py_tot_price,
                  od_test = data.py_test;

              } else if (od_id && data.od_id) {
                html += '<div><a href="' + g5_admin_url + '/subscription_admin/orderform.php?od_id=' + data.od_id + ' " class="btn btn_02">구독내역 바로가기</a></div>';

                var od_name = data.od_name,
                  od_hp = data.od_hp,
                  od_b_name = data.od_b_name,
                  od_b_tel = data.od_b_tel,
                  od_b_hp = data.od_b_hp,
                  od_b_full_address = data.od_b_full_address,
                  od_invoice = data.od_invoice,
                  od_delivery_company = data.od_delivery_company,
                  od_delivery_full_info = data.od_delivery_full_info,
                  od_invoice_time = data.od_invoice_time,
                  od_cart_price = data.od_cart_price,
                  od_send_cost = data.od_send_cost,
                  od_tot_price = data.od_tot_price,
                  od_test = data.od_test;
              }

              html += "<h3>주문하신 분</h3>";
              html += "<li><span class='th'>이름 :</span> " + od_name + "</li>";
              html += "<li><span class='th'>핸드폰 :</span> " + od_hp + "</li>";

              if (od_test) {
                html += "<li class='is_pay_test'>이 결제는 테스트로 결제되었습니다.</li>";
              }

              html += "<h3>받으시는 분</h3>";
              html += "<li><span class='th'>이름 :</span> " + od_b_name + "</li>";
              html += "<li><span class='th'>전화번호 :</span> " + od_b_tel + "</li>";
              html += "<li><span class='th'>핸드폰 :</span> " + od_b_hp + "</li>";
              html += "<li><span class='th'>주소 :</span> " + od_b_full_address + "</li>";

              html += "<h3>배송정보</h3>";
              if (od_invoice && od_delivery_company) {
                html += "<li><span class='th'>배송회사 :</span> " + od_delivery_full_info + "</li>";
                html += "<li><span class='th'>운송장번호 :</span> " + od_invoice + "</li>";
                html += "<li><span class='th'>배송일시 :</span> " + od_invoice_time + "</li>";
              } else {
                html += "<li class='is_not_delivery'>아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</li>";
              }

              html += "<li><span class='th'>주문총액 :</span> " + od_cart_price + "</li>";
              if (od_send_cost) {
                html += "<li><span class='th'>배송비 :</span> " + od_send_cost + "</li>";
              }
              html += "<li><span class='th'>총계 :</span> " + od_tot_price + "</li>";

              if (pay_id && data.pay_id) {
                html += "<h3>결제정보</h3>";
                html += "<li><span class='th'>주문번호 :</span> " + data.subscription_pg_id + "</li>";
                html += "<li><span class='th'>주문일시 :</span> " + data.py_time + "</li>";
                // html += "<li><span class='th'>결제방식 :</span> " + data.py_settle_case + "</li>";
                html += "<li><span class='th'>결제카드 :</span> " + data.py_settle_case + "</li>";
                html += "<li><span class='th'>결제금액 :</span> " + data.py_receipt_price + "</li>";
                html += "<li><span class='th'>결제일시 :</span> " + data.py_receipt_time + "</li>";
                html += "<li><span class='th'>승인번호 :</span> " + data.py_app_no + "</li>";
              }

              if (data.py_receipt_url) {
                html += "<li><span class='th'>영수증 :</span> <a href='" + data.py_receipt_url + "' target='_blank' class='subscription-receipt-view'>영수증클릭</a></li>";
              }

              ulEl.append(html);

              for (var i = 0; i < data.cart_infos.goods.length; i++) {
                  
                var productName = data.cart_infos.goods[i],
                  productPrice = 0,
                  img = "";

                try {
                  img = data.cart_infos.image_urls[i].img;
                } catch (error) {
                  img = "";
                }

                var productOption = data.cart_infos.it_options[i][0].ct_option;
                // var productPrice = data.cart_infos.it_options[i][0].tot_sell_price;
                var pioPrice = data.cart_infos.it_options[i][0].io_price;

                // let optionsHtml = data.cart_infos.it_options.map(opt => `<div>${opt.option} (수량: ${opt.qty}, 가격: ${opt.price}원${opt.point ? `, 포인트: ${opt.point}` : ''})</div>`).join('');

                var optionsHtml = '';

                data.cart_infos.it_options[i].forEach(function(opt) {

                  productPrice += parseInt(opt.opt_price);

                  optionsHtml += '<div>' + opt.ct_option + ' (수량: ' + opt.ct_qty + ', 가격: ' + opt.opt_price + '원' + (opt.point ? ', 포인트: ' + opt.point : '') + ')</div>';
                });

                productPrice = productPrice ? number_format(productPrice) : 0;

                cartHTML += `
                            <div class="product-item">
                                <div class="product-img">${img}</div>
                                <div class="product-info">
                                    <div class="product-name"><a href="#">${productName}</a></div>
                                    <div class="product-options">${optionsHtml}</div>
                                </div>
                                <div class="product-meta">
                                    <div>가격: ${productPrice}원</div>
                                </div>
                            </div>
                        `;

              }

              if (cartHTML) {
                innerEl.append('<div class="product-list">' + cartHTML + '</div>');
              }

              innerEl.append(ulEl);

              // 기존 .content 내부에 추가
              contentEl.html(innerEl);

            }

          },
          error: function(request, status, error) {
            //alert(sir_cm.cm_false_msg+request.responseText);
            //sir_cm.waiting = false;
          }
        })
        .always(function() {
          /*
          if(typeof(a[0].captcha_key) != 'undefined'){
              $(a[0]).find("#captcha_reload").trigger("click");
          }
          */
        });
    });

    $(document).ready(function() {

      $(document).on('keydown mousedown', function(e) {
        const $popover = $('.fc-popover');
        if ($popover.length === 0) return; // 팝오버 없으면 무시

        const $target = $(e.target);
        const $insidePopover = $target.closest('.jquery-modal.blocker');

        if ($insidePopover.length) {
          // 팝오버 외부 클릭 시 닫히지 않도록 이벤트 차단
          e.stopImmediatePropagation();
          e.stopPropagation();
          e.preventDefault();
        }
      });

      // var calendarEl = document.getElementById('calendar');
      var calendarEl = $('#calendar')[0]; // jQuery 객체에서 DOM 요소로 변환
      var $loadingOverlay = $('#loadingOverlay'); // 로딩 오버레이 jQuery 객체

      var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
          start: 'title', // will normally be on the left. if RTL, will be on the right
          center: '',
          end: 'today prev,next' // will normally be on the right. if RTL, will be on the left
        },
        businessHours: true, // display business hours
        editable: true,
        selectable: true,
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
        //dayMaxEventRows: 5, // 최대 ?줄까지 이벤트 표시,
        dayMaxEvents: 7,
        eventClick: function(info) {

          var $subscription_btn = $("#subscription-modal1-btn");

          $subscription_btn.attr("data-oid", "");
          $subscription_btn.attr("data-pid", "");

          if (info.event.extendedProps.oid) {
            $subscription_btn.attr("data-oid", info.event.extendedProps.oid);
          } else if (info.event.extendedProps.pid) {
            $subscription_btn.attr("data-pid", info.event.extendedProps.pid);
          }

          $subscription_btn.trigger("click");
        },
        datesSet: function() {
          setTimeout(() => {
            document.querySelectorAll('.fc-more-link').forEach((btn) => {
              btn.addEventListener('click', function() {
                // More 버튼이 클릭되었습니다
                
              });
            });
          }, 100); // 일정이 렌더링될 때까지 약간의 지연을 줌
        },
        locale: 'ko', // 한국어 설정
        loading: function(isLoading) {
          if (isLoading) {
            // 로딩 중... 로딩 스피너 표시 가능
            $loadingOverlay.fadeIn(200); // 부드럽게 표시
          } else {
            // 로딩 완료시 로딩 스피너 숨김
            $loadingOverlay.fadeOut(200); // 부드럽게 숨김
          }
        },
        eventOrder: function(a, b) {
          const colorPriority = {
            '#ed0707': 1,
            '#c3d4d4': 2,
            '#3788d8': 3
          };

          const priorityA = colorPriority[a.backgroundColor] || 99;
          const priorityB = colorPriority[b.backgroundColor] || 99;

          return priorityA - priorityB;
        },
        events: function(fetchInfo, successCallback, failureCallback) {
            
          // AJAX 요청
          $.ajax({
            url: g5_admin_url + '/subscription_admin/ajax.subscription_search.php', // 서버 API 엔드포인트 (예: 백엔드 URL)
            method: 'GET',
            cache: false, // 캐싱 비활성화
            data: {
              start: fetchInfo.startStr.split("T")[0], // 현재 뷰의 시작 날짜 
              end: fetchInfo.endStr.split("T")[0] // 현재 뷰의 종료 날짜 
            },
            success: function(response) {
              // 서버에서 받은 데이터를 FullCalendar 이벤트 형식으로 변환
              var events = response.map(function(item) {
                  
                return {
                  title: item.title,
                  description: item.title,
                  start: item.start,
                  end: item.end || null, // end가 없는 경우 null 처리
                  extendedProps: {
                    oid: item.oid || null,
                    pid: item.pid || null
                  },
                  backgroundColor: item.color || '#3788d8' // 기본 색상 지정
                };
              });

              // color 기준으로 정렬
              //events.sort(function(a, b) {
              //    return a.backgroundColor.localeCompare(b.backgroundColor);
              //});

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
        },
        eventDidMount: function(info) {

          // a 태그에 data-id 추가
          info.el.setAttribute('data-oid', info.event.extendedProps.oid);

          var tooltip = new Tooltip(info.el, {
            title: info.event.extendedProps.description,
            placement: 'top',
            trigger: 'hover',
            container: 'body'
          });

          /*
          const popoverEl = document.querySelector('.fc-popover');
          
          console.log('popoverEl', popoverEl);
          
          if (popoverEl) {
            popoverEl.addEventListener('click', function(e) {
              e.stopPropagation(); // 팝오버 안 클릭 시 닫히지 않게
            });
          }
          */

          // 이벤트가 DOM에 추가될 때 실행
          info.el.addEventListener('click', function() {
            // 팝오버가 열릴 때 실행될 내용

            // alert("cccc");
          });
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

      /*
          // 문서 전체 클릭 이벤트 감지
          document.addEventListener('click', function(e) {
            const popover = document.querySelector('.fc-popover');
            if (!popover) return; // 팝오버가 없으면 무시

            const closeButton = e.target.closest('.fc-popover-close');
            const isInsidePopover = e.target.closest('.fc-popover');
            
            console.log('closeButton', closeButton);
            console.log('isInsidePopover', isInsidePopover);
            
            if (closeButton) {
              // "X" 버튼 클릭 시 팝오버 닫기
              popover.remove(); // 팝오버 제거
            } else if (!isInsidePopover) {
              // 팝오버 외부 클릭 시 닫히지 않도록 이벤트 차단
              e.stopPropagation();
              e.preventDefault();
              alert(1);
            }
            // 팝오버 내부 클릭 시에는 기본 동작 유지
          }, true); // 캡처 단계에서 실행
      
      
            document.addEventListener('click', function (e) {
              const isPopover = e.target.closest('.fc-popover');
              const isCloseButton = e.target.closest('.fc-popover .fc-popover-close');

              if (isPopover && !isCloseButton) {
                // 팝오버 안을 클릭했지만 close 버튼은 아님 -> 이벤트 전파 막아서 닫히지 않게
                e.stopImmediatePropagation();
              }
            }, true);
        */

      /*
      document.addEventListener('click', function(e) {
        // 팝오버 닫기 버튼 또는 팝오버 외부 클릭 이벤트를 가로채기
        if (e.target.closest('.fc-popover-close') || 
            (!e.target.closest('.fc-popover') && document.querySelector('.fc-popover'))) {
          e.stopPropagation();
          e.preventDefault();
          
          alert('12');
          
          return false;
        }
      }, true); // 캡처 단계에서 이벤트 처리
      */


    });
  });
</script>

<?php
include_once G5_ADMIN_PATH . '/admin.tail.php';
