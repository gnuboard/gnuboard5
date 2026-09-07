<?php
$sub_menu = '100410';
include_once('./_common.php');
include_once(G5_LIB_PATH . '/migration.lib.php');
include_once(G5_LIB_PATH . '/shop_install.lib.php');

auth_check_menu($auth, $sub_menu, 'r');

if ($is_admin != 'super') {
    alert('최고관리자만 접근 가능합니다.');
}

$is_execute = isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST';
$migration_result = array('success' => true, 'applied' => 0, 'skipped' => 0, 'errors' => array());
$shop_install_result = null;

if ($is_execute) {
    check_demo();
    check_admin_token();
    $action = isset($_POST['action']) ? trim($_POST['action']) : 'migrate';
    if ($action === 'install_shop') {
        $shop_install_result = g5_shop_install_run();
    } else {
        $migration_id = isset($_POST['migration_id']) ? trim($_POST['migration_id']) : '';
        $migration_result = g5_migration_run($migration_id);
        $migration_result = run_replace('admin_dbupgrade_result', $migration_result);
    }
}

$migration_statuses = g5_migration_status();
$pending_count = 0;
foreach ($migration_statuses as $migration_status) {
    if (isset($migration_status['error']) || $migration_status['status'] !== 'success' || $migration_status['checksum_changed']) {
        $pending_count++;
    }
}

if ($shop_install_result !== null && $shop_install_result['success']) {
    alert('쇼핑몰 설치가 완료되었습니다.', G5_ADMIN_URL . '/dbupgrade.php');
} elseif ($shop_install_result !== null) {
    $db_upgrade_msg = '쇼핑몰 설치에 실패했습니다. 오류 내용을 확인해 주십시오.';
} elseif (!$is_execute) {
    $db_upgrade_msg = $pending_count ? '적용하지 않은 DB 마이그레이션이 있습니다. 아래 내용을 확인한 뒤 실행해 주십시오.' : 'DB 마이그레이션이 모두 적용되어 있습니다.';
} elseif (!$migration_result['success']) {
    $db_upgrade_msg = 'DB 마이그레이션에 실패했습니다. 오류 내용을 확인해 주십시오.';
} elseif ($migration_result['applied'] || $migration_result['skipped']) {
    $db_upgrade_msg = 'DB 마이그레이션이 완료되었습니다.';
} else {
    $db_upgrade_msg = '적용할 DB 마이그레이션이 없습니다.';
}

$g5['title'] = 'DB 업그레이드';
include_once('./admin.head.php');
$dbupgrade_token = get_admin_token();
?>

<div class="local_desc01 local_desc">
    <p><?php echo $db_upgrade_msg; ?></p>
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
            $migration_succeeded = $current_migration_status === 'success' && !$migration_status['checksum_changed'];
            $migration_can_run = $current_migration_status !== 'error' && $current_migration_status !== 'success' && $previous_migrations_ready;
            $migration_button_title = $current_migration_status === 'success' ? '이미 성공한 마이그레이션입니다.' : ($previous_migrations_ready ? '' : '선행 마이그레이션을 먼저 실행해야 합니다.');
            ?>
            <tr>
            <?php if (isset($migration_status['error'])) { ?>
                <td colspan="5"><?php echo htmlspecialchars($migration_status['error'], ENT_QUOTES, 'UTF-8'); ?></td>
            <?php } else { ?>
                <td><?php echo htmlspecialchars($migration_status['id'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td class="dbupgrade_description"><?php echo htmlspecialchars($migration_status['description'], ENT_QUOTES, 'UTF-8'); ?></td>
                <td><?php echo g5_migration_status_label($migration_status['status'], $migration_status['checksum_changed']); ?></td>
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

<style>
.dbupgrade_all_actions {display:flex;width:100%;margin:0 0 20px;justify-content:flex-end;gap:5px}
.dbupgrade_all_actions form {margin:0}
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
