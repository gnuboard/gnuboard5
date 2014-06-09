<?php
include_once('./_common.php');

if ($w == 's') {
    $qstr = 'bo_table='.$bo_table.'&amp;sfl='.$sfl.'&amp;stx='.$stx.'&amp;sop='.$sop.'&amp;wr_id='.$wr_id.'&amp;page='.$page;

    $wr = get_write($write_table, $wr_id);

    if (sql_password($wr_password) != $wr['wr_password'])
        alert('비밀번호가 틀립니다.');

    // 세션에 아래 정보를 저장. 하위번호는 비밀번호없이 보아야 하기 때문임.
    //$ss_name = 'ss_secret.'_'.$bo_table.'_'.$wr_id';
    $ss_name = 'ss_secret_'.$bo_table.'_'.$wr['wr_num'];
    //set_session("ss_secret", "$bo_table|$wr[wr_num]");
    set_session($ss_name, TRUE);

} else
    alert('w 값이 제대로 넘어오지 않았습니다.');

goto_url('./board.php?'.$qstr);
?>
