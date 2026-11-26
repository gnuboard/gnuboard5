<?php
$sub_menu = '100410';
include_once('./_common.php');
include_once(G5_LIB_PATH . '/migration.lib.php');
include_once(G5_LIB_PATH . '/shop_install.lib.php');
include_once(G5_LIB_PATH . '/abuse_rate.lib.php');

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$is_execute = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
$migration_result = array('success' => true, 'applied' => 0, 'skipped' => 0, 'errors' => array());
$shop_install_result = null;
$action = '';

if ($is_execute) {
    check_demo();
    check_admin_token();
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'migrate';
    if ($action === 'install_shop') {
        $shop_install_result = g5_shop_install_run();
    } elseif ($action === 'record_existing') {
        $migration_result = g5_migration_run('', true);
    } else {
        $migration_id = isset($_POST['migration_id']) ? trim($_POST['migration_id']) : '';
        $migration_result = g5_migration_run($migration_id);
        $migration_result = run_replace('admin_dbupgrade_result', $migration_result);
    }
}

$migration_statuses = g5_migration_status();
$pending_count = 0;
$unrecorded_count = 0;
foreach ($migration_statuses as $migration_status) {
    if (isset($migration_status['status']) && $migration_status['status'] === 'unrecorded') {
        $unrecorded_count++;
    } elseif (isset($migration_status['error']) || !g5_migration_status_complete($migration_status['status']) || $migration_status['checksum_changed']) {
        $pending_count++;
    }
}

if ($shop_install_result !== null && $shop_install_result['success']) {
    alert('쇼핑몰 설치가 완료되었습니다.', G5_ADMIN_URL . '/dbupgrade.php');
} elseif ($shop_install_result !== null) {
    $db_upgrade_msg = '쇼핑몰 설치에 실패했습니다. 오류 내용을 확인해 주십시오.';
} elseif (!$is_execute) {
    $db_upgrade_msg = $pending_count ? '확인·실행이 필요한 DB 마이그레이션이 있습니다. 아래 내용을 확인해 주십시오.' : ($unrecorded_count ? '현재 실행할 변경은 없지만 기존 상태 확인 이력이 등록되지 않았습니다.' : '현재 사용 중인 기능의 DB 마이그레이션이 모두 적용되어 있습니다.');
} elseif (!$migration_result['success']) {
    $db_upgrade_msg = 'DB 마이그레이션에 실패했습니다. 오류 내용을 확인해 주십시오.';
} elseif ($action === 'record_existing') {
    $db_upgrade_msg = '기존 상태 확인 이력 ' . (int) $migration_result['skipped'] . '건을 등록했습니다. 선행 항목에 실행이 필요하거나 실패 이력이 있으면 등록을 중단합니다.';
} elseif ($migration_result['applied'] || $migration_result['skipped']) {
    $db_upgrade_msg = 'DB 마이그레이션이 완료되었습니다.';
} else {
    $db_upgrade_msg = '적용할 DB 마이그레이션이 없습니다.';
}

// 마이그레이션 이력만으로는 알 수 없는 저장 엔진·구조·연결 상태를 조회로 점검한다.
$abuse_rate_diagnosis = g5_abuse_rate_storage_diagnose();
$abuse_rate_messages = g5_abuse_rate_storage_messages($abuse_rate_diagnosis);
$abuse_rate_innodb_labels = array('YES' => '사용 가능', 'DEFAULT' => '사용 가능(기본 엔진)', 'NO' => '지원하지 않음', 'DISABLED' => '비활성화됨');

$g5['title'] = 'DB 업그레이드';
include_once('./admin.head.php');
$dbupgrade_token = get_admin_token();
?>

<div class="local_desc01 local_desc">
    <p><?php echo $db_upgrade_msg; ?></p>
    <?php if ($unrecorded_count) { ?>
    <p>실행 불필요 <?php echo (int) $unrecorded_count; ?>건: 현재 실행 조건상 건너뛰는 항목입니다. 과거 실행 성공을 의미하지 않으며, 선행 작업 후에는 결과가 달라질 수 있습니다. 기존 상태 등록은 처음부터 연속해서 실행 불필요한 항목만 기록합니다.</p>
    <?php } ?>
    <p>대기 항목에는 데이터 보정 등 조회만으로 완료를 판단할 수 없는 작업도 포함됩니다. 조회 시 DB 변경이나 이력 등록은 수행하지 않습니다.</p>
    <p>쇼핑몰 적용 보류 항목은 공통 변경만 완료된 상태입니다. 쇼핑몰 미사용 중에는 후속 공통 작업을 막지 않으며, 사용 설정을 켠 뒤 전체 실행하면 쇼핑몰 부분만 다시 처리합니다.</p>
</div>

<?php if ($shop_install_result !== null && !$shop_install_result['success']) { ?>
<div class="local_desc01 local_desc" style="color:#d00">
    <p><?php echo htmlspecialchars($shop_install_result['error'], ENT_QUOTES, 'UTF-8'); ?></p>
</div>
<?php } elseif ($migration_result['errors']) { ?>
<div class="local_desc01 local_desc" style="color:#d00">
    <?php foreach ($migration_result['errors'] as $migration_error) { ?>
    <p><?php echo htmlspecialchars($migration_error, ENT_QUOTES, 'UTF-8'); ?></p>
    <?php } ?>
</div>
<?php } ?>

<div class="dbupgrade_all_actions">
    <?php if ($unrecorded_count) { ?>
    <form class="dbupgrade_existing_action" method="post" action="<?php echo G5_ADMIN_URL; ?>/dbupgrade.php">
        <input type="hidden" name="token" value="<?php echo $dbupgrade_token; ?>">
        <input type="hidden" name="action" value="record_existing">
        <button type="submit" class="btn_submit">기존 상태 확인 이력 등록</button>
    </form>
    <?php } ?>
    <?php if (!defined('G5_USE_SHOP') || !G5_USE_SHOP) { ?>
    <form method="post" action="<?php echo G5_ADMIN_URL; ?>/dbupgrade.php" onsubmit="return confirm('쇼핑몰을 설치하시겠습니까? 설치 전 데이터베이스와 data/dbconfig.php 백업을 권장합니다.');">
        <input type="hidden" name="token" value="<?php echo $dbupgrade_token; ?>">
        <input type="hidden" name="action" value="install_shop">
        <button type="submit" class="btn_submit">쇼핑몰 설치</button>
    </form>
    <?php } ?>
    <form method="post" action="<?php echo G5_ADMIN_URL; ?>/dbupgrade.php" onsubmit="return confirm('선택한 DB 마이그레이션을 실행하시겠습니까? 실행 전 데이터베이스 백업을 권장합니다.');">
        <input type="hidden" name="token" value="<?php echo $dbupgrade_token; ?>">
        <input type="hidden" name="action" value="migrate">
        <button type="submit" name="migration_id" value="" class="btn_submit">전체 마이그레이션 실행</button>
    </form>
</div>
<form method="post" action="<?php echo G5_ADMIN_URL; ?>/dbupgrade.php" onsubmit="return confirm('선택한 DB 마이그레이션을 실행하시겠습니까? 실행 전 데이터베이스 백업을 권장합니다.');">
    <input type="hidden" name="token" value="<?php echo $dbupgrade_token; ?>">
    <input type="hidden" name="action" value="migrate">
<div class="tbl_head01 tbl_wrap">
    <table id="dbupgrade_migration_table">
        <caption>버전형 DB 마이그레이션 목록</caption>
        <thead>
            <tr>
                <th scope="col" aria-sort="none"><button type="button" class="dbupgrade_sort" data-column="0">마이그레이션 <span aria-hidden="true"></span></button></th>
                <th scope="col" class="dbupgrade_description" aria-sort="none"><button type="button" class="dbupgrade_sort" data-column="1">설명 <span aria-hidden="true"></span></button></th>
                <th scope="col" aria-sort="none"><button type="button" class="dbupgrade_sort" data-column="2">상태 <span aria-hidden="true"></span></button></th>
                <th scope="col" aria-sort="none"><button type="button" class="dbupgrade_sort" data-column="3">적용 시각 <span aria-hidden="true"></span></button></th>
                <th scope="col" aria-sort="none"><button type="button" class="dbupgrade_sort" data-column="4">실행 <span aria-hidden="true"></span></button></th>
            </tr>
        </thead>
        <tbody>
        <?php $previous_migrations_ready = true; ?>
        <?php foreach ($migration_statuses as $migration_status) { ?>
            <?php
            $current_migration_status = isset($migration_status['status']) ? $migration_status['status'] : 'error';
            $migration_succeeded = g5_migration_status_complete($current_migration_status) && !$migration_status['checksum_changed'];
            $migration_can_run = (in_array($current_migration_status, array('pending', 'failed'), true) || (g5_migration_shop_enabled() && in_array($current_migration_status, array('shop_pending', 'shop_failed'), true))) && $previous_migrations_ready;
            $migration_button_title = $current_migration_status === 'unrecorded' ? '기존 상태 확인 이력을 등록해 주십시오.' : ($current_migration_status === 'success' ? '이미 성공한 마이그레이션입니다.' : ($previous_migrations_ready ? '' : '선행 마이그레이션을 먼저 실행해야 합니다.'));
            if (!g5_migration_shop_enabled() && in_array($current_migration_status, array('shop_pending', 'shop_failed'), true)) {
                $migration_button_title = '쇼핑몰 사용 설정을 켠 뒤 실행할 수 있습니다.';
            }
            ?>
            <tr>
            <?php if (isset($migration_status['error'])) { ?>
                <td colspan="5"><?php echo htmlspecialchars($migration_status['error'], ENT_QUOTES, 'UTF-8'); ?></td>
            <?php } else { ?>
                <td><?php echo htmlspecialchars($migration_status['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="dbupgrade_description"><?php echo htmlspecialchars($migration_status['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo g5_migration_status_label($migration_status['status'], $migration_status['checksum_changed']); ?>
                    <?php if ($migration_status['error_message'] !== '') { ?><div><?php echo htmlspecialchars($migration_status['error_message'], ENT_QUOTES, 'UTF-8'); ?></div><?php } ?>
                </td>
                <td><?php echo htmlspecialchars($migration_status['applied_at'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td>
                    <button type="submit" name="migration_id" value="<?php echo htmlspecialchars($migration_status['id'], ENT_QUOTES, 'UTF-8'); ?>" class="btn_frmline"<?php echo $migration_can_run ? '' : ' disabled'; ?><?php echo $migration_button_title !== '' ? ' title="' . htmlspecialchars($migration_button_title, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>실행</button>
                </td>
            <?php } ?>
            </tr>
            <?php $previous_migrations_ready = $previous_migrations_ready && $migration_succeeded; ?>
        <?php } ?>
        </tbody>
    </table>
</div>
</form>

<section id="abuse_rate_storage" class="dbupgrade_storage">
    <h2 class="h2_frm">비밀번호 찾기 요청 제한 저장소 점검</h2>
    <div class="local_desc01 local_desc"<?php echo ($abuse_rate_diagnosis['ready'] && $abuse_rate_diagnosis['lock'] !== false) ? '' : ' style="color:#d00"'; ?>>
        <p><strong><?php echo $abuse_rate_diagnosis['ready'] ? '정상' : '사용 불가'; ?>:</strong> <?php echo htmlspecialchars($abuse_rate_messages['reason'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php if ($abuse_rate_messages['action'] !== '') { ?>
        <p><?php echo htmlspecialchars($abuse_rate_messages['action'], ENT_QUOTES, 'UTF-8'); ?></p>
        <?php } ?>
        <?php if (!$abuse_rate_diagnosis['ready']) { ?>
        <p>위 조치 후에도 같으면 서버의 PHP 오류 로그에 [g5 abuse rate]로 시작하는 기록이 있는지 확인해 주세요.</p>
        <?php } ?>
    </div>
    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption>요청 제한 저장소 점검 결과</caption>
            <tbody>
            <tr>
                <th scope="row">테이블</th>
                <td><?php echo htmlspecialchars(g5_abuse_rate_table(), ENT_QUOTES, 'UTF-8'); ?></td>
            </tr>
            <tr>
                <th scope="row">저장 엔진</th>
                <td><?php echo $abuse_rate_diagnosis['engine'] !== '' ? htmlspecialchars($abuse_rate_diagnosis['engine'], ENT_QUOTES, 'UTF-8') : '확인할 수 없음'; ?></td>
            </tr>
            <tr>
                <th scope="row">동작 방식</th>
                <td><?php echo $abuse_rate_diagnosis['mode'] === 'transaction' ? 'InnoDB 트랜잭션' : ($abuse_rate_diagnosis['mode'] === 'lock' ? '이름 잠금(GET_LOCK)' . ($abuse_rate_diagnosis['lock'] === false ? ' - 지금 잠금을 얻지 못함' : '') : '없음'); ?></td>
            </tr>
            <tr>
                <th scope="row">DB 서버 InnoDB</th>
                <td><?php echo isset($abuse_rate_innodb_labels[$abuse_rate_diagnosis['innodb']]) ? $abuse_rate_innodb_labels[$abuse_rate_diagnosis['innodb']] : '확인할 수 없음'; ?></td>
            </tr>
            <?php if (!empty($abuse_rate_diagnosis['row'])) { ?>
            <tr>
                <th scope="row">컬럼 구조</th>
                <td>ar_key <?php echo htmlspecialchars($abuse_rate_diagnosis['row']['key_type'] . ($abuse_rate_diagnosis['row']['key_null'] === 'NO' ? ' NOT NULL' : ' NULL'), ENT_QUOTES, 'UTF-8'); ?>,
                    ar_next <?php echo htmlspecialchars($abuse_rate_diagnosis['row']['next_type'] . ($abuse_rate_diagnosis['row']['next_null'] === 'NO' ? ' NOT NULL' : ' NULL'), ENT_QUOTES, 'UTF-8'); ?>,
                    기본키 <?php echo htmlspecialchars($abuse_rate_diagnosis['row']['primary_column'], ENT_QUOTES, 'UTF-8'); ?>
                    (필요: ar_key char(64) NOT NULL 기본키, ar_next bigint unsigned NOT NULL)</td>
            </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</section>

<style>
.dbupgrade_all_actions {display:flex;width:100%;margin:0 0 20px;justify-content:flex-end;gap:5px}
.dbupgrade_all_actions form {margin:0}
.dbupgrade_all_actions .dbupgrade_existing_action {margin-right:auto}
.dbupgrade_all_actions .btn_submit {display:inline-block;float:none;position:static;width:auto;height:30px;margin:0;padding:0 15px;border:0}
.dbupgrade_sort {width:100%; border:0; background:transparent; color:inherit; font:inherit; cursor:pointer}
#dbupgrade_migration_table td.dbupgrade_description {text-align:left}
#dbupgrade_migration_table th.dbupgrade_description, #dbupgrade_migration_table th.dbupgrade_description .dbupgrade_sort {text-align:center}
.btn_frmline:disabled {background:#b7b7b7;color:#fff;cursor:not-allowed}
</style>
<script>
(function () {
    var table = document.getElementById('dbupgrade_migration_table');
    if (!table || !table.tBodies.length) {
        return;
    }

    var buttons = table.querySelectorAll('.dbupgrade_sort');
    var tbody = table.tBodies[0];

    function getCellText(row, column) {
        return row.cells[column] ? row.cells[column].textContent.replace(/^\s+|\s+$/g, '') : '';
    }

    function sortRows(button, initialAscending) {
        var column = parseInt(button.getAttribute('data-column'), 10);
        var header = button.parentNode;
        var ascending = typeof initialAscending === 'boolean' ? initialAscending : header.getAttribute('aria-sort') !== 'ascending';
        var rows = Array.prototype.slice.call(tbody.rows);

        rows.sort(function (left, right) {
            var leftText = getCellText(left, column);
            var rightText = getCellText(right, column);
            var compared = leftText.localeCompare(rightText, undefined, {numeric: true, sensitivity: 'base'});

            return ascending ? compared : -compared;
        });

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].parentNode.setAttribute('aria-sort', 'none');
            buttons[i].getElementsByTagName('span')[0].textContent = '';
        }
        header.setAttribute('aria-sort', ascending ? 'ascending' : 'descending');
        button.getElementsByTagName('span')[0].textContent = ascending ? '▲' : '▼';

        for (var j = 0; j < rows.length; j++) {
            tbody.appendChild(rows[j]);
        }
    }

    for (var i = 0; i < buttons.length; i++) {
        buttons[i].onclick = function () {
            sortRows(this);
        };
    }

    if (buttons.length) {
        sortRows(buttons[0], false);
    }
}());
</script>

<?php
include_once('./admin.tail.php');
