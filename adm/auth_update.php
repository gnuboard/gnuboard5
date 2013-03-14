<?
$sub_menu = "100200";
include_once("./_common.php");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

$mb = get_member($mb_id);
if (!$mb[mb_id])
    alert("존재하는 회원아이디가 아닙니다."); 

check_token();

if ($member[mb_password] != sql_password($_POST['admin_password'])) {
    alert("패스워드가 다릅니다.");
}

$sql = " insert into $g4[auth_table] 
            set mb_id   = '$_POST[mb_id]',
                au_menu = '$_POST[au_menu]',
                au_auth = '$_POST[r],$_POST[$w],$_POST[$d]' ";
$result = sql_query($sql, FALSE);
if (!$result) {
    $sql = " update $g4[auth_table] 
                set au_auth = '$_POST[r],$_POST[$w],_POST[$d]'
              where mb_id   = '$_POST[mb_id]'
                and au_menu = '$_POST[au_menu]' ";
    sql_query($sql);
}

//sql_query(" OPTIMIZE TABLE `$g4[auth_table]` ");

goto_url("./auth_list.php?$qstr");
?>
