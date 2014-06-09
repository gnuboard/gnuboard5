<?php
include_once('./_common.php');

if ($is_guest)
    alert_close('회원만 이용하실 수 있습니다.');

$g5['title'] = '내 쪽지함';
include_once(G5_PATH.'/head.sub.php');

if (!$kind) $kind = 'recv';

if ($kind == 'recv')
    $unkind = 'send';
else if ($kind == 'send')
    $unkind = 'recv';
else
    alert(''.$kind .'값을 넘겨주세요.');

$sql = " select count(*) as cnt from {$g5['memo_table']} where me_{$kind}_mb_id = '{$member['mb_id']}' ";
$row = sql_fetch($sql);
$total_count = number_format($row['cnt']);

if ($kind == 'recv')
{
    $kind_title = '받은';
    $recv_img = 'on';
    $send_img = 'off';
}
else
{
    $kind_title = '보낸';
    $recv_img = 'off';
    $send_img = 'on';
}

$list = array();

$sql = " select a.*, b.mb_id, b.mb_nick, b.mb_email, b.mb_homepage
            from {$g5['memo_table']} a
            left join {$g5['member_table']} b on (a.me_{$unkind}_mb_id = b.mb_id)
            where a.me_{$kind}_mb_id = '{$member['mb_id']}'
            order by a.me_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $list[$i] = $row;

    $mb_id = $row["me_{$unkind}_mb_id"];

    if ($row['mb_nick'])
        $mb_nick = $row['mb_nick'];
    else
        $mb_nick = '정보없음';

    $name = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);

    if (substr($row['me_read_datetime'],0,1) == 0)
        $read_datetime = '아직 읽지 않음';
    else
        $read_datetime = substr($row['me_read_datetime'],2,14);

    $send_datetime = substr($row['me_send_datetime'],2,14);

    $list[$i]['name'] = $name;
    $list[$i]['send_datetime'] = $send_datetime;
    $list[$i]['read_datetime'] = $read_datetime;
    $list[$i]['view_href'] = './memo_view.php?me_id='.$row['me_id'].'&amp;kind='.$kind;
    $list[$i]['del_href'] = './memo_delete.php?me_id='.$row['me_id'].'&amp;kind='.$kind;
}

include_once($member_skin_path.'/memo.skin.php');

include_once(G5_PATH.'/tail.sub.php');
?>
