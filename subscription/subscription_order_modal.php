<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 정기구독 설정 불러오기
// 배송주기
$subscription_info_inputs = get_subscription_info_inputs();

// 이용횟수
$subscription_use_inputs = get_subscription_use_inputs();   
?>

<div class="<?php if (defined('IS_SUBSCRIPTION_ORDER_FORM') && IS_SUBSCRIPTION_ORDER_FORM) { ?>subscription-form order-table<?php } ?>">
    <div class="row">
    
        <?php if (defined('G5_IS_SUBSCRIPTION_ITEM') && G5_IS_SUBSCRIPTION_ITEM) { ?>
            <h2 class="subscription-title">
                정기구독 배송일 선택
            </h2>
            <?php if (get_subs_option('su_subscription_content_first')) {   // 정기결제 폼 첫번째 안내문이 있다면 ?>
            <div class="subscription-desc1">
                <?php echo conv_content(get_subs_option('su_subscription_content_first'), 1); ?>
            </div>
            <?php } ?>
        <?php } ?>
        
        <div class="cell header">
            <label for=""><?php echo subscription_item_delivery_title(); ?></label>
        </div>
        <div class="cell">
        <?php if (get_subs_option('su_chk_user_delivery')) { ?>
            <input id="od_subscription_select_data" name="od_subscription_select_data" type="number" inputmode="numeric" placeholder="숫자" max="365" maxlength="3" value="<?php echo (isset($aparams_array['delivery_cycle']) && $aparams_array['delivery_cycle']) ? (int) $aparams_array['delivery_cycle'] : get_subs_option('su_user_delivery_default_day'); ?>" class="frm_input">
            <span class="od_subscription_days">일</span>
        <?php } else { ?>

            <?php if (get_subs_option('su_output_display_type')) {  // 버튼식 ?>
                <div class="su-display-btns">
                <?php 
                foreach ($subscription_info_inputs as $key=>$opt) {
                    if (! $opt['opt_use']) {
                        continue;
                    }
                    
                    $opt_print = $opt['opt_print'] ? $opt['opt_print'] : $opt['opt_input'].' 일마다';
                    
                    if (!$opt['opt_print']) {
                        
                        if (!$opt['opt_input']) $opt['opt_input'] = 1;
                        
                        if ($opt['opt_date_format'] === 'week') {
                            
                            $opt_print = (int) $opt['opt_input'].'주에 ';
                            
                            if (isset($opt['opt_etc']) && $opt['opt_etc']) {
                                $opt_print .= get_subscriptionDayOfWeek($opt['opt_etc']);
                            } else {
                                $opt_print .= '한 번';
                            }
                            /*
                            $opt_print = '1주에 한 번';
                            if (isset($opt['opt_etc']) && $opt['opt_etc']) {
                                $cg_yoil = get_subscriptionDayOfWeek($opt['opt_etc']);
                                $opt_print = '1주에 '.$cg_yoil;
                            }
                            */
                        } else if($opt['opt_date_format'] === 'month') {
                            
                            $opt_print = (int) $opt['opt_input'].'달에 ';
                            
                            if (isset($opt['opt_etc']) && $opt['opt_etc']) {
                                $opt_print .= (int) $opt['opt_etc'].'일';
                            } else {
                                $opt_print .= '한 번';
                            }
                            /*
                            $opt_print = '1달에 한 번';
                            if (isset($opt['opt_etc']) && $opt['opt_etc']) {
                                $cg_yoil = get_subscriptionMonthDay((int) $opt['opt_etc']);
                                $opt_print = '1달에 '.$cg_yoil;
                            }
                            */
                        } else if($opt['opt_date_format'] === 'year') {
                            $opt_print = '1년에 한 번';
                            
                        }
                    }
                    
                    if ($opt['opt_input'] || $opt['opt_date_format']) {
                        $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print);
                        $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print);
                    }
                    
                    $checked = (isset($aparams_array['delivery_cycle']) && $aparams_array['delivery_cycle'] === $key.'||'.$opt['opt_input'].'||'.$opt['opt_date_format'].'||'.$opt['opt_etc']) ? 'checked' : '';
                ?>
                    <input type="radio" id="od_subscription_select_data_<?php echo $key; ?>" class="sound_only" name="od_subscription_select_data" <?php echo $checked; ?> value="<?php echo get_text($key.'||'.$opt['opt_input'].'||'.$opt['opt_date_format'].'||'.$opt['opt_etc']); ?>">
                    <label for="od_subscription_select_data_<?php echo $key; ?>" class="select-icon"><span><?php echo $opt_print; ?></span></label>
                <?php } ?>
                </div>
            <?php } else {  // 셀렉트박스 ?>
                <select id="od_subscription_select_data" class="frm_input" name="od_subscription_select_data">
                    <option value="" selected="" disabled="">선택해주세요</option>
                <?php
                foreach ($subscription_info_inputs as $key=>$opt) {
                    if (! $opt['opt_use']) {
                        continue;
                    }

                    $opt_print = $opt['opt_print'] ? $opt['opt_print'] : $opt['opt_input'].' 일마다';

                    if ($opt['opt_input'] || $opt['opt_date_format']) {
                        $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print);
                        $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print);
                    }
                ?>
                    <option value="<?php echo get_text($key.'||'.$opt['opt_input'].'||'.$opt['opt_date_format']); ?>"><?php echo $opt_print; ?></option>
                <?php } ?>
                </select>
            <?php } ?>
                        
        <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="cell header"><label for="">이용횟수</label></div>

        <div class="cell">
        <?php if (get_subs_option('su_output_display_type')) {  // 버튼식 ?>
            <div class="su-display-btns">
                <?php foreach ($subscription_use_inputs as $key=>$use) {
                if (! $use['num_use']) {
                    continue;
                }

                $use_print = $use['use_print'] ? $use['use_print'] : $use['use_input'].' 회';

                if ($use['use_input']) {
                    $use_print = str_replace("{입력}", $use['use_input'], $use_print);
                }
                
                $checked = (isset($aparams_array['usage_count']) && $aparams_array['usage_count'] === $key.'||'.$use['use_input']) ? 'checked' : '';
                ?>
                <input type="radio" id="od_subscription_select_number_<?php echo $key; ?>" class="sound_only" name="od_subscription_select_number" <?php echo $checked; ?> value="<?php echo get_text($key.'||'.$use['use_input']); ?>">
                <label for="od_subscription_select_number_<?php echo $key; ?>" class="select-icon"><span><?php echo $use_print; ?></span></label>
                <?php } ?>
            </div>
        <?php } else {  // 셀렉트박스 ?>
            <select id="od_subscription_select_number" class="frm_input" name="od_subscription_select_number">
                <option value="" selected="" disabled="">선택해주세요</option>
            <?php
            foreach ($subscription_use_inputs as $key=>$use) {
                if (! $use['num_use']) {
                    continue;
                }

                $use_print = $use['use_print'] ? $use['use_print'] : $use['use_input'].' 회';

                if ($use['use_input']) {
                    $use_print = str_replace("{입력}", $use['use_input'], $use_print);
                }
            ?>
                <option value="<?php echo get_text($key.'||'.$use['use_input']); ?>"><?php echo $use_print; ?></option>
            <?php } ?>
            </select>
        <?php } ?>
        </div>
    </div>
    
    <div class="row">
        <?php if (get_subs_option('su_hope_date_use')) { // 배송희망일 사용 ?>
        <div class="cell header">
            <label for="od_hope_date_print">첫 희망배송일</label>
        </div>
        <div class="cell jquery-pg-datepicker">
            <input type="hidden" name="od_hope_date" value="<?php echo isset($aparams_array['hope_delivery_date']) ? get_text($aparams_array['hope_delivery_date']) : ''; ?>" id="od_hope_date" class="frm_input" maxlength="10">
            <div id="od_hope_date_print" class="jquery-datepicker"></div>
        </div>
        <?php } ?>
    </div>
    
    <?php if (defined('G5_IS_SUBSCRIPTION_ITEM') && G5_IS_SUBSCRIPTION_ITEM) { ?>
    <div>
        <?php if (get_subs_option('su_subscription_content_end')) {   // 정기결제 폼 마지막 안내문이 있다면 ?>
        <div class="subscription-desc-end">
            <?php echo conv_content(get_subs_option('su_subscription_content_end'), 1); ?>
        </div>
        <?php } ?>
            
        <div class="form-box-btns">
            <button type="submit" onclick="document.pressed=this.value;" value="정기구독신청" class="sit_btn_subscription sit_btn_buy">정기구독 신청하기</button>
            <!-- <a href="#" class="sit_btn_subscription sit_btn_buy">정기구독 신청하기</a> -->
        </div>
    </div>
    <?php } ?>
</div>
                
<script>
    
    /*
    function getBusinessDaysBefore(date, businessDays) {
        // date: 기준 날짜 (Date 객체)
        // businessDays: 몇 영업일 전으로 이동할 것인지
        // holidays: 공휴일 배열 (YYYY-MM-DD 형식의 문자열 배열)
        while (businessDays > 0) {
            date.setDate(date.getDate() - 1); // 하루 전으로 이동
            const dayOfWeek = date.getDay(); // 요일 (0: 일요일, 6: 토요일)
            
            // 날짜 포맷 (YYYY-MM-DD)
            const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
            
            // 주말(토, 일)이 아니고 공휴일이 아니면 영업일로 간주
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                businessDays--;
            }
        }

        return date;
    }
    */
    
    <?php if (get_subs_option('su_chk_user_delivery')) { ?>
    jQuery(function($){
        $("input#od_subscription_select_data").on('input', function() {
            var $this = $(this),
                $this_val = parseInt($this.val()),
                this_length = $this.val().length,
                ml = parseInt($this.attr("maxlength"));
            
            // 입력 값이 비어있거나 1보다 작은 값이면 1로 설정
            if (isNaN($this_val) || $this_val < 1 || 365 < $this_val) {
                $this_val = "<?php echo get_subs_option('su_user_delivery_default_day'); ?>";
                $(this).val($this_val);
            }
            
            calculate_next_delivery_date();
        });
    });
    <?php } else { ?>
        
        $(document).on("click", "input[name='od_subscription_select_data']", function(e) {
            
            calculate_next_delivery_date();
            
        });
        
    <?php } ?>
    
    <?php if (get_subs_option('su_hope_date_use')) { ?>
        
        function getDateAfterDays(days) {
          const today = new Date(); // 오늘 날짜를 가져옵니다.
          today.setDate(today.getDate() + days); // 현재 날짜에 days(3일)를 더합니다.

          const year = today.getFullYear();
          const month = String(today.getMonth() + 1).padStart(2, '0'); // 월은 0부터 시작하므로 +1 필요
          const day = String(today.getDate()).padStart(2, '0'); // 일도 2자리로 포맷팅

          return `${year}-${month}-${day}`; // YYYY-MM-DD 형식으로 반환
        }
        
        var holidays = [
            '2024-01-01', // 신정
            '2024-02-09', '2024-02-10', '2024-02-11', '2024-02-12', // 설날 연휴
            '2024-03-01', // 삼일절
            '2024-05-05', '2024-05-06', // 어린이날 대체 공휴일
            '2024-06-06', // 현충일
            '2024-08-15', // 광복절
            '2024-09-16', '2024-09-17', '2024-09-18', // 추석 연휴
            '2024-10-03', // 개천절
            '2024-10-09', // 한글날
            '2024-12-25',  // 성탄절
            '2025-01-01', // 새해 첫날
            '2025-01-28', // 설날 연휴 시작
            '2025-01-29', // 설날
            '2025-01-30', // 설날 연휴 끝
            '2025-03-01', // 삼일절
            '2025-03-03', // 삼일절 대체공휴일
            '2025-05-05', // 어린이날 및 부처님오신날
            '2025-05-06', // 부처님오신날 대체공휴일
            '2025-06-06', // 현충일
            '2025-08-15', // 광복절
            '2025-10-03', // 개천절
            '2025-10-05', // 추석 연휴 시작
            '2025-10-06', // 추석
            '2025-10-07', // 추석 연휴 끝
            '2025-10-09', // 한글날
            '2025-12-25'  // 성탄절
        ];
        
        jQuery(function($) {

            var g5_yymmdd = "<?php echo G5_TIME_YMD; ?>";
            
            var $od_hope_date_print = $("#od_hope_date_print");
            $od_hope_date_print.datepicker({
                defaultDate: g5_yymmdd,
                dateFormat: "yy-mm-dd",
                inline: true,
                yearRange: "c-99:c+99",
			    beforeShowDay: function(date){
                    var today = new Date();
                    
                    // 오늘 날짜면 "오늘" 표시
                    if (date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && date.getDate() === today.getDate()) {
                        return [true, "today-highlight", "오늘"];
                    }
                    
                    // 토요일 일요일 제외
                    var dayOfWeek = date.getDay(); // 0: 일요일, 6: 토요일
                    var formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

                    // 주말(토, 일) 또는 공휴일이면 비활성화 (false 반환)
                    if (dayOfWeek === 0 || dayOfWeek === 6 || holidays.includes(formattedDate)) {
                        return [false, 'ui-state-disabled', '공휴일 또는 주말입니다.'];
                    }
                    
                    // 그 외의 날짜는 활성화
                    return [true, '', ''];
                },
                onSelect: function(dateText, inst) {
                    console.log(dateText, inst);
                    change_hope_date_val();
                    
                    $("#od_hope_date").val(dateText);
                    calculate_next_delivery_date();
                },
                //minDate: "+<?php echo (int) get_subs_option('su_hope_date_after'); ?>d",
                //maxDate: "+<?php echo (int) get_subs_option('su_hope_date_after') + 30; ?>d"
                minDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after')); ?>"),
                maxDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after') + 30); ?>")
            });
            
            <?php if (isset($aparams_array['hope_delivery_date']) && $aparams_array['hope_delivery_date']) { ?>
                $od_hope_date_print.datepicker("setDate", "<?php echo $aparams_array['hope_delivery_date']; ?>");
            <?php } ?>
                
            /*
            $("#od_hope_date").datepicker({
                changeMonth: true,
                changeYear: true,
                dateFormat: "yy-mm-dd",
                showButtonPanel: true,
                yearRange: "c-99:c+99",
                minDate: "+<?php echo (int) get_subs_option('su_hope_date_after'); ?>d;",
                maxDate: "+<?php echo (int) get_subs_option('su_hope_date_after') + 6; ?>d;"
            });
            */
            
            function change_hope_date_val() {
                var before_pay_date = "<?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?>";
                
                if (before_pay_date && parseInt(before_pay_date) > 0) {
                    
                    setTimeout(function(){
                        var od_hope_date_print = $od_hope_date_print.datepicker("getDate");
                        
                        if (od_hope_date_print) {
                            
                            var resultDate = getBusinessDaysBefore(new Date(od_hope_date_print), parseInt(before_pay_date));
                            
                            // alert(resultDate);
                            
                            var daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
                            var year = resultDate.getFullYear();
                            var month = resultDate.getMonth() + 1; // 월은 0부터 시작하므로 +1
                            var date = resultDate.getDate();
                            var dayOfWeek = daysOfWeek[resultDate.getDay()]; // 요일 가져오기

                            var formattedDate1 = `${month}월 ${date}일 (${dayOfWeek})`;
                            var formattedDate2 = `${year}년 ${month}월 ${date}일 (${dayOfWeek})`;
                            
                            jQuery(".set_pay_date").text(formattedDate1);
                            jQuery(".before_pay_date_tr").show();
                        }
                    }, 100);
                }
            }
            
            change_hope_date_val();
            
            /*
            $('#od_hope_date_print').pignoseCalendar({
                lang: 'ko',
                disabledWeekdays: [0, 6], // SUN (0), SAT (6)
                disabledDates: holidays,
                minDate: getDateAfterDays(<?php echo (int) get_subs_option('su_hope_date_after'); ?>),
                maxDate: getDateAfterDays(<?php echo (int) get_subs_option('su_hope_date_after') + 30; ?>)
            });
            */
            
            $(document).on("click", ".sit_btn_subscription", function(e){
                e.preventDefault();
                
                // 1. 입력값 가져오기
                const deliveryCycle = $("#od_subscription_select_data").val() || $("input[name='od_subscription_select_data']:checked").val();
                const usageCount = $("#od_subscription_select_number").val()  || $("input[name='od_subscription_select_number']:checked").val();
                const hopeDeliveryDate = $("#od_hope_date").val();
                
                // 2. 유효성 검사 (선택 사항)
                if (!deliveryCycle) {
                    alert("배송주기를 선택해주세요.");
                    return;
                }
                if (!usageCount) {
                    alert("이용횟수를 선택해주세요.");
                    return;
                }
                if (!hopeDeliveryDate) {
                    alert("희망배송일을 입력해주세요.");
                    return;
                }
                
                // 3. 숨겨진 폼에 값 할당
                document.getElementById("hidden_delivery_cycle").value = deliveryCycle;
                document.getElementById("hidden_usage_count").value = usageCount;
                document.getElementById("hidden_hope_delivery_date").value = hopeDeliveryDate;
                
                $("form[name='fitem']").submit();
            });
        });
        
    <?php } ?>
    
    function getNextDeliveryDate(date, businessDays = 0, interval='') {
        // date가 Date 객체가 아니면 변환
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        // 다음 영업일 계산 (businessDays는 0으로 고정)
        var nextBusinessDay = getNextBusinessDay(date, businessDays);
        
        console.log('nextBusinessDay : ' + nextBusinessDay);
        
        // 기준 날짜와 다음 영업일의 월 비교
        const inputMonth = date.getMonth(); // 0-11 (0: 1월, 11: 12월)
        const nextMonth = nextBusinessDay.getMonth();

        // interval이 'month'이고, 다음 영업일이 다른 달이면 직전 영업일로 변경
        if (interval === 'month' && nextMonth !== inputMonth) {
            // 직전 영업일 계산
            return getBusinessDaysBefore(date, 0);
        }

        return nextBusinessDay;
    }
    
    function getBusinessDaysBefore(date, businessDays = 0, holidays = []) {
        // date가 Date 객체가 아니면 변환
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        let prevDate = new Date(date); // 원본 변경 방지

        if (businessDays > 0) {
            // 지정된 영업일 수만큼 이전으로 이동
            while (businessDays > 0) {
                // 하루 전으로 이동
                prevDate.setDate(prevDate.getDate() - 1);

                const dayOfWeek = prevDate.getDay(); // 요일 (0: 일요일, 6: 토요일)
                const formattedDate = `${prevDate.getFullYear()}-${String(prevDate.getMonth() + 1).padStart(2, '0')}-${String(prevDate.getDate()).padStart(2, '0')}`;

                // 주말이 아니고 공휴일이 아니면 영업일 감소
                if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                    businessDays--;
                }
            }
        } else {
            // 영업일이 0이면 직전 영업일 찾기
            while (true) {
                const dayOfWeek = prevDate.getDay(); // 요일 (0: 일요일, 6: 토요일)
                const formattedDate = `${prevDate.getFullYear()}-${String(prevDate.getMonth() + 1).padStart(2, '0')}-${String(prevDate.getDate()).padStart(2, '0')}`;

                // 주말이 아니고 공휴일이 아니면 종료
                if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                    break;
                }

                // 하루 전으로 이동
                prevDate.setDate(prevDate.getDate() - 1);
            }
        }

        return prevDate;
    }

    function getNextBusinessDay(date, businessDays = 0, holidays = []) {
        // date가 Date 객체가 아니면 변환
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        let nextDate = new Date(date); // 원본 변경 방지

        if (businessDays > 0) {
            // 지정된 영업일 수만큼 이동
            while (businessDays > 0) {
                // 하루 증가
                nextDate.setDate(nextDate.getDate() + 1);

                const dayOfWeek = nextDate.getDay(); // 요일 (0: 일요일, 6: 토요일)
                const formattedDate = `${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}-${String(nextDate.getDate()).padStart(2, '0')}`;

                // 주말이 아니고 공휴일이 아니면 영업일 감소
                if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                    businessDays--;
                }
            }
        } else {
            // 영업일이 0이면 다음 영업일 찾기
            while (true) {
                const dayOfWeek = nextDate.getDay(); // 요일 (0: 일요일, 6: 토요일)
                const formattedDate = `${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}-${String(nextDate.getDate()).padStart(2, '0')}`;

                // 주말이 아니고 공휴일이 아니면 종료
                if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                    break;
                }

                // 하루 증가
                nextDate.setDate(nextDate.getDate() + 1);
            }
        }

        return nextDate;
    }
    
    /*
    function getNextBusinessDay(date, holidays = [], interval='') {
        // date가 Date 객체가 아니면 변환
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        let nextDate = new Date(date); // 원본 변경 방지

        while (true) {
            const dayOfWeek = nextDate.getDay(); // 요일 (0: 일요일, 6: 토요일)

            // 날짜 포맷 (YYYY-MM-DD)
            const formattedDate = `${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}-${String(nextDate.getDate()).padStart(2, '0')}`;

            // 주말이 아니고 공휴일이 아니면 종료
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                break;
            }

            nextDate.setDate(nextDate.getDate() + 1); // 하루 증가
        }

        return nextDate;
    }
    */
    /*
    function getNextBusinessDay(date) {
        // date: 기준 날짜 (Date 객체)
        // holidays: 공휴일 배열 (YYYY-MM-DD 형식의 문자열 배열)

        // 날짜 객체로 변환 (입력값 검사)
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        while (true) {
            const dayOfWeek = date.getDay(); // 요일 (0: 일요일, 6: 토요일)

            // 날짜 포맷 (YYYY-MM-DD)
            const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

            // 주말(토, 일)이 아니고 공휴일이 아니면 다음 영업일로 간주
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                break;
            }
            
            date.setDate(date.getDate() + 1); // 하루 앞으로 이동
        }

        return date;
    }
    */
    
    // 다음 예상 발송일 계산
    function calculate_next_delivery_date() {
        
        if (!$("#od_hope_date").length) {   // 희망배송일이 없으면 리턴
            return false;
        }
        
        if (! jQuery("#od_hope_date").val()) {
            jQuery("#od_hope_date").val($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker( "getDate" )));
        }
        
        var $od_subscription_select_data = jQuery("#od_subscription_select_data").val() || jQuery("input[name='od_subscription_select_data']:checked").val(),
            $od_subscription_select_number = jQuery("#od_subscription_select_number").val(),
            $od_hope_date_print = $("#od_hope_date").val();
        
        if ($od_subscription_select_number && $od_subscription_select_number < 2) {     // 이용횟수가 1회이면 리턴
            return false;
        }
        
        var $next_el = $('.jquery-pg-datepicker .next-delivery-date-el').length ? $(".jquery-pg-datepicker .next-delivery-date-el") : $('<div class="next-delivery-date-el"></div>').appendTo(".jquery-pg-datepicker");
        
        // 기준 날짜 계산
        
        console.log( $od_hope_date_print );
        let baseDate = new Date($od_hope_date_print);
        
        if (typeof $od_subscription_select_data === 'undefined') {
            return false;
        }
        
        console.log( $od_subscription_select_data );
        
        var select_interval = "";
        
        if ($od_subscription_select_data && $od_subscription_select_data.includes("||")) {
            
            let [no, plus, interval, etc_data] = $od_subscription_select_data.split("||");
            
            console.log(no, plus, interval, etc_data);
            
            interval = interval || "day";
            plus = Math.abs(parseInt(plus, 10)) || 1;
            
            let isCheckBefore = false;
            
            console.log('plus : ' + plus);
            
            select_interval = interval;
            
            switch (interval) {
                case "day":
                    baseDate.setDate(baseDate.getDate() + plus);
                    break;
                case "week":
                    if (typeof etc_data !== 'undefined' && etc_data) {
                        /*
                        var targetDay = ["sun", "mon", "tue", "wed", "thu", "fri", "sat"].indexOf(etc_data); // 요일을 숫자로 변환 (0: 일요일, ..., 6: 토요일)
                        var currentDay = baseDate.getDay(); // 현재 요일 (0~6)
                        
                        console.log( currentDay, targetDay );
                        
                        var daysToAdd = (plus * 7) - (currentDay - targetDay + 7) % 7;
                        baseDate.setDate(baseDate.getDate() + daysToAdd);
                        */
                        
                        // 요일 매핑
                        const dayMap = {
                            "sun": 0,
                            "mon": 1,
                            "tue": 2,
                            "wed": 3,
                            "thu": 4,
                            "fri": 5,
                            "sat": 6
                        };
                        
                        // etc_data에서 목표 요일 가져오기 (없으면 기본값으로 월요일(1) 사용)
                        const targetDay = dayMap[etc_data] !== undefined ? dayMap[etc_data] : 1;
                        
                        // 현재 주의 일요일(주 시작)으로 이동 (일요일을 주의 시작으로 가정)
                        const currentDay = baseDate.getDay();
                        baseDate.setDate(baseDate.getDate() - currentDay); // 주의 첫 날(일요일)로
                        
                        // plus 주만큼 이동
                        baseDate.setDate(baseDate.getDate() + plus * 7);

                        // 목표 요일(목요일)로 조정
                        const newCurrentDay = baseDate.getDay();
                        const daysToAdd = (targetDay - newCurrentDay + 7) % 7;
                        baseDate.setDate(baseDate.getDate() + daysToAdd);
    
                    } else {
                        baseDate.setDate(baseDate.getDate() + plus * 7);
                    }
                    break;
                case "month":
                    baseDate.setMonth(baseDate.getMonth() + plus);
                
                    if (typeof etc_data !== 'undefined' && etc_data > 1) {
                        let targetDay = parseInt(etc_data, 10) || 0; // 목표 일자
                        
                        if (targetDay) {
                            let lastDayOfMonth = new Date(baseDate.getFullYear(), baseDate.getMonth() + 1, 0).getDate(); // 해당 월의 마지막 날짜
                            
                            // 목표 날짜가 해당 월의 마지막 날을 초과하면 마지막 날로 설정
                            if (targetDay > lastDayOfMonth) {
                                baseDate.setDate(lastDayOfMonth);
                            } else {
                                baseDate.setDate(targetDay);
                            }
                            
                            // 해당 월에 목표 일자가 없으면, 마지막 날짜로 설정
                            // baseDate.setDate(Math.min(targetDay, lastDayOfMonth));
                        }
                    }
                    
                    isCheckBefore = true;
                    break;
                case "year":
                    baseDate.setFullYear(baseDate.getFullYear() + plus);
                    isCheckBefore = true;
                    break;
                default:
                    throw new Error(`Unknown billing interval: ${interval}`);
            }
            
            // let formattedDate = baseDate.toISOString().slice(0, 19).replace("T", " ");

            // return isCheckBefore ? getBusinessDaysBefore(formattedDate) : getBusinessDaysNext(formattedDate);
            
            // const nextDeliveryDate = getNextDeliveryDate(formattedDate, holidays);
            
        } else {
            
            baseDate.setDate(baseDate.getDate() + parseInt($od_subscription_select_data)); // 몇일 이후 날짜 계산
            
        }
        
        console.log("다음 예상 배송일:" + baseDate);
        
        const nextDeliveryDate = getNextDeliveryDate(baseDate, 0, select_interval);
        

        $next_el.html("다음 예상 배송일 : " + nextDeliveryDate.toISOString().slice(0, 10));
            
        // $next_el.html("다음 예상 배송일 : " + nextDeliveryDate.toISOString().slice(0, 10));
        
        /*
        baseDate.setDate(baseDate.getDate() + parseInt($od_subscription_select_data)); // 몇일 이후 날짜 계산

        const nextDeliveryDate = getNextDeliveryDate(baseDate, holidays);
        
        // 결과 출력
        console.log('기준 날짜:', baseDate.toISOString().slice(0, 10));
        console.log('다음 배송일:', nextDeliveryDate.toISOString().slice(0, 10));

        $next_el.html("다음 예상 배송일 : " + nextDeliveryDate.toISOString().slice(0, 10));
        
        */
        
    }
    
    calculate_next_delivery_date();
    
    $("form[name='fitem']").on("form:valid", function() {
        
        if (document.pressed === "구독장바구니" || document.pressed === "정기구독신청" || document.pressed === "정기구독") {
            
            // form의 action을 구독 전용 URL로 변경
            this.action = "<?php echo G5_SUBSCRIPTION_URL; ?>/cartupdate.php";
            
            $("input[name='is_subscription']").val('1');
            
            if (document.pressed === "구독장바구니") {
                this.sw_direct.value = 0;
            } else if (document.pressed === "정기구독신청" || document.pressed === "정기구독") {
                this.sw_direct.value = 1;
            }
            
        } else {
            $("input[name='is_subscription']").val('');
        }
        
    });

</script>