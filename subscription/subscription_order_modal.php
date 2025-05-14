<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 차단

// 정기구독 설정 불러오기
// 배송주기 옵션
$subscription_info_inputs = get_subscription_info_inputs();

// 이용횟수 옵션
$subscription_use_inputs = get_subscription_use_inputs();   
?>

<div class="<?php echo (defined('IS_SUBSCRIPTION_ORDER_FORM') && IS_SUBSCRIPTION_ORDER_FORM) ? 'subscription-form order-table' : ''; ?>">
    <div class="row">
    
        <?php if (defined('G5_IS_SUBSCRIPTION_ITEM') && G5_IS_SUBSCRIPTION_ITEM) { ?>
            <h2 class="subscription-title">
                정기구독 배송일 선택
            </h2>
            <?php if ($first_content = get_subs_option('su_subscription_content_first')) { // 정기결제 폼 첫 번째 안내문 표시 ?>
            <div class="subscription-desc1">
                <?php echo conv_content($first_content, 1); // 첫 번째 안내문 내용 변환 및 출력 ?>
            </div>
            <?php } ?>
        <?php } ?>
        
        <div class="cell header">
            <label><?php echo subscription_item_delivery_title(); // 배송주기 제목 표시 ?></label>
        </div>
        <div class="cell">
        <?php if (get_subs_option('su_chk_user_delivery')) { // 사용자 직접 입력 배송주기 허용 ?>
            <input id="od_subscription_select_data" name="od_subscription_select_data" type="number" inputmode="numeric" placeholder="숫자" max="365" maxlength="3" 
                   value="<?php echo isset($aparams_array['delivery_cycle']) ? (int)$aparams_array['delivery_cycle'] : get_subs_option('su_user_delivery_default_day'); ?>" class="frm_input">
            <span class="od_subscription_days">일</span>
        <?php } else { ?>

            <?php if (get_subs_option('su_output_display_type')) { // 버튼 형식 출력 ?>
                <div class="su-display-btns">
                <?php 
                foreach ($subscription_info_inputs as $key => $opt) {
                    if (!$opt['opt_use']) continue; // 비활성화 옵션 제외
                    
                    $opt_print = $opt['opt_print'] ?: $opt['opt_input'] . ' 일마다'; // 기본 출력: 입력값 + '일마다'
                     
                    if (!$opt['opt_print']) {
                        $opt['opt_input'] = $opt['opt_input'] ?: 1; // 입력값 없으면 1로 설정
                        
                        if ($opt['opt_date_format'] === 'week') {
                            $opt_print = (int)$opt['opt_input'] . '주에 '; // 주 단위 출력
                            $opt_print .= isset($opt['opt_etc']) && $opt['opt_etc'] ? get_subscriptionDayOfWeek($opt['opt_etc']) : '한 번';
                        } elseif ($opt['opt_date_format'] === 'month') {
                            $opt_print = (int)$opt['opt_input'] . '달에 '; // 월 단위 출력
                            $opt_print .= isset($opt['opt_etc']) && $opt['opt_etc'] ? (int)$opt['opt_etc'] . '일' : '한 번';
                        } elseif ($opt['opt_date_format'] === 'year') {
                            $opt_print = '1년에 한 번'; // 연 단위 출력
                        }
                    }
                    
                    if ($opt['opt_input'] || $opt['opt_date_format']) {
                        $opt_print = str_replace("{입력}", (int)$opt['opt_input'], $opt_print); // 입력값 치환
                        $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print); // 주기 치환
                        
                        $opt_etc_str = '';
                        if ($opt['opt_etc']) {
                            $opt_etc_str = $opt['opt_date_format'] === 'week' ? get_subscriptionDayOfWeek($opt['opt_etc']) : (int)$opt['opt_etc'] . '일'; // 기타 정보 처리
                        }
                        $opt_print = str_replace("{기타}", $opt_etc_str, $opt_print); // 기타 정보 치환
                    }
                    
                    $checked = (isset($aparams_array['delivery_cycle']) && $aparams_array['delivery_cycle'] === "$key||{$opt['opt_input']}||{$opt['opt_date_format']}||{$opt['opt_etc']}") ? 'checked' : ''; // 선택 여부 확인
                ?>
                    <input type="radio" id="od_subscription_select_data_<?php echo $key; ?>" class="sound_only" name="od_subscription_select_data" <?php echo $checked; ?> 
                           value="<?php echo get_text("$key||{$opt['opt_input']}||{$opt['opt_date_format']}||{$opt['opt_etc']}"); ?>">
                    <label for="od_subscription_select_data_<?php echo $key; ?>" class="select-icon"><span><?php echo $opt_print; ?></span></label>
                <?php } ?>
                </div>
            <?php } else { // 셀렉트 박스 형식 출력 ?>
                <select id="od_subscription_select_data" class="frm_input" name="od_subscription_select_data">
                    <option value="" selected disabled>선택해주세요</option>
                <?php
                foreach ($subscription_info_inputs as $key => $opt) {
                    if (!$opt['opt_use']) continue; // 비활성화 옵션 제외

                    $opt_print = $opt['opt_print'] ?: $opt['opt_input'] . ' 일마다'; // 기본 출력 설정

                    if (!$opt['opt_print']) {
                        $opt['opt_input'] = $opt['opt_input'] ?: 1; // 입력값 없으면 1로 설정
                        
                        if ($opt['opt_date_format'] === 'week') {
                            $opt_print = (int)$opt['opt_input'] . '주에 '; // 주 단위 출력
                            $opt_print .= isset($opt['opt_etc']) && $opt['opt_etc'] ? get_subscriptionDayOfWeek($opt['opt_etc']) : '한 번';
                        } elseif ($opt['opt_date_format'] === 'month') {
                            $opt_print = (int)$opt['opt_input'] . '달에 '; // 월 단위 출력
                            $opt_print .= isset($opt['opt_etc']) && $opt['opt_etc'] ? (int)$opt['opt_etc'] . '일' : '한 번';
                        } elseif ($opt['opt_date_format'] === 'year') {
                            $opt_print = '1년에 한 번'; // 연 단위 출력
                        }
                    }
                    
                    if ($opt['opt_input'] || $opt['opt_date_format']) {
                        $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print); // 입력값 치환
                        $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print); // 주기 치환
                        
                        $opt_etc_str = $opt['opt_etc'] ? ($opt['opt_date_format'] === 'week' ? get_subscriptionDayOfWeek($opt['opt_etc']) : (int)$opt['opt_etc'] . '일') : ''; // 기타 정보 처리
                        $opt_print = str_replace("{기타}", $opt_etc_str, $opt_print); // 기타 정보 치환
                    }
                ?>
                    <option value="<?php echo get_text("$key||{$opt['opt_input']}||{$opt['opt_date_format']}"); ?>"><?php echo $opt_print; ?></option>
                <?php } ?>
                </select>
            <?php } ?>
        <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="cell header"><label>이용횟수</label></div>
        <div class="cell">
        <?php if (get_subs_option('su_output_display_type')) { // 버튼 형식 출력 ?>
            <div class="su-display-btns">
                <?php foreach ($subscription_use_inputs as $key => $use) {
                    if (!$use['num_use']) continue; // 비활성화 옵션 제외

                    $use_print = $use['use_print'] ?: $use['use_input'] . ' 회'; // 기본 출력 설정

                    if ($use['use_input']) {
                        $use_print = str_replace("{입력}", $use['use_input'], $use_print); // 입력값 치환
                    }
                    
                    $checked = (isset($aparams_array['usage_count']) && $aparams_array['usage_count'] === "$key||{$use['use_input']}") ? 'checked' : ''; // 선택 여부 확인
                ?>
                    <input type="radio" id="od_subscription_select_number_<?php echo $key; ?>" class="sound_only" name="od_subscription_select_number" <?php echo $checked; ?> 
                           value="<?php echo get_text("$key||{$use['use_input']}"); ?>">
                    <label for="od_subscription_select_number_<?php echo $key; ?>" class="select-icon"><span><?php echo $use_print; ?></span></label>
                <?php } ?>
            </div>
        <?php } else { // 셀렉트 박스 형식 출력 ?>
            <select id="od_subscription_select_number" class="frm_input" name="od_subscription_select_number">
                <option value="" selected disabled>선택해주세요</option>
            <?php
            foreach ($subscription_use_inputs as $key => $use) {
                if (!$use['num_use']) continue; // 비활성화 옵션 제외

                $use_print = $use['use_print'] ?: $use['use_input'] . ' 회'; // 기본 출력 설정

                if ($use['use_input']) {
                    $use_print = str_replace("{입력}", $use['use_input'], $use_print); // 입력값 치환
                }
            ?>
                <option value="<?php echo get_text("$key||{$use['use_input']}"); ?>"><?php echo $use_print; ?></option>
            <?php } ?>
            </select>
        <?php } ?>
        </div>
    </div>
    
    <div class="row">
        <?php if (get_subs_option('su_hope_date_use')) { // 배송 희망일 사용 설정 ?>
        <div class="cell header">
            <label for="od_hope_date_print">첫 희망배송일</label>
        </div>
        <div class="cell jquery-pg-datepicker">
            <input type="hidden" name="od_hope_date" value="<?php echo isset($aparams_array['hope_delivery_date']) ? get_text($aparams_array['hope_delivery_date']) : ''; ?>" 
                   id="od_hope_date" class="frm_input" maxlength="10">
            <div id="od_hope_date_print" class="jquery-datepicker"></div>
        </div>
        <?php } ?>
    </div>
    
    <?php if (defined('G5_IS_SUBSCRIPTION_ITEM') && G5_IS_SUBSCRIPTION_ITEM) { ?>
    <div>
        <?php if ($end_content = get_subs_option('su_subscription_content_end')) { // 정기결제 폼 마지막 안내문 표시 ?>
        <div class="subscription-desc-end">
            <?php echo conv_content($end_content, 1); // 마지막 안내문 내용 변환 및 출력 ?>
        </div>
        <?php } ?>
            
        <div class="form-box-btns">
            <button type="submit" onclick="document.pressed=this.value;" value="정기구독신청" class="sit_btn_subscription sit_btn_buy">정기구독 신청하기</button>
        </div>
    </div>
    <?php } ?>
</div>
                
<script>
    // 지정된 일수 후의 날짜 계산
    function getDateAfterDays(days) {
        const today = new Date(); // 오늘 날짜 가져오기
        today.setDate(today.getDate() + days); // 지정된 일수 추가
        return `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`; // YYYY-MM-DD 형식 반환
    }
    
    // 한국 공휴일 배열 (YYYY-MM-DD 형식)
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
        '2024-12-25', // 성탄절
        '2025-01-01', // 신정
        '2025-01-28', '2025-01-29', '2025-01-30', // 설날 연휴
        '2025-03-01', '2025-03-03', // 삼일절 및 대체공휴일
        '2025-05-05', '2025-05-06', // 어린이날 및 부처님오신날
        '2025-06-06', // 현충일
        '2025-08-15', // 광복절
        '2025-10-03', // 개천절
        '2025-10-05', '2025-10-06', '2025-10-07', // 추석 연휴
        '2025-10-09', // 한글날
        '2025-12-25'  // 성탄절
    ];
    
    // 이전 영업일 계산 (주말 및 공휴일 제외)
    function getBusinessDaysBefore(date, businessDays = 0) {
        if (!(date instanceof Date)) date = new Date(date); // Date 객체로 변환
        let prevDate = new Date(date); // 원본 날짜 복사

        while (businessDays > 0) {
            prevDate.setDate(prevDate.getDate() - 1); // 하루 전으로 이동
            const dayOfWeek = prevDate.getDay(); // 요일 확인 (0: 일요일, 6: 토요일)
            const formattedDate = `${prevDate.getFullYear()}-${String(prevDate.getMonth() + 1).padStart(2, '0')}-${String(prevDate.getDate()).padStart(2, '0')}`;
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                businessDays--; // 영업일 감소
            }
        }

        while (true) {
            const dayOfWeek = prevDate.getDay();
            const formattedDate = `${prevDate.getFullYear()}-${String(prevDate.getMonth() + 1).padStart(2, '0')}-${String(prevDate.getDate()).padStart(2, '0')}`;
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) break; // 영업일이면 종료
            prevDate.setDate(prevDate.getDate() - 1); // 하루 전으로 이동
        }

        return prevDate;
    }

    // 다음 영업일 계산 (주말 및 공휴일 제외)
    function getNextBusinessDay(date, businessDays = 0) {
        if (!(date instanceof Date)) date = new Date(date); // Date 객체로 변환
        let nextDate = new Date(date); // 원본 날짜 복사

        while (businessDays > 0) {
            nextDate.setDate(nextDate.getDate() + 1); // 하루 후로 이동
            const dayOfWeek = nextDate.getDay(); // 요일 확인
            const formattedDate = `${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}-${String(nextDate.getDate()).padStart(2, '0')}`;
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                businessDays--; // 영업일 감소
            }
        }

        while (true) {
            const dayOfWeek = nextDate.getDay();
            const formattedDate = `${nextDate.getFullYear()}-${String(nextDate.getMonth() + 1).padStart(2, '0')}-${String(nextDate.getDate()).padStart(2, '0')}`;
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) break; // 영업일이면 종료
            nextDate.setDate(nextDate.getDate() + 1); // 하루 후로 이동
        }

        return nextDate;
    }
    
    // 다음 배송일 계산 (간격 및 공휴일 고려)
    function getNextDeliveryDate(date, businessDays = 0, interval = '') {
        if (!(date instanceof Date)) date = new Date(date); // Date 객체로 변환
        const nextBusinessDay = getNextBusinessDay(date, businessDays); // 다음 영업일 계산
        const inputMonth = date.getMonth(); // 입력 월
        const nextMonth = nextBusinessDay.getMonth(); // 다음 영업일의 월

        if (interval === 'month' && nextMonth !== inputMonth) {
            return getBusinessDaysBefore(date, 0); // 다른 월이면 직전 영업일 반환
        }

        return nextBusinessDay; // 다음 영업일 반환
    }
    
    <?php if (get_subs_option('su_chk_user_delivery')) { // 사용자 직접 입력 배송주기 처리 ?>
    jQuery($ => {
        $("#od_subscription_select_data").on('input', function() {
            const $this = $(this);
            let value = parseInt($this.val()) || <?php echo get_subs_option('su_user_delivery_default_day'); ?>; // 입력값 검증
            if (value < 1 || value > 365) value = <?php echo get_subs_option('su_user_delivery_default_day'); ?>; // 범위 초과 시 기본값
            $this.val(value);
            calculate_next_delivery_date(); // 다음 배송일 계산
        });
    });
    <?php } else { // 라디오 버튼 선택 처리 ?>
        
        jQuery($ => {
            $("input[name='od_subscription_select_data']").on("click", calculate_next_delivery_date); // 배송주기 선택 시 계산
        });
        
    <?php } ?>
    
    <?php if (get_subs_option('su_hope_date_use')) { // 배송 희망일 사용 설정 ?>
        
        jQuery($ => {
            const $hopeDatePrint = $("#od_hope_date_print"); // 날짜 선택기 요소
            const $hopeDateInput = $("#od_hope_date"); // 숨겨진 날짜 입력 요소

            $hopeDatePrint.datepicker({
                defaultDate: "<?php echo G5_TIME_YMD; ?>", // 기본 날짜 설정
                dateFormat: "yy-mm-dd", // 날짜 형식
                inline: true, // 인라인 표시
                yearRange: "c-99:c+99", // 연도 범위
                beforeShowDay: function(date) {
                    const today = new Date();
                    const isToday = date.getFullYear() === today.getFullYear() && date.getMonth() === today.getMonth() && date.getDate() === today.getDate();
                    if (isToday) return [true, "today-highlight", "오늘"]; // 오늘 강조 표시
                    
                    const dayOfWeek = date.getDay(); // 요일 확인
                    const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                    if (dayOfWeek === 0 || dayOfWeek === 6 || holidays.includes(formattedDate)) {
                        return [false, 'ui-state-disabled', '공휴일 또는 주말입니다.']; // 주말 및 공휴일 비활성화
                    }
                    return [true, '', '']; // 활성화된 날짜
                },
                onSelect: function(dateText) {
                    $hopeDateInput.val(dateText); // 선택된 날짜 저장
                    updatePaymentDate(); // 결제일 갱신
                    calculate_next_delivery_date(); // 다음 배송일 계산
                },
                minDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after')); ?>"), // 최소 날짜
                maxDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after') + 30); ?>") // 최대 날짜
            });
            
            <?php if (isset($aparams_array['hope_delivery_date']) && $aparams_array['hope_delivery_date']) { ?>
                $hopeDatePrint.datepicker("setDate", "<?php echo $aparams_array['hope_delivery_date']; ?>"); // 저장된 희망 배송일 설정
            <?php } ?>
                
            // 결제일 갱신 (리드 타임 기반)
            function updatePaymentDate() {
                const leadDays = <?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?>; // 결제 리드 타임
                if (leadDays <= 0) return; // 리드 타임 없으면 종료

                setTimeout(() => {
                    const hopeDate = $hopeDatePrint.datepicker("getDate"); // 선택된 희망 배송일
                    if (!hopeDate) return; // 날짜 없으면 종료

                    const resultDate = getBusinessDaysBefore(hopeDate, leadDays); // 이전 영업일 계산
                    const daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
                    const formattedDate1 = `${resultDate.getMonth() + 1}월 ${resultDate.getDate()}일 (${daysOfWeek[resultDate.getDay()]})`; // 출력 형식
                    $(".set_pay_date").text(formattedDate1); // 결제일 표시
                    $(".before_pay_date_tr").show(); // 결제일 행 표시
                }, 100);
            }
            
            updatePaymentDate(); // 초기 결제일 설정
            
            // 정기구독 신청 버튼 클릭 처리
            $(".sit_btn_subscription").on("click", e => {
                e.preventDefault(); // 기본 동작 방지
                
                // 입력값 가져오기
                const deliveryCycle = $("#od_subscription_select_data").val() || $("input[name='od_subscription_select_data']:checked").val();
                const usageCount = $("#od_subscription_select_number").val() || $("input[name='od_subscription_select_number']:checked").val();
                const hopeDeliveryDate = $hopeDateInput.val();
                
                // 유효성 검사
                if (!deliveryCycle) return alert("배송주기를 선택해주세요.");
                if (!usageCount) return alert("이용횟수를 선택해주세요.");
                if (!hopeDeliveryDate) return alert("희망배송일을 입력해주세요.");
                
                // 숨겨진 폼에 값 설정
                $("#hidden_delivery_cycle").val(deliveryCycle);
                $("#hidden_usage_count").val(usageCount);
                $("#hidden_hope_delivery_date").val(hopeDeliveryDate);
                
                $("form[name='fitem']").submit(); // 폼 제출
            });
        });
        
    <?php } ?>
    
    // 다음 예상 배송일 계산
    function calculate_next_delivery_date() {
        const $hopeDateInput = $("#od_hope_date"); // 희망 배송일 입력 요소
        if (!$hopeDateInput.length) return; // 희망 배송일 없으면 종료
        
        if (!$hopeDateInput.val()) {
            $hopeDateInput.val($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker("getDate"))); // 기본값 설정
        }
        
        const deliveryCycle = $("#od_subscription_select_data").val() || $("input[name='od_subscription_select_data']:checked").val(); // 배송주기
        const usageCount = $("#od_subscription_select_number").val(); // 이용횟수
        const hopeDate = $hopeDateInput.val(); // 희망 배송일
        
        if (usageCount && parseInt(usageCount.split("||")[1]) < 2) return; // 이용횟수 1회면 종료
        
        const $nextEl = $('.next-delivery-date-el').length ? $('.next-delivery-date-el') : $('<div class="next-delivery-date-el"></div>').appendTo(".jquery-pg-datepicker"); // 다음 배송일 표시 요소
        
        let baseDate = new Date(hopeDate); // 기준 날짜 설정
        if (!deliveryCycle) return; // 배송주기 없으면 종료
        
        let select_interval = "";
        
        if (deliveryCycle.includes("||")) { // 복합 배송주기 처리
            let [no, plus, interval, etc_data] = deliveryCycle.split("||"); // 주기 분리
            interval = interval || "day"; // 기본값: 일
            plus = Math.abs(parseInt(plus, 10)) || 1; // 증가 값
            let isCheckBefore = false;
            
            select_interval = interval;
            
            switch (interval) {
                case "day":
                    baseDate.setDate(baseDate.getDate() + plus); // 일 단위 증가
                    break;
                case "week":
                    if (etc_data) { // 특정 요일 지정
                        const dayMap = { "sun": 0, "mon": 1, "tue": 2, "wed": 3, "thu": 4, "fri": 5, "sat": 6 }; // 요일 매핑
                        const targetDay = dayMap[etc_data] !== undefined ? dayMap[etc_data] : 1; // 기본값: 월요일
                        baseDate.setDate(baseDate.getDate() - baseDate.getDay()); // 주의 시작(일요일)으로 이동
                        baseDate.setDate(baseDate.getDate() + plus * 7); // 주 단위 증가
                        const daysToAdd = (targetDay - baseDate.getDay() + 7) % 7; // 목표 요일 조정
                        baseDate.setDate(baseDate.getDate() + daysToAdd);
                    } else {
                        baseDate.setDate(baseDate.getDate() + plus * 7); // 주 단위 증가
                    }
                    break;
                case "month":
                    baseDate.setMonth(baseDate.getMonth() + plus); // 월 단위 증가
                    if (etc_data && parseInt(etc_data) > 1) { // 특정 일자 지정
                        const targetDay = parseInt(etc_data); // 목표 일자
                        const lastDayOfMonth = new Date(baseDate.getFullYear(), baseDate.getMonth() + 1, 0).getDate(); // 월의 마지막 날
                        baseDate.setDate(Math.min(targetDay, lastDayOfMonth)); // 목표 일자 또는 마지막 날 설정
                    }
                    isCheckBefore = true; // 직전 영업일 확인
                    break;
                case "year":
                    baseDate.setFullYear(baseDate.getFullYear() + plus); // 연 단위 증가
                    isCheckBefore = true; // 직전 영업일 확인
                    break;
                default:
                    console.error(`알 수 없는 주기: ${interval}`); // 오류 로그
                    return;
            }
            
            const nextDeliveryDate = getNextDeliveryDate(baseDate, 0, select_interval); // 다음 배송일 계산
            $nextEl.html(`다음 예상 배송일: ${nextDeliveryDate.toISOString().slice(0, 10)}`); // 결과 표시
        } else {
            baseDate.setDate(baseDate.getDate() + parseInt(deliveryCycle)); // 단순 일수 추가
            const nextDeliveryDate = getNextDeliveryDate(baseDate); // 다음 배송일 계산
            $nextEl.html(`다음 예상 배송일: ${nextDeliveryDate.toISOString().slice(0, 10)}`); // 결과 표시
        }
    }
    
    calculate_next_delivery_date(); // 초기 배송일 계산
    
    // 폼 제출 처리
    $("form[name='fitem']").on("form:valid", function() {
        if (["구독장바구니", "정기구독신청", "정기구독"].includes(document.pressed)) { // 구독 관련 버튼 처리
            this.action = "<?php echo G5_SUBSCRIPTION_URL; ?>/cartupdate.php"; // 구독 전용 URL 설정
            $("input[name='is_subscription']").val('1'); // 구독 플래그 설정
            this.sw_direct.value = document.pressed === "구독장바구니" ? 0 : 1; // 직접 구매 여부
        } else {
            $("input[name='is_subscription']").val(''); // 구독 플래그 초기화
        }
    });
</script>