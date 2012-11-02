<?
$sub_menu = "300200";
include_once("./_common.php");

sql_query(" ALTER TABLE $g4[group_member_table] CHANGE `gm_id` `gm_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT ", false);

if ($w == "") 
{
    auth_check($auth[$sub_menu], "w");

    $mb = get_member($mb_id);
    if (!$mb[mb_id]) { 
        alert("존재하지 않는 회원입니다."); 
    }

    $gr = get_group($gr_id);
    if (!$gr[gr_id]) {
        alert("존재하지 않는 그룹입니다."); 
    }

    $sql = " select count(*) as cnt 
               from $g4[group_member_table]
              where gr_id = '$gr_id'
                and mb_id = '$mb_id' ";
    $row = sql_fetch($sql);
    if ($row[cnt]) {
        alert("이미 등록되어 있는 자료입니다.");
    } 
    else 
    {
        check_token();

        $sql = " insert into $g4[group_member_table]
                    set gr_id       = '$_POST[gr_id]',
                        mb_id       = '$_POST[mb_id]',
                        gm_datetime = '$g4[time_ymdhis]' ";
        sql_query($sql);
    }
} 
else if ($w == 'd' || $w == 'listdelete') 
{
    auth_check($auth[$sub_menu], "d");
    $sql = " select * from $g4[group_member_table] where gm_id = '$_POST[gm_id]' ";
    $gm = sql_fetch($sql);
    if (!$gm[gm_id]) {
        alert("존재하지 않는 자료입니다.");
    }

    check_token();

    $gr_id = $gm[gr_id];
    $mb_id = $gm[mb_id];

    $sql = " delete from $g4[group_member_table] where gm_id = '$_POST[gm_id]' ";
    sql_query($sql);
}

if ($w == 'listdelete')
    goto_url("./boardgroupmember_list.php?gr_id=$gr_id");
else
    goto_url("./boardgroupmember_form.php?mb_id=$mb_id");
?>
