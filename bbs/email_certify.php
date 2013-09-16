<?php
include_once('./_common.php');

$sql = " select mb_id, mb_email, mb_datetime from {$g5['member_table']} where mb_id = '{$mb_id}' ";
$row = sql_fetch($sql);
if (!$row['mb_id'])
    alert('존재하는 회원이 아닙니다.', G5_URL);

if ($mb_md5)
{
    $tmp_md5 = md5($row['mb_id'].$row['mb_email'].$row['mb_datetime']);
    if ($mb_md5 == $tmp_md5)
    {
        sql_query(" update {$g5['member_table']} set mb_email_certify = '".G5_TIME_YMDHIS."' where mb_id = '{$mb_id}' ");

        alert("메일인증 처리를 완료 하였습니다.\\n\\n지금부터 {$mb_id} 아이디로 로그인 가능합니다.", G5_URL);
    }
}

alert('제대로 된 값이 넘어오지 않았습니다.', G5_URL);
?>