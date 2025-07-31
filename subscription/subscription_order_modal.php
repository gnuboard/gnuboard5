<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 차단

if (defined('IS_SUBSCRIPTION_ORDER_FORM') && IS_SUBSCRIPTION_ORDER_FORM) {
    $aparams_array = (isset($_REQUEST['aparams']) && isValidBase64($_REQUEST['aparams'])) ? unserialize(base64_decode($_REQUEST['aparams'])) : array('hope_delivery_date'=>'', 'delivery_cycle'=>'');
    
    $business_next_day = getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after'));
    
    if ($aparams_array['hope_delivery_date'] && isValidDate($aparams_array['hope_delivery_date'])) {
        
        if (strtotime($aparams_array['hope_delivery_date']) < strtotime($business_next_day)) {
            $aparams_array['hope_delivery_date'] = $business_next_day;
        }
        
    } else {
        $aparams_array['hope_delivery_date'] = $business_next_day;
    }
    
} else {
    $aparams_array = array('hope_delivery_date' => getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after')), 'delivery_cycle'=>'', 'usage_count'=>0);
}
// 정기구독 설정 불러오기
$subscription_info_inputs = get_subscription_info_inputs(); // 배송주기 옵션
$subscription_use_inputs = get_subscription_use_inputs();   // 이용횟수 옵션
?>

<div class="<?php echo (defined('IS_SUBSCRIPTION_ORDER_FORM') && IS_SUBSCRIPTION_ORDER_FORM) ? 'subscription-form order-table' : ''; ?>">
    <div class="row">
        <?php if (defined('G5_IS_SUBSCRIPTION_ITEM') && G5_IS_SUBSCRIPTION_ITEM) { ?>
            <h2 class="subscription-title">정기구독 배송일 선택</h2>
            <?php if ($first_content = get_subs_option('su_subscription_content_first')) { // 첫 번째 안내문 가져오기 ?>
                <div class="subscription-desc1"><?php echo conv_content($first_content, 1); // 첫 번째 안내문 출력 ?></div>
            <?php } ?>
        <?php } ?>
        
        <div class="cell header"><label><?php echo subscription_item_delivery_title(); // 배송주기 제목 출력 ?></label></div>
        <div class="cell">
            <?php if (get_subs_option('su_chk_user_delivery')) { // 사용자 직접 입력 배송주기 허용 ?>
                <input id="od_subscription_select_data" name="od_subscription_select_data" type="number" inputmode="numeric" 
                       placeholder="숫자" max="365" maxlength="3" 
                       value="<?php echo (isset($aparams_array['delivery_cycle']) && $aparams_array['delivery_cycle']) ? (int)$aparams_array['delivery_cycle'] : get_subs_option('su_user_delivery_default_day'); ?>" 
                       class="frm_input">
                <span class="od_subscription_days">일</span>
            <?php } else { ?>
                <?php if (get_subs_option('su_output_display_type')) { // 버튼 형식 출력 ?>
                    <div class="su-display-btns">
                        <?php foreach ($subscription_info_inputs as $key => $opt) {
                            if (!$opt['opt_use']) continue; // 비활성화 옵션 제외
                            $opt_print = subscription_formatted_option($opt); // 옵션 포맷팅
                            $checked = (isset($aparams_array['delivery_cycle']) && $aparams_array['delivery_cycle'] === "$key||{$opt['opt_input']}||{$opt['opt_date_format']}||{$opt['opt_etc']}") ? 'checked' : '';
                        ?>
                            <input type="radio" id="od_subscription_select_data_<?php echo $key; ?>" class="sound_only" 
                                   name="od_subscription_select_data" <?php echo $checked; ?> 
                                   value="<?php echo get_text("$key||{$opt['opt_input']}||{$opt['opt_date_format']}||{$opt['opt_etc']}"); ?>">
                            <label for="od_subscription_select_data_<?php echo $key; ?>" class="select-icon"><span><?php echo $opt_print; ?></span></label>
                        <?php } ?>
                    </div>
                <?php } else { // 셀렉트 박스 형식 출력 ?>
                    <select id="od_subscription_select_data" class="frm_input" name="od_subscription_select_data">
                        <option value="" disabled <?php echo $aparams_array['delivery_cycle'] ? '' : 'selected'; ?>>선택해주세요</option>
                        <?php foreach ($subscription_info_inputs as $key => $opt) {
                            if (!$opt['opt_use']) continue; // 비활성화 옵션 제외
                            $opt_print = subscription_formatted_option($opt); // 옵션 포맷팅
                            $this_value = get_text("$key||{$opt['opt_input']}||{$opt['opt_date_format']}||{$opt['opt_etc']}");
                            echo option_selected($this_value, $aparams_array['delivery_cycle'], $opt_print);
                        } ?>
                    </select>
                <?php } ?>
            <?php } ?>
        </div>
    </div>

    <div class="row">
        <div class="cell header"><label><?php echo subscription_item_select_title(); ?></label></div>
        <div class="cell">
            <?php if (get_subs_option('su_output_display_type')) { // 버튼 형식 출력 ?>
                <div class="su-display-btns">
                    <?php foreach ($subscription_use_inputs as $key => $use) {
                        if (!$use['num_use']) continue; // 비활성화 옵션 제외
                        $use_print = $use['use_print'] ? $use['use_print'] : $use['use_input'] . ' 회'; // 기본 출력 설정
                        $use_print = str_replace("{입력}", $use['use_input'], $use_print); // 입력값 치환
                        $checked = (isset($aparams_array['usage_count']) && $aparams_array['usage_count'] === "$key||{$use['use_input']}") ? 'checked' : '';
                    ?>
                        <input type="radio" id="od_subscription_select_number_<?php echo $key; ?>" class="sound_only" 
                               name="od_subscription_select_number" <?php echo $checked; ?> 
                               value="<?php echo get_text("$key||{$use['use_input']}"); ?>">
                        <label for="od_subscription_select_number_<?php echo $key; ?>" class="select-icon"><span><?php echo $use_print; ?></span></label>
                    <?php } ?>
                </div>
            <?php } else { // 셀렉트 박스 형식 출력 ?>
                <select id="od_subscription_select_number" class="frm_input" name="od_subscription_select_number">
                    <option value="" disabled <?php echo $aparams_array['delivery_cycle'] ? '' : 'selected'; ?>>선택해주세요</option>
                    <?php foreach ($subscription_use_inputs as $key => $use) {
                        if (!$use['num_use']) continue; // 비활성화 옵션 제외
                        $use_print = $use['use_print'] ? $use['use_print'] : $use['use_input'] . ' 회'; // 기본 출력 설정
                        $use_print = str_replace("{입력}", $use['use_input'], $use_print); // 입력값 치환
                        $this_value = get_text("$key||{$use['use_input']}");
                        echo option_selected($this_value, $aparams_array['usage_count'], $use_print);
                    } ?>
                </select>
            <?php } ?>
        </div>
    </div>
    
    <?php if (get_subs_option('su_hope_date_use')) { // 배송 희망일 사용 설정 ?>
        <div class="row">
            <div class="cell header"><label for="od_hope_date_print">첫 희망배송일</label></div>
            <div class="cell jquery-pg-datepicker">
                <input type="hidden" name="od_hope_date" value="<?php echo isset($aparams_array['hope_delivery_date']) ? get_text($aparams_array['hope_delivery_date']) : ''; ?>" 
                       id="od_hope_date" class="frm_input" maxlength="10">
                <div id="od_hope_date_print" class="jquery-datepicker"></div>
                <div class="set_pay_date"></div>
            </div>
        </div>
    <?php } ?>
    
    <?php if (defined('G5_IS_SUBSCRIPTION_ITEM') && G5_IS_SUBSCRIPTION_ITEM) { ?>
        <?php if ($end_content = get_subs_option('su_subscription_content_end')) { // 마지막 안내문 가져오기 ?>
            <div class="subscription-desc-end"><?php echo conv_content($end_content, 1); // 마지막 안내문 출력 ?></div>
        <?php } ?>
        <div class="form-box-btns">
            <button type="submit" onclick="document.pressed=this.value;" value="정기구독신청" class="sit_btn_subscription sit_btn_buy">정기구독 신청하기</button>
        </div>
    <?php } ?>
</div>

<?php
$php_holidays = get_subscription_business_days();
$php_exception_dates = get_subscription_exception_dates();
?>

<script>
// 한국 공휴일 배열 (yyyy-mm-dd 형식)
var holidays = [];
var exception_dates = [];

<?php if ($php_holidays) { ?>
// 공휴일 날짜
holidays = <?php echo json_encode($php_holidays); ?>;
<?php } ?>
<?php if ($php_exception_dates) { ?>
// 영업일 지정 날짜
exception_dates = <?php echo json_encode($php_exception_dates); ?>;
<?php } ?>

// 지정된 일수 후의 날짜 계산
function getDateAfterDays(days) {
    var date = new Date();
    date.setDate(date.getDate() + days);
    return date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
}

// 영업일인지 확인
function isBusinessDay(date) {
    var dayOfWeek = date.getDay();
    var formattedDate = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    
    // 예외 날짜는 무조건 영업일로 처리
    if (exception_dates.indexOf(formattedDate) !== -1) {
        return true;
    }
    
    return dayOfWeek != 0 && dayOfWeek != 6 && holidays.indexOf(formattedDate) == -1;
}

// 결제일 계산
function getPayDaysBefore(date, daysToSubtract) {
    date = new Date(date);
    
    date.setDate(date.getDate() - daysToSubtract);
    
    return date;
}

// 이전 영업일 계산 (주말 및 공휴일 제외)
function getBusinessDaysBefore(date, businessDays) {
    date = new Date(date);
    while (businessDays > 0) {
        date.setDate(date.getDate() - 1);
        if (isBusinessDay(date)) businessDays--;
    }
    while (!isBusinessDay(date)) date.setDate(date.getDate() - 1);
    return date;
}

// 다음 영업일 계산 (주말 및 공휴일 제외)
function getNextBusinessDay(date, businessDays) {
    date = new Date(date);
    while (businessDays > 0) {
        date.setDate(date.getDate() + 1);
        if (isBusinessDay(date)) businessDays--;
    }
    while (!isBusinessDay(date)) date.setDate(date.getDate() + 1);
    return date;
}

// 다음 배송일 계산 (간격 및 공휴일 고려)
function getNextDeliveryDate(date, businessDays, interval) {
    date = new Date(date);
    var nextBusinessDay = getNextBusinessDay(date, businessDays);
    if (interval == 'month' && nextBusinessDay.getMonth() != date.getMonth()) {
        return getBusinessDaysBefore(date, 0);
    }
    return nextBusinessDay;
}

// jQuery 코드
jQuery(function($) {
    var $hopeDateInput = $("#od_hope_date"); // 희망 배송일 입력 요소
    var $hopeDatePrint = $("#od_hope_date_print"); // 날짜 선택기 요소
    var $deliveryCycleInput = $("#od_subscription_select_data"); // 배송주기 입력 요소
    var $usageCountInput = $("#od_subscription_select_number"); // 이용횟수 입력 요소
    var $form = $("form[name='fitem']"); // 폼 요소

    // 배송주기 입력 처리
    <?php if (get_subs_option('su_chk_user_delivery')) { // 사용자 직접 입력 배송주기 처리 ?>
        $deliveryCycleInput.on('input', function() {
            var value = this.value;

            // 빈 값일 경우에는 그대로 두고 함수 실행만 안함
            if (value === '') {
                return;
            }

            // 숫자로 변환
            var intValue = parseInt(value);

            // 숫자가 아닐 경우 처리하지 않음
            if (isNaN(intValue)) {
                return;
            }

            // 값 범위 제한
            this.value = Math.max(1, Math.min(365, intValue));
            
            calculateFirstDeliveryDate(); // 1회차 배송일 계산
        });
        
        // 입력이 끝났을 때 보정 (blur 시)
        $deliveryCycleInput.on('blur', function () {
            var value = parseInt(this.value);

            // 빈 값이면
            if (isNaN(value)) {
                this.value = <?php echo get_subs_option('su_user_delivery_default_day'); ?>;
            } else {
                // 범위 보정
                this.value = Math.max(1, Math.min(365, value));
            }

            calculateFirstDeliveryDate();
        });

    <?php } else { // 라디오 버튼 선택 처리 ?>
        $("input[name='od_subscription_select_data']").on("click", calculateFirstDeliveryDate); // 배송주기 선택 시 계산
        
        // 셀렉트 버튼 선택 처리
        $("#od_subscription_select_data, #od_subscription_select_number").on("change", calculateFirstDeliveryDate);
    <?php } ?>

    // 배송 희망일 날짜 선택기
    <?php if (get_subs_option('su_hope_date_use')) { // 배송 희망일 사용 설정 ?>
        $hopeDatePrint.datepicker({
            defaultDate: "<?php echo G5_TIME_YMD; ?>", // 기본 날짜 설정
            dateFormat: "yy-mm-dd", // 날짜 형식
            inline: true, // 인라인 표시
            yearRange: "c-99:c+99", // 연도 범위
            beforeShowDay: function(date) {
                var today = new Date();
                var isToday = date.getFullYear() == today.getFullYear() && date.getMonth() == today.getMonth() && date.getDate() == today.getDate();
                if (isToday) return [true, "today-highlight", "오늘"]; // 오늘 강조 표시
                return [isBusinessDay(date), '', '']; // 영업일 여부 반환
            },
            onSelect: function(dateText) {
                $hopeDateInput.val(dateText); // 선택된 날짜 저장
                updatePaymentDate(); // 결제일 갱신
                calculateFirstDeliveryDate(); // 1회차 배송일 계산
            },
            minDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after')); ?>"), // 최소 날짜
            maxDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after') + 30); ?>") // 최대 날짜
        });

        <?php if (isset($aparams_array['hope_delivery_date']) && $aparams_array['hope_delivery_date']) { ?>
            $hopeDatePrint.datepicker("setDate", "<?php echo $aparams_array['hope_delivery_date']; ?>"); // 저장된 희망 배송일 설정
        <?php } ?>

        // 결제일 갱신 (리드 타임 기반)
        function updatePaymentDate() {
            var leadDays = <?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?>; // 결제 리드 타임
            if (leadDays <= 0) return; // 리드 타임 없으면 종료

            var hopeDate = $hopeDatePrint.datepicker("getDate"); // 선택된 희망 배송일
            if (!hopeDate) return; // 날짜 없으면 종료
            
            var resultDate = getPayDaysBefore(hopeDate, leadDays); // 결제일 계산
            var today = new Date();
            today.setHours(0, 0, 0, 0); // 시간을 00:00:00 으로 맞춤
            resultDate.setHours(0, 0, 0, 0); // 비교를 위해 동일하게 설정

            if (resultDate < today) {
                resultDate = new Date(); // 오늘 날짜로 재설정
            }
    
            var daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
            // 날짜를 YYYY-MM-DD 형식으로 포맷
            var yyyy = resultDate.getFullYear();
            var mm = resultDate.getMonth() + 1;
            var dd = resultDate.getDate();
            var dayName = daysOfWeek[resultDate.getDay()];

            // 두 자리수 처리 (padStart 없이)
            if (mm < 10) mm = '0' + mm;
            if (dd < 10) dd = '0' + dd;

            var formattedDate = yyyy + '-' + mm + '-' + dd + ' (' + dayName + ')';

            $(".set_pay_date").text('1회차 결제일: ' + formattedDate).show();
        }

        updatePaymentDate(); // 초기 결제일 설정
    <?php } ?>

    // 정기구독 신청 버튼 클릭 처리
    $(".sit_btn_subscription").on("click", function(e) {
        e.preventDefault(); // 기본 동작 방지
        var deliveryCycle = $deliveryCycleInput.val() || $("input[name='od_subscription_select_data']:checked").val(); // 배송주기
        var usageCount = $usageCountInput.val() || $("input[name='od_subscription_select_number']:checked").val(); // 이용횟수
        var hopeDeliveryDate = $hopeDateInput.val(); // 희망 배송일

        if (!deliveryCycle) return alert("<?php echo subscription_item_delivery_title(); ?>를 선택해주세요."); // 배송주기 유효성 검사
        if (!usageCount) return alert("<?php echo subscription_item_select_title(); ?>를 선택해주세요."); // 이용횟수 유효성 검사

        $("#hidden_delivery_cycle").val(deliveryCycle); // 숨겨진 필드에 배송주기 설정
        $("#hidden_usage_count").val(usageCount); // 숨겨진 필드에 이용횟수 설정
        
        <?php if (get_subs_option('su_hope_date_use')) { // 정기구독 배송 희망일 사용한다면 ?>
        var hopeDeliveryDate = $hopeDateInput.val(); // 희망 배송일
        if (!hopeDeliveryDate) return alert("희망배송일을 입력해주세요."); // 희망 배송일 유효성 검사
        $("#hidden_hope_delivery_date").val(hopeDeliveryDate); // 숨겨진 필드에 희망 배송일 설정
        <?php } ?>
            
        $form.submit(); // 폼 제출
    });

    function formatKSTDate(date) {
        const year = date.getFullYear();
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const day = date.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
    }
    
    // 1회차 배송일 계산
    function calculateFirstDeliveryDate() {
        var $nextEl = $('.next-delivery-date-el').length ? $('.next-delivery-date-el') : $('<div class="next-delivery-date-el"></div>').appendTo(".jquery-pg-datepicker"); // 다음 배송일 표시 요소
        
        if (!$hopeDateInput.length || !$hopeDateInput.val()) {
            $nextEl.html('');
            return; // 희망 배송일 없으면 종료
        }
        
        var deliveryCycle = $deliveryCycleInput.val() || $("input[name='od_subscription_select_data']:checked").val(); // 배송주기
        var usageCount = $usageCountInput.val() || $("input[name='od_subscription_select_number']:checked").val(); // 이용횟수
        
        if (usageCount && parseInt(usageCount.split("||")[1]) < 1) {
            $nextEl.html('');
            return; // 이용횟수 1회면 종료
        }
        
        var baseDate = new Date($hopeDateInput.val()); // 기준 날짜 설정

        if (!deliveryCycle) {
            $nextEl.html('');
            return; // 배송주기 없으면 종료
        }
        
        var leadDays = <?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?> || 0; // 결제 리드 타임
        
        if (leadDays) {
            var today = new Date();
            var later_day = new Date();
            later_day.setDate(today.getDate() + leadDays);
            
            if (baseDate < later_day) {
                baseDate = later_day;
            }
        }
        
        if (deliveryCycle.indexOf("||") != -1) { // 복합 배송주기 처리
            var cycleParts = deliveryCycle.split("||"); // 주기 분리
            var no = cycleParts[0], plus = parseInt(cycleParts[1]) || 1, interval = cycleParts[2] || "day", etc_data = cycleParts[3]; // 값 추출

            var nextDeliveryDate = getNextDeliveryDate(baseDate, 0, interval); // 다음 배송일 계산
            $nextEl.html('1회차 예상 배송일: ' + formatKSTDate(nextDeliveryDate)); // 결과 표시
        } else {
            baseDate.setDate(baseDate.getDate() + parseInt(deliveryCycle)); // 단순 일수 추가
            var nextDeliveryDate = getNextDeliveryDate(baseDate); // 다음 배송일 계산
            $nextEl.html('1회차 예상 배송일: ' + formatKSTDate(nextDeliveryDate)); // 결과 표시
        }
        
        
    }

    calculateFirstDeliveryDate(); // 초기 배송일 계산

    // 폼 제출 처리
    $form.bind("form:valid", function() {
        if (["구독장바구니", "정기구독신청", "정기구독"].indexOf(document.pressed) != -1) { // 구독 관련 버튼 처리
            this.action = "<?php echo G5_SUBSCRIPTION_URL; ?>/cartupdate.php"; // 구독 전용 URL 설정
            $("input[name='is_subscription']").val('1'); // 구독 플래그 설정
            this.sw_direct.value = document.pressed == "구독장바구니" ? 0 : 1; // 직접 구매 여부
        } else {
            $("input[name='is_subscription']").val(''); // 구독 플래그 초기화
        }
    });
});
</script>