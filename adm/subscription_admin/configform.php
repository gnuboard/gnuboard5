<?php
$sub_menu = '600100';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "r");

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$sql = " select * from `{$g5['g5_subscription_config_table']}` limit 1";
$g5_subscriptions_options = $config['g5_subscriptions_options'] = sql_fetch($sql);

/*
if (! isset($g5_subscriptions_options['su_cron_execute_hour']) ) {
    sql_query(
        " ALTER TABLE `{$g5['g5_subscription_config_table']}`
                    ADD `su_cron_updatetime` datetime DEFAULT NULL,
                    ADD `su_cron_execute_hour` tinyint(2) NOT NULL DEFAULT '0'",
        true
    );
}
*/

/*
add_stylesheet('<link type="text/css" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/themes/base/jquery-ui.css" rel="stylesheet" />', 0);
add_stylesheet('<link type="text/css" href="'.G5_PLUGIN_URL.'/jquery-ui/style.css">', 0);

add_javascript('<script src="//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js"></script>', 1);
*/

add_javascript('<script src="//cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.js"></script>', 2);
add_stylesheet('<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css">', 2);

$subscription_info_inputs = get_subscription_info_inputs();

// print_r2($subscription_info_inputs);
// exit;
$g5['title'] = '정기결제설정';
include_once (G5_ADMIN_PATH.'/admin.head.php');
?>
<div>
<form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
<input type="hidden" name="token" value="">
<section>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>사업자정보 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label>정기결제 CRON PATH</label></th>
            <td>
                <p><?php echo G5_SUBSCRIPTION_URL.'/cron_script.php'; ?></p>
                <p><?php echo G5_SUBSCRIPTION_PATH.'/cron_script.php'; ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>크론 등록 과정</label></th>
            <td>
                <ul>
                
                    <li>
                    <a href="cron-job.org" target="_blank">cron-job.org</a> 과 같은 웹사이트에서 cron을 등록합니다.
                    </li>
                    <li>cron-job.org 사이트의 경우 로그인 후 CREATE CRONJOB 버튼을 눌러 URL 항목에<?php echo G5_SUBSCRIPTION_URL; ?>/cron_script.php 을 등록합니다.</li>
                    
                </ul>
            </td>
        </tr>
        <tr>
            <th scope="row"><label>마지막 크론실행시간</label></th>
            <td>
                <?php echo get_subs_option('su_cron_updatetime'); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="su_cron_execute_hour">매일 크론 실행 hour</label></th>
            <td>
                <?php echo help('매일 실행되는 크론 실행 시간을 지정합니다. 지정된 시간에만 정기결제를 실행합니다.'); ?>
                
                <select name="su_cron_execute_hour" id="su_cron_execute_hour">
                    <?php for($i=0;$i<24;++$i) { ?>
                    <option value="<?php echo $i; ?>" <?php echo get_selected(get_subs_option('su_cron_execute_hour'), $i) ?>><?php echo $i.' ~ '.$i + 1; ?> 시</option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">구독정보입력</th>
            <td>
                <div>
                출력형식 : 
<select name="it_subscription_number">
<option value="0">셀렉트박스</option>
<option value="1">버튼식</option>
</select>
<br>
                <input type="number" name="number" >배송되기 몇일 전에 자동결제 합니다.
                <br>
                <input type="checkbox" name="" >다음 결제일 변경 수정가능
                <br>
                <input type="checkbox" name="" >다음 결제일 변경 가능일
                <br>
                <input type="checkbox" name="" >휴일(토요일, 월요일) 에는 결제 안함 (휴일을 지난 다음날 평일에 결제합니다.)
                <br>
                </div>
                <div id="sit_option_addfrm_btn"><button type="button" id="add_supply_row" class="btn_frmline">옵션추가</button></div>
                <div id="sit_supply_frm">
                    <table>
                        <tr class="not-remove">
                            <th>삭제체크</th>
                            <th>결제주기 선택</th>
                            <th></th>
                            <th>출력텍스트</th>
                            <th>사용여부</th>
                        </tr>
                        <?php 
                        
/*
Array
(
    [0] => Array
        (
            [opt_id] => 1
            [opt_chk] => 
            [opt_input] => 1
            [opt_date_format] => day
            [opt_print] => 
            [opt_use] => 1
        )

    [1] => Array
        (
            [opt_id] => 2
            [opt_chk] => 
            [opt_input] => 1
            [opt_date_format] => day
            [opt_print] => 
            [opt_use] => 1
        )

    [2] => Array
        (
            [opt_id] => 3
            [opt_chk] => 
            [opt_input] => 1
            [opt_date_format] => day
            [opt_print] => 
            [opt_use] => 1
        )

    [3] => Array
        (
            [opt_id] => 4
            [opt_chk] => 
            [opt_input] => 1
            [opt_date_format] => day
            [opt_print] => 
            [opt_use] => 1
        )

    [4] => Array
        (
            [opt_id] => 5
            [opt_chk] => 
            [opt_input] => 1
            [opt_date_format] => day
            [opt_print] => 
            [opt_use] => 1
        )

)
*/

                        $i = 0;
                        if ($subscription_info_inputs) { ?>
                        <?php
                        foreach ($subscription_info_inputs as $opt) {
                        ?>
                        <tr class="trtr" data-jbox-content="">
                            <td>
                                <input type="hidden" name="opt_id[]" value="<?php echo $opt['opt_id']; ?>" >
                                <input type="checkbox" name="opt_chk[]" id="opt_chk_<?php echo $i; ?>" disabled>
                            </td>
                            <td>
                                <span class="default_format">
                                    <input type="number" name="opt_input[]" class="frm_input" value="<?php echo $opt['opt_input']; ?>">
                                </span>
                            </td>
                            <td>
                            <select class="subscription_date_format" name="opt_date_format[]">
                                <?php echo option_selected("day", $opt['opt_date_format'], "일"); ?>
                                <?php echo option_selected("week", $opt['opt_date_format'], "주"); ?>
                                <?php echo option_selected("month", $opt['opt_date_format'], "월"); ?>
                                <?php echo option_selected("year", $opt['opt_date_format'], "년"); ?>
                            </select>
                            </td>
                            <td>
                                <input type="text" class="frm_input subscription_print_format" name="opt_print[]" title="" value="<?php echo $opt['opt_print']; ?>">
                            </td>
                            <td>
                                <select name="opt_use[]" id="spl_use_<?php echo $i; ?>">
                                    <?php echo option_selected("1", $opt['opt_use'], "사용함"); ?>
                                    <?php echo option_selected("0", $opt['opt_use'], "사용안함"); ?>
                                </select>
                            </td>
                        </tr>
                        <?php $i++;
                        } // end foreach ?>
                        <?php } else { ?>
                        <tr class="trtr" data-jbox-content="">
                            <td>
                                <input type="hidden" name="opt_id[]" value="1" >
                                <input type="checkbox" name="opt_chk[]" id="opt_chk_<?php echo $i; ?>" disabled value="1">
                            </td>
                            <td>
                                <span class="default_format">
                                    <input type="number" name="opt_input[]" class="frm_input" value="1">
                                </span>
                            </td>
                            <td>
                            <select class="subscription_date_format" name="opt_date_format[]">
                                <option value="day">일</option>
                                <option value="week">주</option>
                                <option value="month">월</option>
                                <option value="year">년</option>
                            </select>
                            </td>
                            <td>
                                <input type="text" class="frm_input subscription_print_format" name="opt_print[]" title="">
                            </td>
                            <td>
                                <select name="opt_use[]" id="spl_use_<?php echo $i; ?>">
                                    <option value="1">사용함</option>
                                    <option value="0">사용안함</option>
                                </select>
                            </td>
                        </tr>
                        <?php } // end if ?>
                    </table>
                </div>
                <div class="btn_list01 btn_list">
                    <button type="button" id="sel_option_delete" class="btn btn_02">선택삭제</button>
                </div>
                <script>
                    
                    /*
                    $(".subscription_print_format").tooltip({
                        content: function() {
                            return $(this).val();
                        }
                    });
                    */
                    
                    function jBox_tooltip_attach($this) {
                        
                        new jBox('Tooltip', {
                            attach: $this,
                            target: $this.find(".subscription_print_format"),
                            theme: 'TooltipBorder',
                            trigger: 'changeinput',
                            adjustTracker: true,
                            closeOnClick: 'body',
                            closeButton: 'box',
                            animation: 'move',
                            autoClose: 3000,
                            position: {
                                x: 'left',
                                y: 'top'
                            },
                            outside: 'y',
                            pointer: 'left:20',
                            offset: {
                                x: 25
                            },
                            getContent: 'data-jbox-content',
                            onOpen: function () {
                                // this.source.addClass('active').html('Now scroll');
                            },
                            onClose: function () {
                                // this.source.removeClass('active').html('Click me');
                            }
                        });
                        
                    }
                    
                    $("#sit_supply_frm .trtr").each(function(index, item){
                        var $this = $(this);
                        
                        jBox_tooltip_attach($this);
                    });
  
                    $("#add_supply_row").click(function() {
                        var $el = $("#sit_supply_frm tr:last"),
                            newRow = $el.clone();
                        
                        var values = $("input[name='opt_id[]']").map(function() {
                            return parseInt($(this).val(), 10); // 값을 정수로 변환
                        }).get(); // jQuery 객체를 일반 배열로 변환

                        var maxValue = Math.max.apply(null, values);
                        
                        // 복사된 tr 내부의 값 초기화
                        newRow.removeClass("not-remove");
                        newRow.find('input[name="opt_id[]"]').val(maxValue + 1);
                        newRow.find('input[type="checkbox"]').prop('checked', false).removeAttr("disabled"); // 체크박스 해제
                        newRow.find('input[type="text"]').val(''); // 텍스트 초기화
                        // newRow.find('select[name="opt_date_format[]"]').val('day'); // 기본 선택값 설정 (필요 시 변경 가능)

                        $el.after(newRow);
                        jBox_tooltip_attach(newRow);
                        
                        // supply_sequence();
                    });
                    
                    // 선택삭제
                    $(document).on("click", "#sel_option_delete", function() {
                        var $el = $("input[name='opt_chk[]']:checked");
                        if($el.length < 1) {
                            alert("삭제하려는 옵션을 하나 이상 선택해 주십시오.");
                            return false;
                        }
                        
                        if ($el.closest("tr").hasClass("not-remove")) {
                        } else {
                            $el.closest("tr").remove();
                        }
                    });
                    
                    function get_yoil(str) {
                        var arr_yoil = {"mon": "월요일", "tue": "화요일", "wed": "수요일", "thu": "목요일", "fri": "금요일", "sat": "토요일", "sun": "일요일"};

                        return arr_yoil[str];
                    }
                    
                    function subcription_change_event($selector, is_change_format=0) {
                        const $row = $selector.closest("tr");
                        const $defaultFormatInput = $row.find(".default_format");
                        const $opt_print = $row.find('[name="opt_print[]"]');
                        const $opt_input = $row.find('[name="opt_input[]"]');
                        const $selectTag = $row.find('[name="opt_date_format[]"]');
                        const selectedValue = $selectTag.val();
                        
                        console.log( $row );
                        
                        var day_content = "매일 정기결제합니다.",
                            week_content = "매주 결제한 요일에 정기결제합니다.",
                            month_content = "매월 결제한 일에 정기결제합니다.",
                            year_content = "매년 결제한 일에 정기결제합니다.";
                        
                        var $opt_print_val = $opt_input.val();
                        
                        if ($opt_print_val && is_change_format === 2) {
                            day_content = "매 "+ $opt_print_val +"일 마다 정기결제합니다.";
                            week_content = "매주 "+ get_yoil($opt_print_val) +" 마다 정기결제합니다.";
                            month_content = "매월 "+ $opt_print_val +"일에 정기결제합니다.";
                        }
                        
                        // 데이터와 템플릿 정의
                        const templates = {
                            day: {
                                input: '<input type="number" name="opt_input[]" class="frm_input" value="1">',
                                content: day_content
                            },
                            week: {
                                input: `
                                    <select name="opt_input[]">
                                        <option value="">선택안함</option>
                                        <option value="mon">월요일</option>
                                        <option value="tue">화요일</option>
                                        <option value="wed">수요일</option>
                                        <option value="thu">목요일</option>
                                        <option value="fri">금요일</option>
                                        <option value="sat">토요일</option>
                                        <option value="sun">일요일</option>
                                    </select>`,
                                content: week_content
                            },
                            month: {
                                input: '<input type="number" name="opt_input[]" class="frm_input" min="0" max="31" value="0">',
                                content: month_content
                            },
                            year: {
                                input: '<input type="number" name="opt_input[]" class="frm_input" value="1" disabled>',
                                content: year_content
                            }
                        };
                        
                        // 선택 값에 따른 동작
                        const template = templates[selectedValue];
                        
                        if (template) {
                            if (is_change_format !== 2) {
                                $defaultFormatInput.html(template.input);
                            }
                            $row.attr("data-jbox-content", template.content);
                            $row.trigger("changeinput");
                        }
                    }
                    
                    $(document).on("change", '[name="opt_input[]"]', function (e) {
                        
                        console.log('is_close', $(this).val());
                        subcription_change_event($(this), 2);
                        
                    });
                    
                    $(document).on("change", 'input[name="opt_print[]"]', function (e) {
                        var $this = $(this);
                        
                        subcription_change_event($(this), 2);
                        
                    });
                    
                    $(document).on("change", "select[name='opt_date_format[]']", function (e) {
                        
                        subcription_change_event($(this), 1);
                            
                    });
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="su_pg_service">결제대행사</label></th>
            <td>
                <input type="hidden" name="su_pg_service" id="su_pg_service" value="<?php echo get_subs_option('su_pg_service'); ?>" >
                <?php echo help('정기결제에서 사용할 결제대행사를 선택합니다.'); ?>
                <ul class="de_pg_tab">
                    <li class="<?php if(get_subs_option('su_pg_service') == 'kcp') echo 'tab-current'; ?>"><a href="#kcp_info_anchor" data-value="kcp" title="NHN KCP 선택하기" >NHN KCP</a></li>
                    <li class="<?php if(get_subs_option('su_pg_service') == 'inicis') echo 'tab-current'; ?>"><a href="#inicis_info_anchor" data-value="inicis" title="KG이니시스 선택하기">KG이니시스</a></li>
                    <li class="<?php if(get_subs_option('su_pg_service') == 'nicepay') echo 'tab-current'; ?>"><a href="#nicepay_info_anchor" data-value="nicepay" title="NICEPAY 선택하기">NICEPAY</a></li>
                </ul>
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld" id="kcp_info_anchor">
            <th scope="row">
                <label for="su_kcp_mid">KCP SITE CODE</label><br>
                <a href="http://sir.kr/main/service/p_pg.php" target="_blank" id="scf_kcpreg" class="kcp_btn">NHN KCP 신청하기</a>
            </th>
            <td>
                <?php echo help("NHN KCP 에서 받은 SR 로 시작하는 영대문자, 숫자 혼용 총 5자리 중 SR 을 제외한 나머지 3자리 SITE CODE 를 입력하세요.\n만약, 사이트코드가 SR로 시작하지 않는다면 NHN KCP에 사이트코드 변경 요청을 하십시오. 예) SR9A3"); ?>
                <span class="sitecode">SR</span> <input type="text" name="su_kcp_mid" value="<?php echo get_sanitize_input(get_subs_option('su_kcp_mid')); ?>" id="su_kcp_mid" class="frm_input code_input" size="2" maxlength="3"> 영대문자, 숫자 혼용 3자리
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="su_kcp_group_id">NHN KCP 그룹아이디</label></th>
            <td>
                <input type="text" name="su_kcp_group_id" value="<?php echo get_sanitize_input(get_subs_option('su_kcp_group_id')); ?>" id="su_kcp_group_id" class="frm_input" size="36" maxlength="25">
            </td>
        </tr>
        <tr class="pg_info_fld kcp_info_fld">
            <th scope="row"><label for="su_kcp_cert_info">NHN KCP 인증서 정보</label></th>
            <td>
                <?php echo help("kcp_cert_info는 결제 승인, 거래취소, 거래등록 시에 필요합니다.\n추가적으로 NHNKCP 상점 관리자 > 기술관리센터 > 인증센터 > KCP PG-API > 발급하기 경로에서 개인키 + 인증서 발급이 가능합니다."); ?>
                <textarea id="su_kcp_cert_info" name="su_kcp_cert_info" rows="7"><?php echo html_purifier(get_subs_option('su_kcp_cert_info')); ?></textarea>
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld" id="inicis_info_anchor">
            <th scope="row">
                <label for="su_inicis_mid">KG이니시스 상점아이디</label><br>
                <a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_kgreg" class="kg_btn">KG이니시스 신청하기</a>
            </th>
            <td>
                <?php echo help("KG이니시스로 부터 발급 받으신 상점아이디(MID) 10자리 중 SIR 을 제외한 나머지 7자리를 입력 합니다.\n만약, 상점아이디가 SIR로 시작하지 않는다면 계약담당자에게 변경 요청을 해주시기 바랍니다. (Tel. 02-3430-5858) 예) SIRpaytest"); ?>
                <span class="sitecode">SIR</span> <input type="text" name="su_inicis_mid" value="<?php echo get_subs_option('su_inicis_mid'); ?>" id="su_inicis_mid" class="frm_input code_input" size="10" maxlength="10"> 영문소문자(숫자포함 가능)
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="su_inicis_sign_key">KG이니시스 웹결제 사인키</label></th>
            <td>
                <?php echo help("KG이니시스에서 발급받은 웹결제 사인키를 입력합니다.\n<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보의 웹결제 signkey생성 조회 버튼 클릭, 팝업창에서 생성 버튼 클릭 후 해당 값을 입력합니다."); ?>
                <input type="text" name="su_inicis_sign_key" value="<?php echo get_sanitize_input(get_subs_option('su_inicis_sign_key')); ?>" id="su_inicis_sign_key" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="su_inicis_iniapi_key">KG이니시스 INIAPI KEY</label></th>
            <td>
                <?php echo help("<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보 > INIAPI key 생성 조회 하여 KEY를 여기에 입력합니다."); ?>
                <input type="text" name="su_inicis_iniapi_key" value="<?php echo get_sanitize_input(get_subs_option('su_inicis_iniapi_key')); ?>" id="su_inicis_iniapi_key" class="frm_input" size="30" maxlength="30">
            </td>
        </tr>
        <tr class="pg_info_fld inicis_info_fld">
            <th scope="row"><label for="su_inicis_iniapi_iv">KG이니시스 INIAPI IV</label></th>
            <td>
                <?php echo help("<a href='https://iniweb.inicis.com/' target='_blank'>KG이니시스 가맹점관리자</a> > 상점정보 > 계약정보 > 부가정보 > INIAPI IV 생성 조회 하여 KEY를 여기에 입력합니다."); ?>
                <input type="text" name="su_inicis_iniapi_iv" value="<?php echo get_sanitize_input(get_subs_option('su_inicis_iniapi_iv')); ?>" id="su_inicis_iniapi_iv" class="frm_input" size="30" maxlength="30">
            </td>
        </tr>
        <tr class="pg_info_fld nicepay_info_fld" id="nicepay_info_anchor">
            <th scope="row"><label for="su_nice_clientid">나이스페이 clientId</label>
            <br>
            <a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_nicepay_reg" class="nicepay_btn">NICEPAY 신청하기</a>
            </th>
            <td>
                <input type="text" name="su_nice_clientid" value="<?php echo get_sanitize_input(get_subs_option('su_nice_clientid')); ?>" id="su_nice_clientid" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr class="pg_info_fld nicepay_info_fld">
            <th scope="row"><label for="su_nice_secretkey">나이스페이 secretKey</label></th>
            <td>
                <?php echo help("나이스페이 clientId 또는 secretKey 값이 틀리거나 테스트 계정으로 실결제를 진행할 경우 사용자 정보가 존재하지 않습니다 라는 메시지가 발생됩니다."); ?>
                <input type="text" name="su_nice_secretkey" value="<?php echo get_sanitize_input(get_subs_option('su_nice_secretkey')); ?>" id="su_nice_secretkey" class="frm_input" size="40" maxlength="50">
            </td>
        </tr>
        <tr>
            <th scope="row">정기결제 테스트</th>
            <td>
                <?php echo help("PG사의 정기결제 테스트를 하실 경우에 체크하세요. 결제단위 최소 1,000원"); ?>
                <input type="radio" name="su_card_test" value="0" <?php echo (get_subs_option('su_card_test') == 0) ? "checked" : ""; ?> id="su_card_test1">
                <label for="su_card_test1">실결제 </label>
                <input type="radio" name="su_card_test" value="1" <?php echo (get_subs_option('su_card_test') == 1) ? "checked" : ""; ?> id="su_card_test2">
                <label for="su_card_test2">테스트결제</label>
                <div class="scf_cardtest kcp_cardtest">
                    <a href="http://admin.kcp.co.kr/" target="_blank" class="btn_frmline" title="NHN_KCP 상점관리자">실결제 관리자</a>
                    <a href="http://testadmin8.kcp.co.kr/" target="_blank" class="btn_frmline" title="NHN_KCP 테스트 관리자">테스트 관리자</a>
                </div>
                <div class="scf_cardtest inicis_cardtest">
                    <a href="https://iniweb.inicis.com/" target="_blank" class="btn_frmline" title="KG이니시스 상점관리자">상점 관리자</a>
                </div>
                 <div class="scf_cardtest nicepay_cardtest">
                    <a href="https://start.nicepay.co.kr/merchant/login/main.do" target="_blank" class="btn_frmline" title="나이스페이 상점관리자">상점 관리자</a>
                </div>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
</div>

</form>
</div>
<script>
jQuery(function($) {
    
    $(document).on("click", ".de_pg_tab a", function(e){

        var pg = $(this).attr("data-value"),
            class_name = "tab-current";
        
        $("#su_pg_service").val(pg);
        $(this).parent("li").addClass(class_name).siblings().removeClass(class_name);

        $(".pg_vbank_url:visible").hide();
        $("#"+pg+"_vbank_url").show();
        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
        
    });
    
    <?php if (get_subs_option('su_pg_service')) { ?>
    $("#<?php echo get_subs_option('su_pg_service'); ?>_vbank_url").show();
    <?php } else { ?>
    $(".kcp_info_fld").show();
    $("#kcp_vbank_url").show();
    <?php } ?>
        
    $("#su_pg_service").on("change", function() {
        var pg = $(this).val();
        $(".pg_info_fld:visible").hide();
        $(".pg_vbank_url:visible").hide();
        $("."+pg+"_info_fld").show();
        $("#"+pg+"_vbank_url").show();
        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $("."+pg+"_cardtest").removeClass("scf_cardtest_hide");
        $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
        $("#"+pg+"_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
    });
    
    $(".scf_cardtest").addClass("scf_cardtest_hide");
    $(".<?php echo get_subs_option('su_pg_service'); ?>_cardtest").removeClass("scf_cardtest_hide");
    $("#<?php echo get_subs_option('su_pg_service'); ?>_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
});
</script>
<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');