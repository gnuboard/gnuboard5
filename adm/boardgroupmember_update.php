<?php
$sub_menu = "300200";
include_once('./_common.php');

sql_query(" ALTER TABLE {$g5['group_member_table']} CHANGE `gm_id` `gm_id` INT( 11 ) DEFAULT '0' NOT NULL AUTO_INCREMENT ", false);

if ($w == '')
{
    auth_check($auth[$sub_menu], 'w');

    $mb = get_member($mb_id);
    if (!$mb['mb_id']) {
        alert('존재하지 않는 회원입니다.');
    }

    $gr = get_group($gr_id);
    if (!$gr['gr_id']) {
        alert('존재하지 않는 그룹입니다.');
    }

    $sql = " select count(*) as cnt
                from {$g5['group_member_table']}
                where gr_id = '{$gr_id}'
                and mb_id = '{$mb_id}' ";
    $row = sql_fetch($sql);
    if ($row['cnt']) {
        alert('이미 등록되어 있는 자료입니다.');
    }
    else
    {
        check_token();

        $sql = " insert into {$g5['group_member_table']}
                    set gr_id = '{$_POST['gr_id']}',
                         mb_id = '{$_POST['mb_id']}',
                         gm_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql);
    }
}
else if ($w == 'd' || $w == 'ld')
{
    auth_check($auth[$sub_menu], 'd');

    $count = count($_POST['chk']);
    if(!$count)
        alert('삭제할 목록을 하나이상 선택해 주세요.');

    check_token();

    for($i=0; $i<$count; $i++) {
        $gm_id = $_POST['chk'][$i];
        $sql = " select * from {$g5['group_member_table']} where gm_id = '$gm_id' ";
        $gm = sql_fetch($sql);
        if (!$gm['gm_id']) {
            if($count == 1)
                alert('존재하지 않는 자료입니다.');
            else
                continue;
        }

        $sql = " delete from {$g5['group_member_table']} where gm_id = '$gm_id' ";
        sql_query($sql);
    }
}

if ($w == 'ld')
    goto_url('./boardgroupmember_list.php?gr_id='.$gr_id);
else
    goto_url('./boardgroupmember_form.php?mb_id='.$mb_id);
?>
