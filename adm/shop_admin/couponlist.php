<?
$sub_menu = "400800";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4['title'] = "쿠폰관리";
include_once ("$g4[admin_path]/admin.head.php");

$sql_common = " from {$g4['yc4_coupon_table']} a left join {$g4['yc4_item_table']} b on ( a.it_id = b.it_id ) ";

$sql_search = " where (1) ";

if($stx != '') {
    $sql_search .= " and $sfl like '%$stx%' ";

    if ($save_stx != $stx)
        $page = 1;
}

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "cp_no";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";

$sql  = " select a.*, b.it_name
           $sql_common
           $sql_search
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = "$qstr&sca=$sca&page=$page";
$qstr  = "$qstr&page=$page&save_stx=$stx";
?>

<table width=100% cellpadding=4 cellspacing=0>
<form name=flist>
<input type=hidden name=page value="<?=$page?>">
<tr>
    <td width=20%><a href='<?=$_SERVER['PHP_SELF']?>'>처음</a></td>
    <td width=60% align=center>
       <select name=sfl>
            <option value='cp_id'>쿠폰번호
            <option value='cp_subject'>쿠폰명
            <option value='it_name'>상품명
            <option value='mb_id'>회원아이디
        </select>
        <?// if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
        <? if ($sfl) echo "<script> document.flist.sfl.value = '$sfl';</script>"; ?>

        <input type=hidden name=save_stx value='<?=$stx?>'>
        <input type=text name=stx value='<?=$stx?>'>
        <input type=image src='<?=$g4['admin_path']?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</form>
</table>


<form id="fcouponlist" method="post" action="./coupondelete.php" style="margin: 0;">
<input type="hidden" name="sst"  value="<? echo $sst ?>" />
<input type="hidden" name="sod"  value="<? echo $sod; ?>" />
<input type="hidden" name="sfl"  value="<? echo $sfl; ?>" />
<input type="hidden" name="stx"  value="<? echo $stx; ?>" />
<input type="hidden" name="page" value="<? echo $page; ?>" />
<table cellpadding=0 cellspacing=0 width=100% border=0>
<tr><td colspan=9 height=2 bgcolor=0E87F9></td></tr>
<tr align=center class=ht>
    <td width="50"><input type="checkbox" name="list_all" value="1" /></td>
    <td width="70">쿠폰번호</td>
    <td width="">쿠폰명</td>
    <td width="100"><?=subject_sort_link("mb_id", "")?>회원아이디</a></td>
    <td width="100">적용범위</td>
    <td width="70">할인</td>
    <td width="100"><?=subject_sort_link("cp_limit", "")?>사용기한</a></td>
    <td width="60">사용수</td>
    <td width="60"><a href='./couponform.php'><img src='<?=$g4['admin_path']?>/img/icon_insert.gif' border=0 title='상품등록'></a></td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
for($i=0; $row=sql_fetch_array($result); $i++) {
    $cp_subject = get_text($row['cp_subject']);
    $cp_end = "";
    $mb_id = $row['mb_id'];
    // 적용범위
    if($row['cp_target'] == 2) {
        $target = '전체상품';
    } else if($row['cp_target'] == 3) {
        if($row['cp_type'] == 1) {
            $target = '결제금액';
        } else if($row['cp_type'] == 2) {
            $target = '배송비';
        }
    } else if($row['cp_target'] == 1) { // 카테고리
        if($row['ca_id'] != '전체카테고리') {
            $sql = " select ca_name from {$g4['yc4_category_table']} where ca_id = '{$row['ca_id']}' ";
            $temp = sql_fetch($sql);
            $target = $temp['ca_name'];
        } else {
            $target = '전체카테고리';
        }
    } else {
        $target = $row['it_name'];
    }
    // 사용기한
    $limit = explode('-', $row['cp_end']);
    $cp_end = substr($limit[0], 2, 2).'년 '.(int)$limit[1].'월 '.(int)$limit[2].'일';
    // 쿠폰사용수
    $sql1 = " select count(*) as cnt from {$g4['yc4_coupon_history_table']} where cp_id = '{$row['cp_id']}' ";
    $row1 = sql_fetch($sql1);
    $use_count = number_format($row1['cnt']);

    $s_mod = icon("수정", "./couponform.php?w=u&cp_no={$row['cp_no']}&$qstr");
    $s_del = icon("삭제", "javascript:del('./couponformupdate.php?w=d&cp_no={$row['cp_no']}&$qstr');");

    $list = $i%2;
    echo "
    <tr class='list$list ht'>
        <td align=\"center\"><input type=\"checkbox\" name=\"list_chk[]\" value=\"{$row['cp_no']}\" /></td>
        <td align=\"center\">".$row['cp_id']."</td>
        <td>".$cp_subject."</td>
        <td align=\"center\">".$mb_id."</td>
        <td>".$target."</td>
        <td align=\"right\">".number_format($row['cp_amount']).($row['cp_method'] ? '%' : '원')."</td>
        <td align=\"center\">".$cp_end."</td>
        <td align=\"center\">".$use_count."</td>
        <td align=\"center\">$s_mod $s_del</td>
    </tr>";
}

if ($i == 0) {
    echo "<tr><td colspan=\"9\" align=\"center\" height=\"100\" bgcolor=\"#ffffff\"><span class=\"point\">자료가 한건도 없습니다.</span></td></tr>\n";
}
?>
<tr><td colspan="9" height="1" bgcolor="#CCCCCC"></td></tr>
</table>

<table width="100%">
<tr>
    <td width="50%"><input type="submit" class="btn1" value="선택삭제" /></td>
    <td width="50%" align="right"><?=get_paging($config['cf_write_pages'], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>
</form>

<script>
$(function() {
    // 전체선택
    $('input[name=list_all]').click(function() {
        if($(this).is(':checked')) {
            $('input[name^=list_chk]').attr('checked', true);
        } else {
            $('input[name^=list_chk]').attr('checked', false);
        }
    });

    // 선택삭제
    $('#fcouponlist').submit(function() {
        if(confirm('선택 쿠폰을 삭제하시겠습니까?')) {
            var count = $('input[name^=list_chk]:checked').size();
            if(count < 1) {
                alert('삭제할 쿠폰을 1개 이상 선택해 주세요.');
                return false;
            }
        }

        return true;
    });
});
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
