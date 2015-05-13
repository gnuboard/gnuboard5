<?php
$sub_menu = "200820";
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], 'd');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$year = preg_replace('/[^0-9]/', '', $_POST['year']);
$month = preg_replace('/[^0-9]/', '', $_POST['month']);
$method = $_POST['method'];
$pass = trim($_POST['pass']);

if(!$pass)
    alert('관리자 비밀번호를 입력해 주십시오.');

// 관리자 비밀번호 비교
$admin = get_admin('super');
if(!check_password($pass, $admin['mb_password']))
    alert('관리자 비밀번호가 일치하지 않습니다.');

if(!$year)
    alert('년도를 선택해 주십시오.');

if(!$month)
    alert('월을 선택해 주십시오.');

// 로그삭제 query
$del_date = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT);
switch($method) {
    case 'before':
        $sql_common = " where substring(vi_date, 1, 7) < '$del_date' ";
        break;
    case 'specific':
        $sql_common = " where substring(vi_date, 1, 7) = '$del_date' ";
        break;
    default:
        alert('올바른 방법으로 이용해 주십시오.');
        break;
}

// 총 로그수
$sql = " select count(*) as cnt from {$g5['visit_table']} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

// 로그삭제
$sql = " delete from {$g5['visit_table']} $sql_common ";
sql_query($sql);

// 삭제 후 총 로그수
$sql = " select count(*) as cnt from {$g5['visit_table']} ";
$row = sql_fetch($sql);
$total_count2 = $row['cnt'];

alert('총 '.number_format($total_count).'건 중 '.number_format($total_count - $total_count2).'건 삭제 완료', './visit_delete.php');
?>