<?php
include_once('./_common.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

$po = sql_fetch(" select * from {$g5['poll_table']} where po_id = '{$po_id}' ");
if (!$po['po_id'])
    alert('설문조사 정보가 없습니다.');

if ($member['mb_level'] < $po['po_level'])
    alert('권한 '.$po['po_level'].' 이상의 회원만 결과를 보실 수 있습니다.');

$g5['title'] = '설문조사 결과';

$po_subject = $po['po_subject'];

$max = 1;
$total_po_cnt = 0;
for ($i=1; $i<=9; $i++) {
    $poll = $po['po_poll'.$i];
    if ($poll == '') break;

    $count = $po['po_cnt'.$i];
    $total_po_cnt += $count;

    if ($count > $max)
        $max = $count;
}
$nf_total_po_cnt = number_format($total_po_cnt);

$list = array();

for ($i=1; $i<=9; $i++) {
    $poll = $po['po_poll'.$i];
    if ($poll == '') { break; }

    $list[$i]['content'] = $poll;
    $list[$i]['cnt'] = $po['po_cnt'.$i];
    if ($total_po_cnt > 0)
        $list[$i]['rate'] = ($list[$i]['cnt'] / $total_po_cnt) * 100;

    $bar = (int)($list[$i]['cnt'] / $max * 100);

    $list[$i]['bar'] = $bar;
    $list[$i]['num'] = $i;
}

$list2 = array();

// 기타의견 리스트
$sql = " select a.*, b.mb_open
           from {$g5['poll_etc_table']} a
           left join {$g5['member_table']} b on (a.mb_id = b.mb_id)
          where po_id = '{$po_id}' order by pc_id desc ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++) {
    $list2[$i]['pc_name']  = get_text($row['pc_name']);
    $list2[$i]['name']     = get_sideview($row['mb_id'], get_text(cut_str($row['pc_name'],10)), '', '', $row['mb_open']);
    $list2[$i]['idea']     = get_text(cut_str($row['pc_idea'], 255));
    $list2[$i]['datetime'] = $row['pc_datetime'];

    $list2[$i]['del'] = '';
    if ($is_admin == 'super' || ($row['mb_id'] == $member['mb_id'] && $row['mb_id']))
        $list2[$i]['del'] = '<a href="'.G5_BBS_URL.'/poll_etc_update.php?w=d&amp;pc_id='.$row['pc_id'].'&amp;po_id='.$po_id.'&amp;skin_dir='.$skin_dir.'" class="poll_delete">';
}

// 기타의견 입력
$is_etc = false;
if ($po['po_etc']) {
    $is_etc = true;
    $po_etc = $po['po_etc'];
    if ($member['mb_id'])
        $name = '<b>'.$member['mb_nick'].'</b> <input type="hidden" name="pc_name" value="'.$member['mb_nick'].'">';
    else
        $name = '<input type="text" name="pc_name" size="10" class="input" required>';
}

$list3 = array();

// 다른투표
$sql = " select po_id, po_subject, po_date from {$g5['poll_table']} order by po_id desc ";
$result = sql_query($sql);
for ($i=0; $row2=sql_fetch_array($result); $i++) {
    $list3[$i]['po_id'] = $row2['po_id'];
    $list3[$i]['date'] = substr($row2['po_date'],2,8);
    $list3[$i]['subject'] = cut_str($row2['po_subject'],60,"…");
}

if (G5_IS_MOBILE) {
    $poll_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/poll/'.$skin_dir;
    $poll_skin_url  = G5_MOBILE_URL.'/'.G5_SKIN_DIR.'/poll/'.$skin_dir;
} else {
    $poll_skin_path = G5_SKIN_PATH.'/poll/'.$skin_dir;
    $poll_skin_url  = G5_SKIN_URL.'/poll/'.$skin_dir;
}

include_once(G5_PATH.'/head.sub.php');

if (!file_exists($poll_skin_path.'/poll_result.skin.php')) die('skin error');
include_once ($poll_skin_path.'/poll_result.skin.php');

include_once(G5_PATH.'/tail.sub.php');
?>
