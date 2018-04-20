<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

$me_id = (int)$_REQUEST['me_id'];

if ($kind == 'recv')
{
    $t = '받은';
    $unkind = 'send';

    $sql = " update {$g5['memo_table']}
                set me_read_datetime = '".G5_TIME_YMDHIS."'
                where me_id = '$me_id'
                and me_recv_mb_id = '{$member['mb_id']}'
                and me_read_datetime = '0000-00-00 00:00:00' ";
    sql_query($sql);
}
else if ($kind == 'send')
{
    $t = '보낸';
    $unkind = 'recv';
}
else
{
    alert($kind.' 값을 넘겨주세요.');
}

$g5['title'] = $t.' 쪽지 보기';
include_once(G5_PATH.'/head.sub.php');

$sql = " select * from {$g5['memo_table']}
            where me_id = '$me_id'
            and me_{$kind}_mb_id = '{$member['mb_id']}' ";
$memo = sql_fetch($sql);

// 이전 쪽지
$sql = " select * from {$g5['memo_table']}
            where me_id > '{$me_id}'
            and me_{$kind}_mb_id = '{$member['mb_id']}'
            order by me_id asc
            limit 1 ";
$prev = sql_fetch($sql);
if ($prev['me_id'])
    $prev_link = './memo_view.php?kind='.$kind.'&amp;me_id='.$prev['me_id'];
else
    //$prev_link = 'javascript:alert(\'쪽지의 처음입니다.\');';
    $prev_link = '';


// 다음 쪽지
$sql = " select * from {$g5[memo_table]}
            where me_id < '{$me_id}'
            and me_{$kind}_mb_id = '{$member[mb_id]}'
            order by me_id desc
            limit 1 ";
$next = sql_fetch($sql);
if ($next['me_id'])
    $next_link = './memo_view.php?kind='.$kind.'&amp;me_id='.$next['me_id'];
else
    //$next_link = 'javascript:alert(\'쪽지의 마지막입니다.\');';
    $next_link = '';

$mb = get_member($memo['me_'.$unkind.'_mb_id']);

$list_link = './memo.php?kind='.$kind;

if(isset($page) && $page){
    $prev_link .= $prev_link ? '&amp;page='.(int) $page : '';
    $next_link .= $next_link ? '&amp;page='.(int) $page : '';
    $list_link .= '&amp;page='.(int) $page;
}

include_once($member_skin_path.'/memo_view.skin.php');

include_once(G5_PATH.'/tail.sub.php');
?>
