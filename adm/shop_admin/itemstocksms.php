<?php
$sub_menu = '500400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '재입고SMS 알림';
include_once (G5_ADMIN_PATH.'/admin.head.php');

// 테이블 생성
if(!isset($g5['g5_shop_item_stocksms_table']))
    die('<meta charset="utf-8">dbconfig.php 파일에 <strong>$g5[\'g5_shop_item_stocksms_table\'] = G5_SHOP_TABLE_PREFIX.\'item_stocksms\';</strong> 를 추가해 주세요.');

if(!sql_query(" select ss_id from {$g5['g5_shop_item_stocksms_table']} limit 1", false)) {
    sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['g5_shop_item_stocksms_table']}` (
                  `ss_id` int(11) NOT NULL AUTO_INCREMENT,
                  `it_id` varchar(20) NOT NULL DEFAULT '',
                  `ss_hp` varchar(255) NOT NULL DEFAULT '',
                  `ss_send` tinyint(4) NOT NULL DEFAULT '0',
                  `ss_send_time` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `ss_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
                  `ss_ip` varchar(25) NOT NULL DEFAULT '',
                  PRIMARY KEY (`ss_id`)
                ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ", true);
}

$doc = isset($_GET['doc']) ? clean_xss_tags($_GET['doc'], 1, 1) : '';
$sort1 = (isset($_GET['sort1']) && in_array($_GET['sort1'], array('it_id', 'ss_hp', 'ss_send', 'ss_send_time', 'ss_datetime'))) ? $_GET['sort1'] : 'ss_send';
$sort2 = (isset($_GET['sort2']) && in_array($_GET['sort2'], array('desc', 'asc'))) ? $_GET['sort2'] : 'asc';
$sel_field = (isset($_GET['sel_field']) && in_array($_GET['sel_field'], array('it_id', 'ss_hp', 'ss_send')) ) ? $_GET['sel_field'] : 'it_id';
$search = isset($_GET['search']) ? get_search_string($_GET['search']) : '';

$sql_search = " where 1 ";
if ($search != "") {
	if ($sel_field != "") {
    	$sql_search .= " and $sel_field like '%$search%' ";
    }
}

$sql_common = "  from {$g5['g5_shop_item_stocksms_table']} ";

// 미전송 건수
$sql = " select count(*) as cnt " . $sql_common . " where ss_send = '0' ";
$row = sql_fetch($sql);
$unsend_count = $row['cnt'];

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql  = " select *
           $sql_common
           $sql_search
          order by $sort1 $sort2
          limit $from_record, $rows ";
$result = sql_query($sql);

$qstr1 = 'sel_field='.$sel_field.'&amp;search='.$search;
$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
     <span class="btn_ov01"><span class="ov_txt">전체 </span><span class="ov_num"> <?php echo number_format($total_count); ?>건</span></span>  
     <span class="btn_ov01"><span class="ov_txt">미전송 </span><span class="ov_num"><?php echo number_format($unsend_count); ?>건</span></span>  
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="doc" value="<?php echo $doc; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<label for="sel_field" class="sound_only">검색대상</label>
<select name="sel_field" id="sel_field">
    <option value="it_id" <?php echo get_selected($sel_field, 'it_id'); ?>>상품코드</option>
    <option value="ss_hp" <?php echo get_selected($sel_field, 'ss_hp'); ?>>휴대폰번호</option>
</select>

<label for="search" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="search" id="search" value="<?php echo $search; ?>" required class="frm_input required">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fitemstocksms" action="./itemstocksmsupdate.php" method="post" onsubmit="return fitemstocksms_submit(this);">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col">
            <label for="chkall" class="sound_only">알림요청 전체</label>
            <input type="checkbox" name="chkall" value="1" id="chkall" onclick="check_all(this.form)">
        </th>
        <th scope="col">상품명</th>
        <th scope="col">휴대폰번호</th>
        <th scope="col">SMS전송</th>
        <th scope="col">SMS전송일시</th>
        <th scope="col">등록일시</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 상품정보
        $sql = " select it_name from {$g5['g5_shop_item_table']} where it_id = '{$row['it_id']}' ";
        $it = sql_fetch($sql);

        if($it['it_name'])
            $it_name = get_text($it['it_name']);
        else
            $it_name = '상품정보 없음';

        $bg = 'bg'.($i%2);

    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_chk">
            <label for="chk_<?php echo $i; ?>" class="sound_only"><?php echo $it_name; ?> 알림요청</label>
            <input type="checkbox" name="chk[]" value="<?php echo $i ?>" id="chk_<?php echo $i; ?>">
            <input type="hidden" name="ss_id[<?php echo $i; ?>]" value="<?php echo $row['ss_id']; ?>">
        </td>
        <td class="td_left"><?php echo $it_name; ?></td>
        <td class="td_telbig"><?php echo $row['ss_hp']; ?></td>
        <td class="td_stat"><?php echo ($row['ss_send'] ? '전송완료' : '전송전'); ?></td>
        <td class="td_datetime"><?php echo (is_null_time($row['ss_send_time']) ? '' : $row['ss_send_time']); ?></td>
        <td class="td_datetime"><?php echo (is_null_time($row['ss_datetime']) ? '' : $row['ss_datetime']); ?></td>
    </tr>
    <?php
    }
    if (!$i)
        echo '<tr><td colspan="6" class="empty_table"><span>자료가 없습니다.</span></td></tr>';
    ?>
    </tbody>
    </table>
</div>

    
<div class="btn_fixed_top">
    <?php if ($is_admin == 'super') { ?>
    <input type="submit" name="act_button" value="선택삭제" onclick="document.pressed=this.value" class="btn btn_02">
    <?php } ?>
    <input type="submit" name="act_button" value="선택SMS전송" class="btn_submit btn" onclick="document.pressed=this.value">
</div>
</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
function fitemstocksms_submit(f)
{
    if (!is_checked("chk[]")) {
        alert(document.pressed+" 하실 항목을 하나 이상 선택하세요.");
        return false;
    }

    if(document.pressed == "선택삭제") {
        if(!confirm("선택한 자료를 정말 삭제하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');