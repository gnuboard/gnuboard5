<?php
$sub_menu = "200400";
require_once './_common.php';
require_once './member_list_exel.lib.php'; // 회원관리파일 공통 라이브러리

auth_check_menu($auth, $sub_menu, 'r');

// 파라미터 수집 및 유효성 검사
$params = get_member_export_params();

// 총건수
$total_count = 0;
$total_error = "";
try {
    $total_count = member_export_get_total_count($params);
} catch (Exception $e) {
    $total_error = $e->getMessage(); // 메서드 호출 괄호 필수
}

$g5['title'] = '회원관리파일';
require_once './admin.head.php';
$colspan = 14;
?>

<h2>회원 엑셀 생성</h2>

<div class="local_desc01 local_desc">
    <p><b>회원수 <?php echo number_format(MEMBER_EXPORT_PAGE_SIZE);?>건 초과 시</b> <?php echo number_format(MEMBER_EXPORT_PAGE_SIZE);?>건 단위로 분리 저장되며, <b>엑셀 생성 최대 건수는 <?php echo number_format(MEMBER_EXPORT_MAX_SIZE);?>건</b>입니다. 초과 시 조건 추가 설정 후 재시도하시기 바랍니다.</p>
    <p><b>수신동의 확인 대상은 만료일까지 1달 미만인 회원</b>을 기준으로 필터링됩니다.</p>

    <br>
    <p>파일 생성 시 서버에 임시 생성된 파일 중 <b>오늘 날짜를 제외 한 파일은 자동 삭제</b>되며, 수동 삭제 필요 시 <a href="<?php echo G5_ADMIN_URL;?>/member_list_file_delete.php"><b>회원관리파일 일괄삭제</b></a>에서 진행하시기 바랍니다.</p>
    <p>회원 정보 수정은 <a href="<?php echo G5_ADMIN_URL;?>/member_list.php" class="link"><b>회원 관리</b></a>에서 진행하실 수 있습니다.</p>
</div>

<div class="local_ov01 local_ov">
    <span class="btn_ov01">
        <span class="ov_txt">총건수 </span>
        <?php if($total_error != "") { ?>
        <span class="ov_num"> <?php echo $total_error ?></span>
        <?php } else {?>
        <span class="ov_num"> <?php echo number_format($total_count) ?>건</span>
        <?php } ?>
    </span>
</div>

<!-- 회원 검색 필터링 폼 -->
<form id="fsearch" name="fsearch" class="member_list_data" method="get">
    <input type="hidden" name="token" value="<?php echo get_token(); ?>">
    <fieldset>
        <legend class="sound_only">회원 검색 필터링</legend>
        <div class="sch_table">

            <!-- 검색어 적용 -->
            <div class="sch_row">
                <div class="label">
                    <label>
                        <input type="checkbox" name="use_stx" value="1" <?php echo isset($_GET['use_stx']) ? 'checked' : ''; ?>>
                        검색어 적용
                    </label>
                </div>
                <div class="field">
                    <select name="sfl">
                        <?php
                            // 검색어 옵션 : [정의] get_export_config() - adm/member_list_exel.lib.php;
                            foreach (get_export_config('sfl_list') as $val => $label) {
                                $selected = (isset($_GET['sfl']) && $_GET['sfl'] === $val) ? 'selected' : '';
                                echo "<option value=\"$val\" $selected>$label</option>";
                            }
                        ?>
                    </select>
                    <input type="text" name="stx" value="<?php echo htmlspecialchars($_GET['stx'] ?? ''); ?>" placeholder="검색어 입력">
                    <span class="radio_group">
                        <label><input type="radio" name="stx_cond" value="like" <?php echo ($_GET['stx_cond'] ?? 'like') === 'like' ? 'checked' : ''; ?>> 포함</label>
                        <label><input type="radio" name="stx_cond" value="equal" <?php echo ($_GET['stx_cond'] ?? '') === 'equal' ? 'checked' : ''; ?>> 일치</label>
                    </span>
                </div>
            </div>

            <!-- 레벨 적용 -->
            <div class="sch_row">
                <div class="label">
                    <label><input type="checkbox" name="use_level" value="1" <?php echo isset($_GET['use_level']) ? 'checked' : ''; ?>> 레벨 적용</label>
                </div>
                <div class="field">
                    <select name="level_start">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo (isset($_GET['level_start']) && $_GET['level_start'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select> ~
                    <select name="level_end">
                        <?php for ($i = 1; $i <= 10; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo (isset($_GET['level_end']) && $_GET['level_end'] == $i) ? 'selected' : ''; ?>><?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- 가입기간 적용 -->
            <div class="sch_row">
                <div class="label">
                    <label><input type="checkbox" name="use_date" value="1" <?php echo isset($_GET['use_date']) ? 'checked' : ''; ?>> 가입기간 적용</label>
                </div>
                <div class="field">
                    <input type="date" name="date_start" max="9999-12-31" value="<?php echo htmlspecialchars($_GET['date_start'] ?? ''); ?>"> ~
                    <input type="date" name="date_end" max="9999-12-31" value="<?php echo htmlspecialchars($_GET['date_end'] ?? ''); ?>">
                </div>
            </div>

            <!-- 포인트 적용 -->
            <div class="sch_row">
                <div class="label">
                    <label><input type="checkbox" name="use_point" value="1" <?php echo isset($_GET['use_point']) ? 'checked' : ''; ?>> 포인트 적용</label>
                </div>
                <div class="field">
                    <input type="number" name="point" value="<?php echo htmlspecialchars($_GET['point'] ?? ''); ?>" placeholder="포인트 입력">
                    <span class="radio_group">
                        <label><input type="radio" name="point_cond" value="gte" <?php echo ($_GET['point_cond'] ?? 'gte') === 'gte' ? 'checked' : ''; ?>> 이상</label>
                        <label><input type="radio" name="point_cond" value="lte" <?php echo ($_GET['point_cond'] ?? '') === 'lte' ? 'checked' : ''; ?>> 이하</label>
                        <label><input type="radio" name="point_cond" value="eq" <?php echo ($_GET['point_cond'] ?? '') === 'eq' ? 'checked' : ''; ?>> 일치</label>
                    </span>
                </div>
            </div>

            <!-- 차단회원 조건 -->
            <div class="sch_row">
                <div class="label">
                    <label><input type="checkbox" name="use_intercept" value="1" <?php echo isset($_GET['use_intercept']) ? 'checked' : ''; ?>> 차단회원</label>
                </div>
                <div class="field">
                    <select name="intercept" id="intercept">
                        <?php
                            // 차단회원 옵션 : [정의] get_export_config() - adm/member_list_exel.lib.php
                            foreach (get_export_config('intercept_list') as $val => $label) {
                                $selected = (($_GET['intercept'] ?? '') === $val) ? 'selected' : '';
                                echo "<option value=\"$val\" $selected>$label</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>

            <!-- 휴대폰 번호 조건 - 초기세팅(설정에 휴대폰번호가 보이기/필수입력이면 기본값 checked로 설정) -->
            <div class="sch_row">
                <div class="label">
                    <label>
                        <?php $use_hp_checked = isset($_GET['token']) ? (isset($_GET['use_hp_exist']) ? 'checked' : '') : (($config['cf_use_hp'] || $config['cf_req_hp']) ? 'checked' : '');?>
                        <input type="checkbox" name="use_hp_exist" value="1" <?php echo $use_hp_checked; ?>> 휴대폰 번호 있는 경우만
                    </label>
                </div>
            </div>

            <!-- 정보수신동의 조건 -->
            <div class="sch_row">
                <div class="label">
                    <label><input type="checkbox" name="ad_range_only" value="1" <?php echo isset($_GET['ad_range_only']) ? 'checked' : ''; ?>> 정보수신동의에 동의한 경우만</label>
                </div>
                <!-- 안내 문구 -->
                <div class="field">
                    <p class="sch_notice">「정보통신망이용촉진및정보보호등에관한법률」에 따라 <b>광고성 정보 수신동의 여부</b>를 <b>매2년</b>마다 확인해야 합니다.</p>
                </div>
            </div>

            <div class="sch_row <?php echo isset($_GET['ad_range_only']) ? '' : 'is-hidden'; ?>">
                <div class="ad_range_wrap">
                    <div class="ad_range_box">
                        <div class="label">
                            <label for="ad_range_type">회원범위</label>
                        </div>
                        <div class="field">
                            <select name="ad_range_type" id="ad_range_type">
                                <?php 
                                    foreach (get_export_config('ad_range_list') as $val => $label) {
                                        $selected = (($_GET['ad_range_type'] ?? '') === $val) ? 'selected' : '';
                                        echo "<option value=\"$val\" $selected>$label</option>";
                                    }
                                ?>
                            </select>

                            <div class="ad_range_wrap">
                                <!-- 기간 직접 입력 -->
                                <div class="ad_range_box <?php echo isset($_GET['ad_range_only']) && ($_GET['ad_range_type'] ?? '') == 'custom_period' ? '' : 'is-hidden'; ?>">
                                    <div class="field">
                                        <input type="date" name="agree_date_start" max="9999-12-31" value="<?php echo htmlspecialchars($_GET['agree_date_start'] ?? date('Y-m-d', strtotime('-1 month'))); ?>"> ~
                                        <input type="date" name="agree_date_end" max="9999-12-31" value="<?php echo htmlspecialchars($_GET['agree_date_end'] ?? date('Y-m-d')); ?>">
                                        <p>* 광고성 정보 수신(<b>이메일 또는 SMS/카카오톡</b>) 동의일자 기준</p>
                                    </div>
                                </div>

                                <!-- 설명 문구 -->
                                <?php
                                    $thirdpartyLbl = (!empty($config['cf_sms_use'])) ? ' / <b>개인정보 제3자 제공</b>' : '';

                                    $ad_range_text = [
                                        'all'           => "* <b>광고성 정보 수신(이메일 또는 SMS/카카오톡)</b> / <b>마케팅 목적의 개인정보 수집 및 이용</b>{$thirdpartyLbl}에 모두 동의한 회원을 선택합니다.",
                                        'mailling_only' => "* <b>광고성 이메일 수신</b> / <b>마케팅 목적의 개인정보 수집 및 이용</b>{$thirdpartyLbl}에 모두 동의한 회원을 선택합니다.",
                                        'sms_only'      => "* <b>광고성 SMS/카카오톡 수신</b> / <b>마케팅 목적의 개인정보 수집 및 이용</b>{$thirdpartyLbl}에 모두 동의한 회원을 선택합니다.",
                                        'month_confirm' => "* 23개월 전(" . date('Y년 m월', strtotime('-23 month')) . ") <b>광고성 정보 수신 동의(이메일 또는 SMS/카카오톡)</b>한 회원을 선택합니다."
                                    ];

                                    if (isset($_GET['ad_range_only'], $_GET['ad_range_type']) && isset($ad_range_text[$_GET['ad_range_type']])) {
                                        echo '<div class="ad_range_box"><p>' . $ad_range_text[$_GET['ad_range_type']] . '</p></div>';
                                    }
                                ?>
                            </div>
                            <br>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 채널 체크박스 -->
            <div class="sch_row <?php echo isset($_GET['ad_range_only']) && in_array($_GET['ad_range_type'], ['month_confirm', 'custom_period']) ? '' : 'is-hidden'; ?>">
                <div class="ad_range_wrap">
                    <div class="ad_range_box">
                        <div class="label">
                        </div>
                        <div class="field">
                            <?php $ad_mailling_checked = isset($_GET['token']) ? (isset($_GET['ad_mailling']) ? 'checked' : '') : 'checked';?>
                            <?php $ad_sms_checked = isset($_GET['token']) ? (isset($_GET['ad_sms']) ? 'checked' : '') : 'checked';?>
                            <label><input type="checkbox" name="ad_mailling" value="1" <?php echo $ad_mailling_checked; ?>> 광고성 이메일 수신</label>
                            <label><input type="checkbox" name="ad_sms" value="1" <?php echo $ad_sms_checked; ?>> 광고성 SMS/카카오톡 수신</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sch_btn">
                <button type="button" id="btnExcelDownload">엑셀파일 다운로드</button>
                <button type="button" class="btn_reset" onclick="location.href='?'">초기화</button>
            </div>
        </div>
    </fieldset>
</form>

<script>
document.querySelector('input[name="ad_range_only"]').addEventListener('change', function () {
  document.querySelectorAll('.ad_range_wrap').forEach(el => {
    el.classList.toggle('is-hidden', !this.checked);
  });
});

document.querySelectorAll('#fsearch input, #fsearch select').forEach(el => {
    const submit = () => document.getElementById('fsearch').submit();
    el.addEventListener(el.type === 'date' ? 'blur' : 'change', submit);

    el.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
        e.preventDefault();
        submit();
        }
    });
});
</script>

<script>
let eventSource = null;

// 일반 엑셀 다운로드 버튼 클릭
document.getElementById('btnExcelDownload').addEventListener('click', () => {
    startExcelDownload();
});

// 엑셀 다운로드 실행
// 1. 기존 SSE 종료
function closePreviousEventSource() {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
}

// 2. FormData QueryString 변환
function buildDownloadParams(selectedFields = []) {
    const formData = new FormData(document.getElementById('fsearch'));
    const params = new URLSearchParams(formData);

    params.append('mode', 'start');

    return params.toString();
}

// 3. 메인 함수
function startExcelDownload(selectedFields = []) {
    closePreviousEventSource();

    const query = buildDownloadParams(selectedFields);
    showDownloadPopup();

    eventSource = new EventSource(`member_list_exel_export.php?${query}`);
    eventSource.onmessage = handleProgressUpdate();
    eventSource.onerror = handleDownloadError();
}

// 다운로드 팝업 표시
function showDownloadPopup() {
    const bodyHTML = `
        <div class="excel-download-progress">
            <div class="progress-desc">
                <p class="progress-summary">총 <strong>0</strong>개 파일로 분할됩니다</p>
                <p class="progress-message"><strong>(0 / 0)</strong> 파일 다운로드 중</p>
                <p class="progress-error"></p>
            </div>
            <div class="progress-spinner">
                <div class="spinner"></div>
                <p class="loading-message">
                    엑셀 파일을 생성 중입니다. 잠시만 기다려주세요.<br>
                    현재 데이터 기준으로 <strong id="estimatedTimeText"></strong> 정도 소요될 수 있습니다.<br>
                    <strong>페이지를 벗어나거나 닫으면 다운로드가 중단</strong>되니, 작업 완료까지 기다려 주세요.
                </p>
            </div>
            <div class="progress-box">
                <div class="progress-download-box"></div>
            </div>
        </div>
    `;

    PopupManager.render('엑셀 다운로드 진행 중', bodyHTML, '', { disableOutsideClose: true });

    // 닫기 버튼 이벤트 핸들링
    const closeBtn = document.querySelector('.popup-close-btn');
    if (closeBtn) {
        closeBtn.removeAttribute('onclick');
        closeBtn.addEventListener('click', handlePopupCloseWithConfirm);
    }
}

// 닫기 버튼 클릭 시 다운로드 중단 여부 확인
function handlePopupCloseWithConfirm(e) {
    if (eventSource) {
        const confirmClose = confirm("엑셀 다운로드가 진행 중입니다.\n정말 중지하시겠습니까?");
        if (!confirmClose) {
            e.preventDefault();
            return;
        }
        eventSource.close();
        eventSource = null;
        alert("엑셀 다운로드가 중단되었습니다.");
    }
    PopupManager.close('popupOverlay');
}

// 체크박스 선택 시 최대 3개 제한 및 선택된 항목 미리보기 표시
function bindFieldSelectEvents() {
    const fieldSelectForm = document.getElementById('fieldSelectForm');
    if (!fieldSelectForm) return;

    fieldSelectForm.addEventListener('change', function (e) {
        if (e.target.name === 'fields') {
            const selected = fieldSelectForm.querySelectorAll('input[name="fields"]:checked');
            if (selected.length > 3) {
                alert("최대 3개까지 선택 가능합니다.");
                e.target.checked = false;
                return;
            }

            // 선택된 항목 표시
            const previewContainer = document.getElementById('selectedFieldsPreview');
            let spans = '<strong>선택된 항목:</strong>';
            selected.forEach(field => {
                const label = field.parentElement.textContent.trim();
                spans += `<span class="field-tag">${label}</span>`;
            });
            previewContainer.innerHTML = spans;
        }
    });
}

// 엑셀 생성 및 다운로드 실행
function handleProgressUpdate() {
    return function(e) {
        const data = JSON.parse(e.data);
        const { status, downloadType, message, total, current, totalChunks, currentChunk, zipFile, files, filePath } = data;

        // DOM 요소 캐싱
        const titleEl = document.getElementById('popupTitle');
        const summaryEl = document.querySelector('.progress-summary');
        const messageEl = document.querySelector('.progress-message');
        const spinnerEl = document.querySelector('.progress-spinner');
        const resultEl = document.querySelector('.loading-message');
        const downloadBoxEl = document.querySelector('.progress-download-box');
        const errorEl = document.querySelector('.progress-error');

        if (status === "progress") 
        {
            summaryEl.innerHTML = `총 <strong>${totalChunks}</strong>개 파일로 ` + (downloadType === 2 ? `분할 생성됩니다` : `다운로드됩니다`) + ` (총 ${total.toLocaleString('ko-KR')}건)`;
            messageEl.innerHTML = downloadType === 2 ? `<strong>(${currentChunk} / ${totalChunks})</strong> 파일 생성 중` : `엑셀 파일 생성 중`;

            /* 작업 소요 시간 : 예상 시간 (1만건당 10초) */
            const sec = Math.max(5, Math.ceil(total * 0.0012 * 1.2)); // 최소 5초 보장
            const text = `예상 처리 시간은 약 ${sec >= 60 ? `${Math.floor(sec / 60)}분 ${sec % 60}초` : `${sec}초`}`;
            document.getElementById('estimatedTimeText').innerText = text;
        }
        else if (status === "zipping") 
        {
            summaryEl.innerHTML = `총 <strong>${totalChunks}</strong>개 파일이 압축파일로 생성됩니다`;
            messageEl.innerHTML = `<strong>${totalChunks}</strong> 파일 압축하는 중`;
        }
        else if (status === "zippingError") 
        {
            errorEl.innerHTML = message;
        } 
        else if (status === "error") 
        {
            summaryEl.innerHTML = `엑셀 파일 다운로드 실패`;
            resultEl.innerHTML = '';
            spinnerEl?.classList.add('is-hidden');

            const parts = message.split(/<br\s*\/?>/i);
            messageEl.innerHTML = parts[0] || '';
            errorEl.innerHTML = parts.slice(1).join('<br>') || '';

            // SSE 작업 닫기
            eventSource?.close();
            eventSource = null;
        } 
        else if (status === "done") 
        {
            // SSE 작업 닫기
            eventSource?.close();
            eventSource = null;

            titleEl.textContent = '엑셀 파일 다운로드 완료';
            messageEl.innerHTML = `<strong>총 ${total.toLocaleString('ko-KR')}건의 데이터 다운로드가 완료되었습니다!</strong>`;
            spinnerEl?.classList.add('is-hidden');

            let html = '<p>* 자동으로 다운로드가 되지 않았다면 아래 버튼을 클릭해주세요.</p>';
            const baseUrl = `<?php echo G5_DATA_URL; ?>/member_list/<?php echo date('Ymdhis'); ?>/`; // 공통 URL 분리

            if (zipFile) {
                const url = `${filePath}/${zipFile}`;
                html += `<a href="${url}" class="btn btn_03" download>압축파일 다운로드</a>`;
                downloadBoxEl.innerHTML = html;
                triggerAutoDownload(url, zipFile);
            } else if (files?.length) {
                files.forEach((file, index) => {
                    const url = `${filePath}/${file}`;
                    html += `<a class="btn btn_03" href="${url}" download>엑셀파일 다운로드 ${index + 1}</a>`;
                });
                downloadBoxEl.innerHTML = html;

                if (files.length === 1) {
                    const url = `${filePath}/${files[0]}`;
                    triggerAutoDownload(url, files[0]);
                } else {
                    summaryEl.innerHTML = `총 <strong>${totalChunks}</strong>개 파일이 생성되었습니다. 아래 버튼을 눌러 다운로드 받아주세요.`;
                }
            }
        }
    }
}

// SSE 오류 처리
function handleDownloadError() {
    return function(e){
        const errorMessage = e?.message || e?.data || '알 수 없는 오류가 발생했습니다.';

        document.querySelector('.progress-summary').innerHTML = `엑셀 파일 다운로드 실패`;
        document.querySelector('.progress-message').innerHTML = `엑셀 파일 다운로드에 실패하였습니다`;
        document.querySelector('.progress-error').innerHTML = errorMessage;
        document.querySelector('.loading-message').innerHTML = '';
        document.querySelector('.progress-spinner').classList.add('is-hidden');

        if (eventSource) {
            eventSource.close();
            eventSource = null;
        }
    }
}

// 자동 다운로드 실행
function triggerAutoDownload(url, filename) {
    const a = document.createElement('a');
    a.href = url;
    a.download = filename;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
}
</script>

<?php
require_once './admin.tail.php';