<?php
define('SUBSCRIPTION_INSTALL_PAGE', 1);
$sub_menu = "600000";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = "정기결제 프로그램 설치";

$setup = (isset($_POST['setup']) && $_POST['setup']) ? 1 : 0;

include_once(G5_ADMIN_PATH.'/admin.head.php');

?>
<form name="hidden_form" method="post" action="<?php echo get_text($_SERVER['SCRIPT_NAME']); ?>">
<input type="hidden" name="setup">
</form>
<?php
//정기결제 설정 정보 테이블이 있는지 검사한다.
if( isset($g5['subscription_cart_table']) && sql_query(" DESCRIBE {$g5['subscription_cart_table']} ", false)) {
    if(!$setup){
        echo '<script>
            var answer = confirm("이미 subscription가 설치되어 있습니다.새로 설치 할 경우 DB 자료가 망실됩니다. 새로 설치하시겠습니까?");
            if (answer){
                document.hidden_form.setup.value = "1";
                document.hidden_form.submit();
            } else {
                history.back();
            }
            </script>
        ';
        exit;
    }
}
?>

<div id="subscription_install">
    <ol>
        <li>정기결제 프로그램 설치가 시작되었습니다.</li>
        <li id="subscription_job_01">전체 테이블 생성중</li>
        <li id="subscription_job_02">DB설정 중</li>
        <li id="subscription_job_03"></li>
    </ol>

    <p><button type="button" id="subscription_btn_next" disabled class="btn_frmline" onclick="location.href='configform.php';">정기결제 기본설정</button></p>

</div>
<?php
flush(); usleep(50000);

// 테이블 생성 ------------------------------------
$file = implode("", file("./subscription.sql"));
eval("\$file = \"$file\";");

$f = explode(";", $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == "") continue;
    if ($f[$i] = get_db_create_replace($f[$i])) {
        
        echo $f[$i];
        
        echo "<br>";
        
        sql_query($f[$i]) or die(sql_error_info());
    }
}
// 테이블 생성 ------------------------------------

echo "<script>document.getElementById('subscription_job_01').innerHTML='전체 테이블 생성 완료';</script>";
flush(); usleep(50000);

//-------------------------------------------------------------------------------------------------
// config 테이블 설정
// $sql = " insert into {$g5['subscription_book_group_table']} set bg_name='미분류'";
// sql_query($sql) or die(sql_error_info() . "<p>" . $sql);

echo "<script>document.getElementById('subscription_job_02').innerHTML='DB설정 완료';</script>";
flush(); usleep(50000);
//-------------------------------------------------------------------------------------------------

echo "<script>document.getElementById('subscription_job_03').innerHTML='정기결제 기본 설정 변경 후 사용하세요.';</script>";
flush(); usleep(50000);

$sql = " select * from {$g5['g5_shop_category_table']} limit 1 ";
$ca = sql_fetch($sql);

if (!isset($ca['ca_class_num'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_category_table']}` ADD `ca_class_num` TINYINT NOT NULL DEFAULT '0' after `ca_id`, ADD INDEX (`ca_class_num`) ", false);
}

$sql = " select * from {$g5['g5_shop_item_table']} limit 1 ";
$ca = sql_fetch($sql);

if (!isset($ca['it_class_num'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_item_table']}` ADD `it_class_num` TINYINT NOT NULL DEFAULT '0' after `it_id`, ADD INDEX (`it_class_num`) ", false);
}
?>

<script>document.getElementById('subscription_btn_next').disabled = false;</script>
<script>document.getElementById('subscription_btn_next').focus();</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');