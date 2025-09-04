<?php
/* 
 * 팝빌 - 알림톡 프리셋 설정
*/
// 템플릿 목록 조회
$templates = get_popbill_template_list();

add_javascript('<script src="'.G5_JS_URL.'/kakao5.js"></script>', 1); // 카카오톡5 솔루션 js 추가
?>

<?php 
// 팝빌 연결이 제대로 안되었을 경우 아래와 같이 표시
$check_popbill = get_popbill_service_info();

if(isset($check_popbill['error'])) { ?>
<h2 class="h2_frm">팝빌 카카오톡 발송 서비스를 사용할 수 없습니다.</h2>
    <div class="local_desc01 local_desc">
    <p>팝빌 서비스 설정이 되어 있지 않아, 프리셋 서비스를 사용할 수 없습니다.
    <br>팝빌 설정은 <a href="<?php echo G5_ADMIN_URL;?>/config_form.php#anc_cf_mail" class="btn_frmline">환경설정 &gt; 기본환경설정 &gt; 기본알림환경</a> 에서 확인 및 설정해 주셔야 사용하실 수 있습니다.</p><br>
    <p>* 설정 오류 내용 : <span style="color:red"><?php echo $check_popbill["error"];?></span></p>
</div>
<?php } else { ?>
<div class="local_desc01 local_desc">
    <p><a href="#" class="btn_template_manage" id="btnTemplateManageInfo">템플릿관리</a>를 통해 신청 후 승인된 템플릿만 사용가능합니다.<br>템플릿 내용 작성 시, 동일한 <b>[구분]</b>에 속한 변수만 사용 가능하며, 아래에 제공된 변수 외의 항목을 입력할 경우 적용되지 않습니다.</p><br>
    <p>아래 표의 <b>#{변수명}</b>만 템플릿 내용에 사용할 수 있으며, 실제 발송 시 값으로 자동 치환됩니다.<br><span style="color:#888;">※ 표에 없는 변수는 치환되지 않습니다.</span></p>
    <p id="variable_toggle" class="variable_toggle" style="cursor:pointer; font-weight:bold;">[사용 가능한 변수 리스트<span class="btn_toggle_text" style="font-weight:normal;">▼</span>]</p>
    <div id="variable_table" class="variable_table">
        <table class="tbl_head01 tbl_wrap">
            <caption>제공 변수 목록</caption>
            <thead>
                <tr>
                    <th scope="col">구분</th>
                    <th scope="col">변수명</th>
                    <th scope="col">설명</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // [정의] $kakao5_preset_variable_list - extend/kakao5.extend.php
                if (!empty($kakao5_preset_variable_list) && is_array($kakao5_preset_variable_list)) {
                    foreach ($kakao5_preset_variable_list as $category) {
                        $cat_name = isset($category['category']) ? $category['category'] : '';
                        $vars = isset($category['variables']) ? $category['variables'] : [];
                        $rowspan = count($vars);
                        foreach ($vars as $idx => $var) {
                            ?>
                            <tr>
                                <?php if ($idx === 0) { ?>
                                    <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($cat_name); ?></td>
                                <?php } ?>
                                <td><?php echo htmlspecialchars($var['name'] ?? ''); ?></td>
                                <td style="text-align:left;"><?php echo $var['description'] ?? ''; ?></td>
                            </tr>
                            <?php
                        }
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <br>

    <p>아래 표의 <b>#{버튼링크명}</b>은 버튼에 사용할 수 있으며, 실제 발송 시 지정된 URL로 자동 치환됩니다.<br><span style="color:#888;">※ 표에 없는 버튼 링크 변수는 치환되지 않습니다. 등록 시 [<b>https://#{버튼링크명}</b>]으로 작성하시면 됩니다.</span></p>
    <p id="button_link_toggle" class="button_link_toggle" style="cursor:pointer; font-weight:bold;">[버튼 링크 치환 리스트<span class="button_link_toggle_text" style="font-weight:normal;">▼</span>]</p>
    <div id="button_link_table" class="variable_table">
        <table class="tbl_head01 tbl_wrap">
            <caption>제공 변수 목록</caption>
            <thead>
                <tr>
                    <th scope="col">버튼링크명</th>
                    <th scope="col">설명</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // [정의] $kakao5_preset_button_links - extend/kakao5.extend.php
                if (!empty($kakao5_preset_button_links) && is_array($kakao5_preset_button_links)) {
                    foreach ($kakao5_preset_button_links as $key => $val) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($key); ?></td>
                            <td style="text-align:left;"><?php echo isset($val['description']) ? $val['description'] : ''; ?><br>
                                <?php if (isset($val['url'])) { ?>
                                    <span style="color:#888;">* URL: <?php echo htmlspecialchars($val['url']); ?></span>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<div class="local_desc01 local_desc">
    <p><b>* 관리자 휴대폰번호</b> : 관리자로 설정된 <b>[<?php echo $config['cf_admin'];?>]</b>의 <b>휴대폰 번호</b>를 사용합니다.</p>
    <p><b>* 그룹 관리자 휴대폰번호</b> : 그룹 관리자로 지정된 아이디의 <b>휴대폰 번호</b>를 사용합니다.</p>
    <p><b>* 게시판 관리자 휴대폰번호</b> : 게시판 관리자로 지정된 아이디의 <b>휴대폰 번호</b>를 사용합니다.</p>
</div>

<?php if (empty($config['cf_req_hp'])) { ?>
<div class="admin_pg_notice od_test_caution">
    <p>
        <?php 
        $link = '<br><a href="'.G5_ADMIN_URL.'/config_form.php#anc_cf_join">환경설정 &gt; 기본환경설정 &gt; 회원가입</a>';

        if (!empty($config['cf_use_hp'])) {
            // 보이기만 설정된 경우
            echo '<b>[휴대폰번호 입력]</b>이 <b>[보이기]</b>로 설정되어 있습니다. 일부 회원은 휴대폰 번호를 입력하지 않아 발송이 제한될 수 있습니다.'
                . $link . '에서 <b>[필수입력]</b>으로 설정하는 것을 권장합니다.';
        } else {
            // 둘 다 설정 안 된 경우
            echo '<b>[휴대폰번호 입력]</b>이 <b>[보이기]</b> 또는 <b>[필수입력]</b>으로 설정되어 있지 않습니다. 현재 상태에서는 알림톡 발송이 불가능합니다.' 
                . $link . '에서 반드시 <b>[보이기]</b>나 <b>[필수입력]</b> 중 하나 이상으로 설정해야 합니다.';
        }
        ?>
    </p>
</div>
<?php } ?>

<form name="falimtalkpreset" action="./alimtalkpresetupdate.php" method="post" enctype="MULTIPART/FORM-DATA">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    
    <section id="anc_scf_customer">
        <div class="tbl_head01 tbl_wrap">
            <table>
                <caption>알림톡 프리셋 목록</caption>
                <thead>
                    <tr>
                        <th scope="col"><?php echo subject_sort_link('kp_active') ?>사용여부</a></th>
                        <th scope="col"><?php echo subject_sort_link('kp_category') ?>구분</a></th>
                        <th scope="col">발송시점</th>
                        <th scope="col"><?php echo subject_sort_link('kp_type') ?>대상</a></th>
                        <th scope="col">템플릿 명</th>
                        <th scope="col">미리보기</th>
                        <th scope="col">버튼정보</th>
                        <th scope="col">문자대체발송</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    for ($ci=0; $row=sql_fetch_array($result); $ci++) {
                        $bg = 'bg'.($ci%2);
                    ?>
                    <tr class="<?php echo $bg; ?>">
                        <td class="td_chk2">
                            <label class="preset-toggle">
                                <input type="checkbox" name="kp_active[<?php echo $row['kp_id']; ?>]" value="1" <?php echo $row['kp_active'] == '1' ? 'checked' : ''; ?> onchange="toggleTemplateFields(<?php echo $row['kp_id']; ?>)">
                                <span class="preset-slider"></span>
                            </label>
                        </td>

                        <td class="td_chk2"><?php echo get_sanitize_input($row['kp_category']); ?></td>
                        <td class="td_cnt"><?php echo get_sanitize_input($row['kp_preset_name']); ?></td>
                        <td class="td_chk2"><?php echo $row['kp_type']; ?></td>

                        <td class="td_category1">
                            <select name="kp_template_name[<?php echo $row['kp_id']; ?>]" id="template_<?php echo $row['kp_id']; ?>" style="min-width: 350px;">
                                <option value="">템플릿 선택</option>
                                <?php                                    
                                    $template_content = '';
                                    $buttons = '';
                                    if (!empty($templates) && is_array($templates)) {
                                        foreach ($templates as $tpl) {
                                            $tplName = $tpl->templateName ?? '';
                                            $tplCode = $tpl->templateCode ?? '';

                                            if (empty($tplCode)) continue;

                                            $isSelected = ($row['kp_template_name'] == $tplCode);
                                            if ($isSelected) {
                                                $template_content = $tpl->template;
                                                $buttons = $tpl->btns;
                                            }

                                            echo '<option value="' . $tplCode . '" ' . ($isSelected ? 'selected' : '') . '>[' . ($tpl->plusFriendID ?? '') . "] ". $tplName . ' (' . $tplCode . ')' . '</option>';
                                        }
                                    }
                                ?>
                            </select>
                        </td>

                        <!-- 템플릿 보기 버튼 추가 -->
                        <td class="td_cnt">
                            <?php if (!empty($row['kp_template_name'])) { ?>                                
                                <button type="button" class="btn_template_preview_open">템플릿보기</button>
                                <div class="template_preview">
                                    <div class="template_preview_txt"><?php echo nl2br($template_content); ?></div>
                                    <button type="button" class="btn_template_preview_close">X 닫기</button>
                                </div>
                            <?php } ?>
                        </td>

                        <!-- 버튼 정보 -->
                        <td class="td_addr td_odrnum">
                            <?php if (!empty($buttons)) { ?>
                                <?php foreach ($buttons as $index => $button) {
                                    echo '<strong>[' . $button->n . ']</strong><br>';
                                    if ($button->t == 'DS') { // 배송조회 버튼
                                        echo '<span style="color:#888;">· 카카오톡검색 링크 버튼 자동생성</span><br>';
                                    }else{
                                        echo ($button->t == 'AL' ? '· iOS링크: ' : ($button->t == 'WL' ? '· Mobile링크: ' : '· 링크: ')) . $button->u1 . 
                                            ($button->u2 ? '<br>' . ($button->t == 'AL' ? '· Android링크: ' : ($button->t == 'WL' ? '· PC링크: ' : '· 링크2: ')) . $button->u2 : '') .
                                            ($button->tg ? '<br>· 아웃링크: ' . $button->tg : '') . '<br>';

                                    }
                                } ?>
                            <?php } ?>
                        </td>

                        <td class="td_category1">
                            <select name="kp_alt_send[<?php echo $row['kp_id']; ?>]" id="alt_send_<?php echo $row['kp_id']; ?>" style="min-width: 150px;">
                                <option value="1" <?php echo $row['kp_alt_send'] == '1' ? 'selected' : ''; ?>>대체문자발송</option>
                                <option value="0" <?php echo $row['kp_alt_send'] == '0' ? 'selected' : ''; ?>>사용안함</option>
                            </select>
                        </td>
                    </tr>
                    <?php } 
                        if ($ci == 0) echo '<tr><td colspan="7" class="empty_table">등록된 프리셋이 없습니다.</td></tr>';
                    ?>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 상단 버튼 [템플릿관리, 전송내역, 일괄수정] -->
    <div class="btn_fixed_top">
        <a href="#" class="btn btn_02 btn_template_manage" id="btnTemplateManage">템플릿관리</a>
        <a href="#" class="btn btn_02 btn_send_manage" id="btnSendManage">전송내역</a>
        <input type="submit" value="일괄수정" class="btn_submit btn" accesskey="s">
    </div>
</form>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 템플릿 미리보기 열기
        document.querySelectorAll('.btn_template_preview_open').forEach(button => {
            button.addEventListener('click', () => {
                // 모든 preview 닫기
                document.querySelectorAll('.template_preview').forEach(preview => {
                    preview.classList.remove('is-visible');
                });

                const previewBox = button.nextElementSibling;
                if (previewBox?.classList.contains('template_preview')) previewBox.classList.add('is-visible');
            });
        });

        // 템플릿 미리보기 닫기
        document.querySelectorAll('.btn_template_preview_close').forEach(closeBtn => {
            closeBtn.addEventListener('click', () => {
                const previewBox = closeBtn.closest('.template_preview');
                if (previewBox) previewBox.classList.remove('is-visible')
            });
        });

        // 변수 리스트 및 버튼 링크 리스트 토글 함수 리팩토링
        function setupToggleSection(toggleId, tableId, toggleTextClass = '.btn_toggle_text') {
            const toggleBtn = document.getElementById(toggleId);
            const table = document.getElementById(tableId);

            if (toggleBtn && table) {
                toggleBtn.addEventListener('click', () => {
                    const toggleText = toggleBtn.querySelector(toggleTextClass);
                    table.classList.toggle('is-visible');
                    const isVisible = table.classList.contains('is-visible');
                    if (toggleText) toggleText.textContent = isVisible ? '▲' : '▼';
                });
            }
        }

        setupToggleSection('variable_toggle', 'variable_table');
        setupToggleSection('button_link_toggle', 'button_link_table');

        // 템플릿 관리 팝업: [정의] openKakao5PopupFromAjax() - js/kakao5.js
        document.querySelectorAll('#btnTemplateManageInfo, #btnTemplateManage').forEach(btn => {
            btn.addEventListener('click', async(e) => {
                e.preventDefault();
                await openKakao5PopupFromAjax('<?php echo G5_KAKAO5_URL; ?>', 1);
            });
        });

        // 전송내역 팝업: [정의] openKakao5PopupFromAjax() - js/kakao5.js
        document.querySelectorAll('#btnSendManage').forEach(btn => {
            btn.addEventListener('click', async(e) => {
                e.preventDefault();
                await openKakao5PopupFromAjax('<?php echo G5_KAKAO5_URL; ?>', 2);
            });
        });
    });
</script>
<?php } ?>