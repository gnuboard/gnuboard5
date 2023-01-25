<?php
$sub_menu = "900800";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$post_chk_bg_no = isset($_POST['chk_bg_no']) ? $_POST['chk_bg_no'] : array();

if(!count($post_chk_bg_no))
    alert('번호를 '.$act.'할 그룹을 한개 이상 선택해 주십시오.', $url);

$bk_no_list = isset($_POST['bk_no_list']) ? preg_replace('/[^a-zA-Z0-9\, ]/', '', $_POST['bk_no_list']) : '';

$sql = "select * from {$g5['sms5_book_table']} where bk_no in ($bk_no_list) order by bk_no desc ";
$result = sql_query($sql);
$save = array();
$save_group = array();

for ($kk=0;$row = sql_fetch_array($result);$kk++)
{
    $bk_no = $row['bk_no'];
    for ($i=0; $i<count($post_chk_bg_no); $i++)
    {
        $bg_no = (int) $post_chk_bg_no[$i];
        if( !$bg_no ) continue;

        $sql = " insert into {$g5['sms5_book_table']}
                    set bg_no='$bg_no',
                        mb_id='{$row['mb_id']}',
                        bk_name='".addslashes($row['bk_name'])."',
                        bk_hp='{$row['bk_hp']}',
                        bk_receipt='{$row['bk_receipt']}',
                        bk_datetime='".G5_TIME_YMDHIS."' ";
        sql_query($sql);
        if( !in_array($bg_no, $save_group) ){
            array_push( $save_group, $bg_no );
        }
    }
    $save[$kk]['bg_no'] = $row['bg_no'];
    $save[$kk]['bk_no'] = $row['bk_no'];
    $save[$kk]['mb_id'] = $row['mb_id'];
    $save[$kk]['bk_receipt'] = $row['bk_receipt'];
}

if ($sw == 'move')
{
    foreach ($save as $v)
    {
        if( empty($v['bk_no']) ) continue;
        sql_query(" delete from {$g5['sms5_book_table']} where bk_no = '{$v['bk_no']}' ");
        if( !in_array($v['bg_no'], $save_group) ){
            array_push( $save_group, $v['bg_no'] );
        }
    }
}

if( count($save_group) ){ //그룹테이블 업데이트
    $save_group = array_unique( $save_group );
    foreach( $save_group as $v )
    {
        if( empty($v) ) continue;
        $bg_count = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where bg_no='$v' ");
        $bg_receipt = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where bg_no='$v' and bk_receipt >= 1 ");
        $bg_reject = (int)$bg_count['cnt'] - (int)$bg_receipt['cnt'];
        $bg_member = sql_fetch("select count(*) as cnt from {$g5['sms5_book_table']} where bg_no='$v' and mb_id <> '' ");
        $bg_nomember = (int)$bg_count['cnt'] - (int)$bg_member['cnt'];
        $sql = "update {$g5['sms5_book_group_table']} set bg_count = {$bg_count['cnt']}, bg_receipt = {$bg_receipt['cnt']}, bg_reject = {$bg_reject}, bg_member = {$bg_member['cnt']}, bg_nomember = {$bg_nomember} where bg_no='$v' ";
        sql_query($sql);
    }
}

$msg = '해당 번호를 선택한 그룹으로 '.$act.' 하였습니다.';
$opener_href = './num_book.php?page='.$page;
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script>
alert("<?php echo $msg; ?>");
opener.document.location.href = "<?php echo $opener_href; ?>";
window.close();
</script>
<noscript>
<p>
    <?php echo $msg; ?>
</p>
<a href="<?php echo $opener_href; ?>">돌아가기</a>
</noscript>