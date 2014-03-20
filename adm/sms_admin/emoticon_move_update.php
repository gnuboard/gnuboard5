<?php
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

if(!count($_POST['chk_fg_no']))
    alert('이모티콘을 이동할 그룹을 한개 이상 선택해 주십시오.', $url);

$sql = "select * from {$g5['sms5_form_table']} where fo_no in ($fo_no_list) order by fo_no desc ";
$result = sql_query($sql);
$save = array();
for ($kk=0;$row = sql_fetch_array($result);$kk++)
{
    $fo_no = $row['fo_no'];
    for ($i=0; $i<count($_POST['chk_fg_no']); $i++)
    {
        $fg_no = $_POST['chk_fg_no'][$i];
        if( !$fg_no ) continue;
        $group = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$fg_no'");
        $sql = " insert into {$g5['sms5_form_table']}
                    set fg_no='$fg_no',
                        fg_member='".$group['fg_member']."',
                        fo_name='".addslashes($row['fo_name'])."',
                        fo_content='".addslashes($row['fo_content'])."',
                        fo_datetime='".G5_TIME_YMDHIS."' ";
        sql_query($sql);
        sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + 1 where fg_no='$fg_no'");
    }
    $save[$kk]['fo_no'] = $row['fo_no'];
    $save[$kk]['fg_no'] = $row['fg_no'];
}

if ($sw == 'move')
{
    foreach ($save as $v)
    {
        if( empty($v['fo_no']) ) continue;
        sql_query(" delete from {$g5['sms5_form_table']} where fo_no = '{$v['fo_no']}' ");
        sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count - 1 where fg_no='{$v['fg_no']}'");
    }
}

$msg = '해당 이모티콘을 선택한 그룹으로 이동 하였습니다.';
$opener_href = './form_list.php?page='.$page;

echo <<<HEREDOC
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script>
alert("$msg");
opener.document.location.href = "$opener_href";
window.close();
</script>
<noscript>
<p>
    "$msg"
</p>
<a href="$opener_href">돌아가기</a>
</noscript>
HEREDOC;
?>