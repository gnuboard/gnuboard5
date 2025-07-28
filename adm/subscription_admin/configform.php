<?php
$sub_menu = '600100';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "r");

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$sql = " select * from `{$g5['g5_subscription_config_table']}` limit 1";
$config['g5_subscriptions_options'] = sql_fetch($sql);
$g5_subscriptions_options = $config['g5_subscriptions_options'];

if (isset($g5_subscriptions_options['api_holiday_data_go_key']) && $g5_subscriptions_options['api_holiday_data_go_key']) {
    subscription_setting_holidays();
}

add_javascript('<script src="//cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.js"></script>', 2);
add_stylesheet('<link rel="stylesheet" href="//cdn.jsdelivr.net/gh/StephanWagner/jBox@v1.3.2/dist/jBox.all.min.css">', 2);

$subscription_info_inputs = get_subscription_info_inputs();

$subscription_use_inputs = get_subscription_use_inputs();

$cron_token = subscription_cron_token();

$g5['title'] = '정기결제설정';
include_once(G5_ADMIN_PATH . '/admin.head.php');
?>
<div>
    <form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
        <input type="hidden" name="token" value="">
        <section class="subscription_config">
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
                                <?php echo help('CRON 경로 (둘 중에 하나만 등록해 주세요)'); ?>
                                <strong>웹에서 등록주소</strong>
                                <div>
                                    <div class="copy-box">
                                        <div class="copy-text" id="text1"><?php echo G5_SUBSCRIPTION_URL . '/cron_script.php?t=' . $cron_token; ?></div>
                                        <button class="copy-btn" data-target="#text1">복사</button>
                                    </div>
                                </div>
                                <strong>서버에서 직접 등록주소 (Linux Crontab)</strong>
                                <div>
                                    <div class="copy-box">
                                        <div class="copy-text" id="text2"><?php echo G5_SUBSCRIPTION_PATH . '/cron_script.php?t=' . $cron_token; ?></div>
                                        <button class="copy-btn" data-target="#text2">복사</button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>크론 등록 과정</label></th>
                            <td>

                                <div class="section mb30">
                                    <strong>방법 1 : 웹에서 등록 (예: <a href="https://cron-job.org" target="_blank">cron-job.org</a> 을 예로 들겠습니다.)</strong>
                                    <ol class="instructions">
                                        <li><a href="https://cron-job.org" target="_blank">cron-job.org</a>에 로그인합니다.</li>
                                        <li><strong>"CREATE CRONJOB"</strong> 버튼을 클릭합니다.</li>
                                        <li><strong>URL</strong> 항목에 다음 주소를 입력합니다:<br>
                                            <code><?php echo G5_SUBSCRIPTION_URL; ?>/cron_script.php?t=<?php echo $cron_token; ?></code>
                                        </li>
                                        <li>실행 주기는 <strong>30분 간격</strong> 또는 원하는 주기로 설정합니다.</li>
                                        <li>저장 후 <strong>활성화</strong> 상태인지 확인합니다.</li>
                                    </ol>
                                </div>

                                <div class="section">
                                    <strong>방법 2 : 서버에서 직접 등록 (Linux Crontab)</strong>
                                    <ol class="instructions">
                                        <li>서버에 SSH로 접속합니다.</li>
                                        <li>터미널에서 아래 명령어를 입력합니다:
                                            <pre><code>crontab -e</code></pre>
                                        </li>
                                        <li>아래와 같이 한 줄 추가하고 저장합니다:
                                            <pre><code>*/5 * * * * /usr/bin/php <?php echo G5_SUBSCRIPTION_PATH; ?>/cron_script.php?t=<?php echo $cron_token; ?> > /dev/null 2>&1</code></pre>
                                            <small>(PHP 경로는 <code>which php</code>로 확인 가능)</small>
                                        </li>
                                        <li>정상 등록 여부는 다음 명령어로 확인합니다:
                                            <pre><code>crontab -l</code></pre>
                                        </li>
                                    </ol>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>마지막 크론실행시간</label></th>
                            <td>
                                <?php echo get_subs_option('su_cron_updatetime'); ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>휴일API키</label></th>
                            <td>
                                <?php
                                $adm_subscription_holiday_url = G5_ADMIN_URL . '/' . G5_SUBSCRIPTION_ADMIN_DIR . '/subscription_holidays.php';
                                echo help('https://www.data.go.kr/data/15012690/openapi.do 에서 휴일정보를 가져옵니다.<br>휴일정보를 가져오면 기본적인 배송제외일에 가져온 공휴일이 추가됩니다.<br>공휴일이 추가되었다면, <a href="' . $adm_subscription_holiday_url . '" target="_blank">공휴일설정</a> 메뉴에서 api로 지정된 휴일에서 확인할수 있습니다.'); ?>

                                <input type="text" class="frm_input" name="api_holiday_data_go_key" value="<?php echo get_subs_option('api_holiday_data_go_key'); ?>" id="api_holiday_data_go_key" size="100">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="cron_night_block">CRON 야간 시간대 제외</label></th>
                            <td>
                                <?php echo help('체크시 야간 시간대 (오후11시에서 오전9시까지) 에는 CRON을 실행하지 않습니다.'); ?>

                                <input type="checkbox" name="cron_night_block" value="1" id="cron_night_block" <?php echo get_subs_option('cron_night_block') ? 'checked' : ''; ?>> 사용
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label>배송주기 타이틀</label></th>
                            <td>
                                <?php echo help('사용자 화면에서 기본적으로 표시되는 "배송주기"라는 문구를, 이 입력값으로 설정한 텍스트로 변경하여 출력합니다.'); ?>
                                <input type="text" class="frm_input" name="su_user_delivery_title" value="<?php echo get_subs_option('su_user_delivery_title'); ?>" size="50" placeholder="배송주기">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>이용횟수 타이틀</label></th>
                            <td>
                                <?php echo help('사용자 화면에서 이용횟수 라고 출력되는 타이틀을 해당 입력값으로 대체하여 출력합니다.'); ?>
                                <input type="text" class="frm_input" name="su_user_select_title" value="<?php echo get_subs_option('su_user_select_title'); ?>" size="50" placeholder="이용횟수">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row"><label>사용자 배송주기 입력 허용</label></th>
                            <td>
                                <?php echo help('체크 시 배송주기의 결제주기 입력이 비활성화되며, 사용자가 직접 일(day) 단위로 배송주기를 입력할 수 있습니다.'); ?>
                                <input type="checkbox" name="su_chk_user_delivery" id="su_chk_user_delivery" value="1" <?php echo get_subs_option('su_chk_user_delivery') ? 'checked' : ''; ?> >
                                <label for="su_chk_user_delivery">사용자 직접 입력 허용 (입력은 일(day)로 제한)</label>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>배송주기 기본값</label></th>
                            <td>
                                <?php echo help('사용자 배송주기 입력 허용 체크시 배송주기의 결제주기 기본값을 설정합니다. 사용자가 입력하지 않을 경우 이 값이 기본으로 적용됩니다.<br>예: 20일'); ?>
                                <input type="number" class="frm_input" name="su_user_delivery_default_day" value="<?php echo get_subs_option('su_user_delivery_default_day'); ?>" size="10">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label>최소 입력일 제한</label></th>
                            <td>
                                <?php echo help('사용자 배송주기 입력 허용 체크시 사용자가 입력할 수 있는 배송주기의 최소 일수를 제한합니다.<br>예: 3일'); ?>
                                <input type="number" class="frm_input" name="su_user_delivery_minimum" value="<?php echo get_subs_option('su_user_delivery_minimum'); ?>" size="10">
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">구독정보입력 출력형식</th>
                            <td>
                                <select name="su_output_display_type">
                                    <?php echo option_selected(0, get_subs_option('su_output_display_type'), "셀렉트박스"); ?>
                                    <?php echo option_selected(1, get_subs_option('su_output_display_type'), "버튼식"); ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">구독정보입력</th>
                            <td>
                                <div>
                                    <div class="local_desc01 local_desc">
                                        <dl>
                                            <dt>정기결제 주문폼</dt>
                                            <dd>{입력} {결제주기} {기타}</dd>
                                        </dl>
                                        <p><span class="frm_info">이것은 헬프문입니다.</span></p>
                                        <p>일 선택시 기본값은 매일 정기결제합니다.</p>
                                        <p>주 선택시 기본값은 매주 결제한 요일에 정기결제합니다.</p>
                                        <p>월 선택시 기본값은 매월 결제한 일에 정기결제합니다.</p>
                                        <p>년 선택시 기본값은 매년 결제한 일에 정기결제합니다.</p>
                                    </div>
                                </div>
                                <div class="add_subscription-options">
                                    <button type="button" id="add-supply-subscriptions" class="btn_frmline">옵션추가</button>
                                </div>
                                <div id="sit_supply_frm">
                                    <table>
                                        <tr class="not-remove">
                                            <th>삭제체크</th>
                                            <th>입력</th>
                                            <th>결제주기</th>
                                            <th>기타</th>
                                            <th>출력텍스트</th>
                                            <th>사용여부</th>
                                        </tr>
                                        <?php
                                        $i = 0;
                                        if ($subscription_info_inputs) { ?>
                                            <?php
                                            foreach ($subscription_info_inputs as $opt) {
                                                $disabled_attr = ($i === 0) ? 'disabled' : '';

                                            ?>
                                                <tr class="trtr" data-jbox-content="" data-index="<?php echo $i; ?>">
                                                    <td>
                                                        <input type="hidden" name="opt_id[]" value="<?php echo $opt['opt_id']; ?>">
                                                        <input type="checkbox" name="opt_chk[]" id="opt_chk_<?php echo $i; ?>" <?php echo $disabled_attr; ?>>
                                                    </td>
                                                    <td>
                                                        <span class="default_format">
                                                            <?php if ($opt['opt_date_format'] === 'year') { ?>
                                                                <input type="number" name="opt_input[]" class="frm_input input-disabled" value="1" readonly>
                                                            <?php } else { ?>
                                                                <input type="number" name="opt_input[]" class="frm_input" value="<?php echo $opt['opt_input']; ?>">
                                                            <?php } ?>
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
                                                        <span class="default_etc_format">
                                                            <?php if ($opt['opt_date_format'] === 'week') { ?>
                                                                <select name="opt_etc[]">
                                                                    <option value="">선택안함</option>
                                                                    <?php echo option_selected("mon", $opt['opt_etc'], "월"); ?>
                                                                    <?php echo option_selected("tue", $opt['opt_etc'], "화"); ?>
                                                                    <?php echo option_selected("wed", $opt['opt_etc'], "수"); ?>
                                                                    <?php echo option_selected("thu", $opt['opt_etc'], "목"); ?>
                                                                    <?php echo option_selected("fri", $opt['opt_etc'], "금"); ?>
                                                                </select>요일
                                                            <?php } else if ($opt['opt_date_format'] === 'month') { ?>
                                                                <input type="number" name="opt_etc[]" class="frm_input month_input" min="0" max="31" value="<?php echo $opt['opt_etc']; ?>">일
                                                            <?php } else { ?>
                                                                <input type="hidden" name="opt_etc[]" class="frm_input" value="">
                                                            <?php } ?>
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="frm_input subscription_print_format" name="opt_print[]" title="" value="<?php echo $opt['opt_print']; ?>" size="40">
                                                    </td>
                                                    <td>
                                                        <select name="opt_use[]" id="spl_use_<?php echo $i; ?>">
                                                            <?php echo option_selected("1", $opt['opt_use'], "사용함"); ?>
                                                            <?php echo option_selected("0", $opt['opt_use'], "사용안함"); ?>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php $i++;
                                            } // end foreach 
                                            ?>
                                        <?php } else { ?>
                                            <tr class="trtr" data-jbox-content="">
                                                <td>
                                                    <input type="hidden" name="opt_id[]" value="1">
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
                                                        <option value="week" selected>주</option>
                                                        <option value="month">월</option>
                                                        <option value="year">년</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <span class="default_etc_format">
                                                        <select name="opt_etc[]">
                                                            <option value="">선택안함</option>
                                                            <option value="mon">월요일</option>
                                                            <option value="tue">화요일</option>
                                                            <option value="wed">수요일</option>
                                                            <option value="thu">목요일</option>
                                                            <option value="fri">금요일</option>
                                                        </select></span>
                                                </td>
                                                <td>
                                                    <input type="text" class="frm_input subscription_print_format" name="opt_print[]" title="" value="" size="40">
                                                </td>
                                                <td>
                                                    <select name="opt_use[]" id="spl_use_<?php echo $i; ?>">
                                                        <option value="1">사용함</option>
                                                        <option value="0">사용안함</option>
                                                    </select>
                                                </td>
                                            </tr>
                                        <?php } // end if 
                                        ?>
                                    </table>
                                </div>
                                <div class="btn_list01 btn_list">
                                    <button type="button" id="sel_option_delete" class="btn btn_02">선택삭제</button>
                                </div>

                                <div>
                                    <h3>이용횟수</h3>

                                    <div id="use_number_addfrm_btn"><button type="button" id="add_use_number" class="btn_frmline">이용횟수 옵션추가</button></div>
                                    <div id="use_number_frm">
                                        <table>
                                            <tr class="not-remove">
                                                <th>삭제체크</th>
                                                <th>입력</th>
                                                <th>출력텍스트</th>
                                                <th>사용여부</th>
                                            </tr>
                                            <?php
                                            $i = 0;
                                            if ($subscription_use_inputs) { ?>
                                                <?php
                                                foreach ($subscription_use_inputs as $use) {

                                                    if (!(isset($use['use_input']) && $use['use_input'])) {
                                                        continue;
                                                    }

                                                    $disabled_attr = ($i === 0) ? 'disabled' : '';
                                                ?>
                                                    <tr class="trtr" data-jbox-content="">
                                                        <td>
                                                            <input type="hidden" name="use_id[]" value="<?php echo $use['use_id']; ?>">
                                                            <input type="checkbox" name="use_chk[]" id="use_chk_<?php echo $i; ?>" <?php echo $disabled_attr; ?>>
                                                        </td>
                                                        <td>
                                                            <span class="default_format">
                                                                <input type="number" name="use_input[]" class="frm_input" required min="1" value="<?php echo get_text($use['use_input']); ?>">
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="frm_input subscription_print_format" name="use_print[]" title="" value="<?php echo $use['use_print']; ?>" size="40">
                                                        </td>
                                                        <td>
                                                            <select name="num_use[]" id="num_use_<?php echo $i; ?>">
                                                                <?php echo option_selected("1", $use['num_use'], "사용함"); ?>
                                                                <?php echo option_selected("0", $use['num_use'], "사용안함"); ?>
                                                            </select>
                                                        </td>
                                                    </tr>
                                                <?php $i++;
                                                } // end foreach 
                                                ?>
                                            <?php } else { ?>
                                                <tr class="trtr" data-jbox-content="">
                                                    <td>
                                                        <input type="hidden" name="use_id[]" value="1">
                                                        <input type="checkbox" name="use_chk[]" id="use_chk_<?php echo $i; ?>" disabled value="1">
                                                    </td>
                                                    <td>
                                                        <span class="default_format">
                                                            <input type="number" name="use_input[]" class="frm_input" value="1" required min="1">
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="text" class="frm_input subscription_print_format" name="use_print[]" title="">
                                                    </td>
                                                    <td>
                                                        <select name="num_use[]" id="num_use_<?php echo $i; ?>">
                                                            <option value="1">사용함</option>
                                                            <option value="0">사용안함</option>
                                                        </select>
                                                    </td>
                                                </tr>
                                            <?php } // end if 
                                            ?>
                                        </table>
                                    </div>
                                    <div class="btn_list01 btn_list">
                                        <button type="button" id="use_option_delete" class="btn btn_02">선택삭제</button>
                                    </div>

                                </div>
                                <script>

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
                                            onOpen: function() {
                                                // this.source.addClass('active').html('Now scroll');
                                            },
                                            onClose: function() {
                                                // this.source.removeClass('active').html('Click me');
                                            }
                                        });

                                    }

                                    $(document).on("input", ".month_input", function(e) {
                                        var value = parseInt($(this).val(), 10);
                                        if (value > 31) {
                                            $(this).val(31);
                                        } else if (value < 0) {
                                            $(this).val(0); // 최소값은 0으로 설정
                                        }
                                    });

                                    $("#sit_supply_frm .trtr").each(function(index, item) {
                                        var $this = $(this);

                                        jBox_tooltip_attach($this);
                                    });

                                    $(document).on("click", "#add-supply-subscriptions", function(e) {
                                        var $el = $("#sit_supply_frm tr:last"),
                                            newRow = $el.clone();

                                        var values = $("input[name='opt_id[]']").map(function() {
                                            return parseInt($(this).val(), 10); // 값을 정수로 변환
                                        }).get(); // jQuery 객체를 일반 배열로 변환

                                        var maxValue = Math.max.apply(null, values);
                                        var newIndex = parseInt($el.attr("data-index")) + 1;

                                        // 복사된 tr 내부의 값 초기화
                                        newRow.removeClass("not-remove");
                                        newRow.find('input[name="opt_id[]"]').val(maxValue + 1);
                                        newRow.find('input[type="checkbox"]').prop('checked', false).removeAttr("disabled"); // 체크박스 해제
                                        newRow.find('input[type="text"]').val(''); // 텍스트 초기화
                                        // newRow.find('select[name="opt_date_format[]"]').val('day'); // 기본 선택값 설정 (필요 시 변경 가능)
                                        // 기존 인덱스를 찾아 새로운 인덱스로 업데이트
                                        newRow.attr("data-index", newIndex);

                                        /*
                                        // opt_input 및 opt_etc name 속성 업데이트
                                        newRow.find('input[name^="opt_input["]').each(function() {
                                            var newName = "opt_input[" + newIndex + "]";
                                            $(this).attr("name", newName);
                                        });
                                        
                                        newRow.find('input[name^="opt_etc["]').each(function() {
                                            var newName = "opt_etc[" + newIndex + "]";
                                            $(this).attr("name", newName);
                                        });
                                        */

                                        $el.after(newRow);
                                        jBox_tooltip_attach(newRow);

                                    });

                                    // 선택삭제
                                    $(document).on("click", "#sel_option_delete", function() {
                                        var $el = $("input[name='opt_chk[]']:checked");
                                        if ($el.length < 1) {
                                            alert("삭제하려는 옵션을 하나 이상 선택해 주십시오.");
                                            return false;
                                        }

                                        if ($el.closest("tr").hasClass("not-remove")) {} else {
                                            $el.closest("tr").remove();
                                        }
                                    });

                                    function get_yoil(str) {
                                        var arr_yoil = {
                                            "mon": "월요일",
                                            "tue": "화요일",
                                            "wed": "수요일",
                                            "thu": "목요일",
                                            "fri": "금요일",
                                            "sat": "토요일",
                                            "sun": "일요일"
                                        };

                                        return arr_yoil[str] || '';
                                    }

                                    function getHangulDateFormat(str) {
                                        var formats = {
                                            day: '일',
                                            week: '주',
                                            month: '월',
                                            year: '년'
                                        };

                                        return formats[str] || '{결제주기}';
                                    }

                                    function subcription_opt_change_event($selector, is_change_format = 0) {
                                        const $row = $selector.closest("tr");
                                        const $defaultFormatInput = $row.find(".default_format");
                                        const $defaultEtcFormat = $row.find(".default_etc_format");
                                        const $opt_print = $row.find('[name="opt_print[]"]');
                                        const $opt_input = $row.find('[name="opt_input[]"]');
                                        const $opt_etc = $row.find('[name="opt_etc[]"]');
                                        const $selectTag = $row.find('[name="opt_date_format[]"]');
                                        const selectedValue = $selectTag.val();

                                        var day_content = "매일 정기결제합니다.",
                                            week_content = "매주 결제한 요일에 정기결제합니다.",
                                            month_content = "매월 결제한 일에 정기결제합니다.",
                                            year_content = "매년 결제한 일에 정기결제합니다.";

                                        var $opt_input_val = $opt_input.val(),
                                            $opt_print_val = $opt_print.val(),
                                            $opt_etc_val = $opt_etc.val();

                                        if ($opt_input_val && is_change_format === 2) {
                                            day_content = "매 " + $opt_input_val + "일 마다 정기결제합니다.";
                                            week_content = "매 " + $opt_input_val + "주마다 " + get_yoil($opt_etc_val) + " 마다 정기결제합니다.";
                                            month_content = "매 " + $opt_input_val + "월마다 " + $opt_etc_val + "일에 정기결제합니다.";
                                        }

                                        $opt_print_val = $opt_print_val.replace("{입력}", $opt_input_val);
                                        $opt_print_val = $opt_print_val.replace("{결제주기}", getHangulDateFormat(selectedValue));

                                        if (selectedValue === 'week') {
                                            $opt_print_val = $opt_print_val.replace("{기타}", get_yoil($opt_etc_val));
                                        } else if (selectedValue === 'month') {
                                            $opt_print_val = $opt_print_val.replace("{기타}", $opt_etc_val + "일");
                                        } else {
                                            $opt_print_val = $opt_print_val.replace("{기타}", "");
                                        }

                                        var add_content = "<br>" + $opt_print_val;


                                        // 데이터와 템플릿 정의
                                        const templates = {
                                            day: {
                                                input: '<input type="number" name="opt_input[]" class="frm_input" value="1">',
                                                etc_input: '<input type="hidden" name="opt_etc[]" class="frm_input" value="">',
                                                content: day_content + add_content
                                            },
                                            week: {
                                                input: '<input type="number" name="opt_input[]" class="frm_input" value="1">',
                                                etc_input: `
                                    <select name="opt_etc[]">
                                        <option value="">선택안함</option>
                                        <option value="mon">월요일</option>
                                        <option value="tue">화요일</option>
                                        <option value="wed">수요일</option>
                                        <option value="thu">목요일</option>
                                        <option value="fri">금요일</option>
                                    </select>요일`,
                                                content: week_content + add_content
                                            },
                                            month: {
                                                input: '<input type="number" name="opt_input[]" class="frm_input" value="1">',
                                                etc_input: '<input type="number" name="opt_etc[]" class="frm_input month_input" min="0" max="31" value="0">일',
                                                content: month_content + add_content
                                            },
                                            year: {
                                                input: '<input type="number" name="opt_input[]" class="frm_input input-disabled" value="1" readonly>',
                                                etc_input: '<input type="hidden" name="opt_etc[]" class="frm_input" value="">',
                                                content: year_content + add_content
                                            }
                                        };

                                        // 선택 값에 따른 동작
                                        const template = templates[selectedValue];

                                        if (template) {
                                            if (is_change_format !== 2) {
                                                $defaultFormatInput.html(template.input);
                                                $defaultEtcFormat.html(template.etc_input);
                                            }
                                            // $defaultEtcFormat.html(template.etc_input);
                                            $row.attr("data-jbox-content", template.content);
                                            $row.trigger("changeinput");
                                        }
                                    }

                                    $(document).on("change input", '[name="opt_input[]"]', function(e) {

                                        subcription_opt_change_event($(this), 2);

                                    });

                                    $(document).on("change input focus", 'input[name="opt_print[]"]', function(e) {
                                        var $this = $(this);

                                        subcription_opt_change_event($(this), 2);

                                    });

                                    $(document).on("change", "select[name='opt_date_format[]']", function(e) {

                                        subcription_opt_change_event($(this), 1);

                                    });

                                    $("#add_use_number").click(function() {
                                        var $el = $("#use_number_frm tr:last"),
                                            newRow = $el.clone();

                                        var values = $("input[name='use_id[]']").map(function() {
                                            return parseInt($(this).val(), 10); // 값을 정수로 변환
                                        }).get(); // jQuery 객체를 일반 배열로 변환

                                        var maxValue = Math.max.apply(null, values);

                                        // 복사된 tr 내부의 값 초기화
                                        newRow.removeClass("not-remove");
                                        newRow.find('input[name="use_id[]"]').val(maxValue + 1);
                                        newRow.find('input[type="checkbox"]').prop('checked', false).removeAttr("disabled"); // 체크박스 해제
                                        newRow.find('input[type="text"]').val(''); // 텍스트 초기화
                                        newRow.find('input[type="number"]').val(''); // 텍스트 초기화
                                        // newRow.find('select[name="opt_date_format[]"]').val('day'); // 기본 선택값 설정 (필요 시 변경 가능)

                                        $el.after(newRow);
                                        jBox_tooltip_attach(newRow);

                                        // supply_sequence();
                                    });

                                    $("#use_number_frm .trtr").each(function(index, item) {
                                        var $this = $(this);

                                        jBox_tooltip_attach($this);
                                    });

                                    $(document).on("change input", '[name="use_input[]"]', function(e) {

                                        subcription_use_change_event($(this), 2);

                                    });

                                    $(document).on("change input", 'input[name="use_print[]"]', function(e) {
                                        var $this = $(this);

                                        subcription_use_change_event($(this), 2);

                                    });

                                    function subcription_use_change_event($selector, is_change_format = 0) {
                                        const $row = $selector.closest("tr");
                                        const $use_print = $row.find('[name="use_print[]"]');
                                        const $use_input = $row.find('[name="use_input[]"]');


                                        var content = "입력한 횟수를 위의 선택된 결제주기에 결제합니다.",
                                            add_content = "";

                                        var $use_input_val = $use_input.val(),
                                            $use_print_val = $use_print.val();

                                        if ($use_input_val && is_change_format === 2) {
                                            content = $use_input_val + "회를 위의 선택된 결제주기에 결제합니다.";
                                        }


                                        if ($use_print_val) {

                                            $use_print_val = $use_print_val.replace("{입력}", $use_input_val);
                                            add_content = "<br>" + $use_print_val;
                                        }

                                        // 선택 값에 따른 동작
                                        const template = content + add_content;

                                        if (template) {
                                            $row.attr("data-jbox-content", template);
                                            $row.trigger("changeinput");
                                        }
                                    }
                                </script>

                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="su_hope_date_use">첫희망배송일사용</label></th>
                            <td>
                                <?php echo help("'예'로 설정한 경우 주문서에서 희망배송일을 입력 받습니다."); ?>
                                <select name="su_hope_date_use" id="su_hope_date_use">
                                    <option value="0" <?php echo get_selected(get_subs_option('su_hope_date_use'), 0); ?>>사용안함</option>
                                    <option value="1" <?php echo get_selected(get_subs_option('su_hope_date_use'), 1); ?>>사용</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="su_hope_date_after">첫희망배송 준비일</label></th>
                            <td>
                                <?php echo help("사용자가 정기배송 신청 시, 오늘로부터 최소 몇 일 이후부터 첫 희망 배송일을 선택할 수 있도록 설정합니다.<br>예: 3일로 설정 시, 오늘이 25년 7월 28일 (월)이라면 25년 7월 31일 (수)부터 희망 배송일 선택 가능.<br>※ 0으로 설정하면 오늘 날짜도 선택할 수 있습니다."); ?>
                                <input type="number" name="su_hope_date_after" value="<?php echo get_sanitize_input(get_subs_option('su_hope_date_after')); ?>" id="su_hope_date_after" class="frm_input" size="5"> 일
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="su_hope_date_after">배송일 이전 자동결제 설정일</label></th>
                            <td>
                                <?php echo help("자동결제 날짜는 배송일 기준으로 몇 일 전인지 설정할 수 있습니다.<br>예를 들어 ‘배송일 3일 전 자동결제’로 설정하면, 매번 배송 예정일 3일 전에 자동으로 결제가 진행됩니다.<br>‘희망배송일 사용’을 사용안함으로 설정시, 정기구독을 신청한 순간 1회차 결제가 즉시 이루어지며,<br>배송은 설정된 자동결제 간격을 기준으로 이후 날짜로 자동 지정됩니다."); ?>
                                <input type="number" name="su_auto_payment_lead_days" value="<?php echo get_sanitize_input(get_subs_option('su_auto_payment_lead_days')); ?>" id="su_auto_payment_lead_days" class="frm_input" size="5"> 일
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">정기결제 폼 첫번째 안내문</th>
                            <td><?php echo editor_html('su_subscription_content_first', get_text(html_purifier(get_subs_option('su_subscription_content_first')), 0)); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">정기결제 폼 마지막 안내문</th>
                            <td><?php echo editor_html('su_subscription_content_end', get_text(html_purifier(get_subs_option('su_subscription_content_end')), 0)); ?></td>
                        </tr>

                        <tr>
                            <th scope="row"><label for="su_pg_service">결제대행사</label></th>
                            <td>
                                <input type="hidden" name="su_pg_service" id="su_pg_service" value="<?php echo get_subs_option('su_pg_service'); ?>">
                                <?php echo help('정기결제에서 사용할 결제대행사를 선택합니다.'); ?>
                                <ul class="de_pg_tab">
                                    <li class="<?php if (get_subs_option('su_pg_service') == 'inicis') echo 'tab-current'; ?>"><a href="#inicis_info_anchor" data-value="inicis" title="KG이니시스 선택하기">KG이니시스</a></li>
                                </ul>
                            </td>
                        </tr>
                        <tr class="pg_info_fld inicis_info_fld" id="inicis_info_anchor">
                            <th scope="row">
                                <label for="su_inicis_mid">KG이니시스 상점아이디</label><br>
                                <a href="http://sir.kr/main/service/inicis_pg.php" target="_blank" id="scf_kgreg" class="kg_btn">KG이니시스 신청하기</a>
                            </th>
                            <td>
                                <?php echo help("KG이니시스로 부터 발급 받으신 상점아이디(MID) 10자리 중 SIR 을 제외한 나머지 7자리를 입력 합니다.\n만약, 상점아이디가 SIR로 시작하지 않는다면 계약담당자에게 변경 요청을 해주시기 바랍니다. (Tel. 02-3430-5858) 예) SIRpaytest"); ?>
                                <span class="sitecode">SIR</span> <input type="text" name="su_inicis_mid" value="<?php echo get_sanitize_input(get_subs_option('su_inicis_mid')); ?>" id="su_inicis_mid" class="frm_input code_input" size="10" maxlength="10"> 영문소문자(숫자포함 가능)
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
    function fconfig_check(f) {
        <?php echo get_editor_js('su_subscription_content_first'); ?>
        <?php echo get_editor_js('su_subscription_content_end'); ?>

        var msg = "",
            pg_msg = "";

        if (msg) {
            if (confirm(msg)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    jQuery(function($) {

        $(document).on("click", ".de_pg_tab a", function(e) {

            var pg = $(this).attr("data-value"),
                class_name = "tab-current";

            $("#su_pg_service").val(pg);
            $(this).parent("li").addClass(class_name).siblings().removeClass(class_name);

            $(".pg_vbank_url:visible").hide();
            $("#" + pg + "_vbank_url").show();
            $(".scf_cardtest").addClass("scf_cardtest_hide");
            $("." + pg + "_cardtest").removeClass("scf_cardtest_hide");
            $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
            $("#" + pg + "_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");

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
            $("." + pg + "_info_fld").show();
            $("#" + pg + "_vbank_url").show();
            $(".scf_cardtest").addClass("scf_cardtest_hide");
            $("." + pg + "_cardtest").removeClass("scf_cardtest_hide");
            $(".scf_cardtest_tip_adm").addClass("scf_cardtest_tip_adm_hide");
            $("#" + pg + "_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");
        });

        $(".scf_cardtest").addClass("scf_cardtest_hide");
        $(".<?php echo get_subs_option('su_pg_service'); ?>_cardtest").removeClass("scf_cardtest_hide");
        $("#<?php echo get_subs_option('su_pg_service'); ?>_cardtest_tip").removeClass("scf_cardtest_tip_adm_hide");

        $(document).on("click", ".copy-btn", function(e) {
            e.preventDefault();

            const target = $(this).data('target');
            const text = $(target).text();

            const $temp = $('<textarea>');
            $('body').append($temp);
            $temp.val(text).select();
            document.execCommand('copy');
            $temp.remove();

            alert('복사되었습니다:\n' + text);
        });
    });
</script>
<?php

// kcp의 경우 pp_cli 체크
if (get_subs_option('su_pg_service') == 'kcp') {

    $is_linux = true;
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
        $is_linux = false;

    $exe = '/kcp/bin/';
    if ($is_linux) {
        if (PHP_INT_MAX == 2147483647) // 32-bit
            $exe .= 'pp_cli';
        else
            $exe .= 'pp_cli_x64';
    } else {
        $exe .= 'pp_cli_exe.exe';
    }

    echo module_exec_check(G5_SUBSCRIPTION_PATH . $exe, 'pp_cli');
}

include_once(G5_ADMIN_PATH . '/admin.tail.php');
