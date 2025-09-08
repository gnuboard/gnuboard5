<?php
$sub_menu = '100320';
include_once('./_common.php');
include_once(G5_KAKAO5_PATH.'/kakao5.lib.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '알림톡 프리셋 관리';
include_once(G5_ADMIN_PATH.'/admin.head.php');

// 영카트(쇼핑몰) 사용 여부 확인
$has_shop = defined('G5_USE_SHOP') && G5_USE_SHOP;

// SQL 조건
$sql_search = $has_shop ? 'where (1)' : "WHERE kp_category <> '쇼핑몰'";

if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default:
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if ($sst) {
    $sql_order = " order by {$sst} {$sod} ";
} else {
    $sql_order = " order by kp_id asc ";
}

// 프리셋 테이블 조회
$result = sql_query("SELECT * FROM {$g5['kakao5_preset_table']} {$sql_search} {$sql_order}");
?>

<?php if (!sql_query("DESC {$g5['kakao5_preset_table']}", false)) { ?>
    <h2 class="h2_frm">카카오톡 프리셋 DB가 설치되지 않았습니다.</h2>
    <div class="local_desc01 local_desc">
        <p>카카오톡 프리셋 DB가 설치되지 않아 프리셋을 사용할 수 없습니다.
        <br><a href="<?php echo G5_ADMIN_URL;?>/dbupgrade.php" class="btn_frmline">DB 업그레이드</a>를 진행해주세요.</p>
    </div>
    <?php include_once(G5_ADMIN_PATH.'/admin.tail.php'); exit; ?>
<?php } ?>

<?php if ($config['cf_kakaotalk_use'] == 'popbill') { // 팝빌 사용
        include_once(G5_ADMIN_PATH.'/alimtalkpreset_popbill.php');
} else { ?>
    <h2 class="h2_frm">카카오톡 발송 서비스를 사용할 수 없습니다.</h2>
    <div class="local_desc01 local_desc">
        <p>카카오톡 을 사용하지 않고 있기 때문에, 카카오톡 전송을 할 수 없습니다.
        <br>카카오톡 사용 설정은 <a href="<?php echo G5_ADMIN_URL;?>/config_form.php#anc_cf_mail" class="btn_frmline">환경설정 &gt; 기본환경설정 &gt; 기본알림환경</a> 에서 카카오톡 사용을 변경해 주셔야 사용하실수 있습니다.</p>
    </div>
<?php } ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');