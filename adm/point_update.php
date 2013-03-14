<?
$sub_menu = "200200";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

check_token();

if ($member[mb_password] != sql_password($_POST['admin_password'])) {
    alert("패스워드가 다릅니다.");
}

$mb_id      = $_POST['mb_id'];
$po_point   = $_POST['po_point'];
$po_content = $_POST['po_content'];

$mb = get_member($mb_id);

if (!$mb[mb_id])
    alert("존재하는 회원아이디가 아닙니다.", "./point_list.php?$qstr"); 

if (($po_point < 0) && ($po_point * (-1) > $mb[mb_point]))
    alert("포인트를 깎는 경우 현재 포인트보다 작으면 안됩니다.", "./point_list.php?$qstr");

insert_point($mb_id, $po_point, $po_content, '@passive', $mb_id, $member[mb_id]."-".uniqid(""));

goto_url("./point_list.php?$qstr");
?>
