<?php
include_once('./_common.php');

if (!$is_member)
    alert('회원만 이용하실 수 있습니다.');

$me_id = isset($_REQUEST['me_id']) ? (int) $_REQUEST['me_id'] : 0;

if ($kind == 'recv')
{
    $t = '받은';
    $unkind = 'send';

    $sql = " update {$g5['memo_table']}
                set me_read_datetime = '".G5_TIME_YMDHIS."'
                where (me_id = '$me_id' or me_send_id = '$me_id' )
                and me_recv_mb_id = '{$member['mb_id']}'
                and me_read_datetime = '0000-00-00 00:00:00' ";
    sql_query($sql);

    $sql = " update `{$g5['member_table']}` set mb_memo_cnt = '".get_memo_not_read($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
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

$sql = " select * from {$g5['memo_table']}
            where me_id = '$me_id'
            and me_{$kind}_mb_id = '{$member['mb_id']}' ";
$memo = sql_fetch($sql);

set_session('ss_memo_delete_token', $token = uniqid(time()));
$del_link = 'memo_delete.php?me_id='.$memo['me_id'].'&amp;token='.$token.'&amp;kind='.$kind;

$g5['title'] = $t.' 쪽지 보기';
include_once(G5_PATH.'/head.sub.php');

// 이전 쪽지
$sql = " select me.*, a.rownum from `{$g5['memo_table']}` as me inner join ( select me_id , (@rownum:=@rownum+1) as rownum from `{$g5['memo_table']}` as memo, (select @rownum:=0) tmp where me_{$kind}_mb_id = '{$member['mb_id']}' and memo.me_type = '$kind' order by me_id desc ) as a on a.me_id = me.me_id where me.me_id < '$me_id' and me.me_{$kind}_mb_id = '{$member['mb_id']}' and me.me_type = '$kind' order by me.me_id desc limit 1 ";

$prev = sql_fetch($sql);
if (isset($prev['me_id']) && $prev['me_id']) {
    $prev_link = './memo_view.php?kind='.$kind.'&amp;me_id='.$prev['me_id'];
    $prev['page']  = ceil( (int)$prev['rownum'] / $config['cf_page_rows']);  // 이동할 페이지 계산
    if( (int)$prev['page'] > 0 ) $prev_link .= "&amp;page=".$prev['page'];
} else {
    $prev_link = '';
}

// 다음 쪽지
$sql = " select me.*, a.rownum from `{$g5['memo_table']}` as me inner join ( select me_id , (@rownum:=@rownum+1) as rownum from `{$g5['memo_table']}` as memo, (select @rownum:=0) tmp where me_{$kind}_mb_id = '{$member['mb_id']}' and memo.me_type = '$kind' order by me_id asc ) as a on a.me_id = me.me_id where me.me_id > '$me_id' and me.me_{$kind}_mb_id = '{$member['mb_id']}' and me.me_type = '$kind' order by me.me_id asc limit 1 ";

$next = sql_fetch($sql);
if (isset($next['me_id']) && $next['me_id']) {
    $next_link = './memo_view.php?kind='.$kind.'&amp;me_id='.$next['me_id'];
    $next['page']  = ceil( (int)$next['rownum'] / $config['cf_page_rows']);  // 이동할 페이지 계산
    if( (int)$next['page'] > 0 ) $next_link .= "&amp;page=".$next['page'];
} else {
    $next_link = '';
}

$mb = get_member($memo['me_'.$unkind.'_mb_id']);

$list_link = './memo.php?kind='.$kind;

if(isset($page) && $page){
    $prev_link .= $prev_link ? '&amp;page='.(int) $page : '';
    $next_link .= $next_link ? '&amp;page='.(int) $page : '';
    $list_link .= '&amp;page='.(int) $page;
}

include_once($member_skin_path.'/memo_view.skin.php');

include_once(G5_PATH.'/tail.sub.php');