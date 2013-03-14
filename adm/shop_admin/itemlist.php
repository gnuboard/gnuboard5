<?
$sub_menu = "400300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "상품관리";
include_once ("$g4[admin_path]/admin.head.php");

// 분류
$ca_list  = "";
$sql = " select * from $g4[yc4_category_table] ";
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '$member[mb_id]' ";
$sql .= " order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row[ca_id]) / 2 - 1;
    $nbsp = "";
    for ($i=0; $i<$len; $i++) {
        $nbsp .= "&nbsp;&nbsp;&nbsp;";
    }
    $ca_list .= "<option value='$row[ca_id]'>$nbsp$row[ca_name]";
}
$ca_list .= "</select>";


$where = " and ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

if ($sca != "") {
    $sql_search .= " $where (a.ca_id like '$sca%' or a.ca_id2 like '$sca%' or a.ca_id3 like '$sca%') ";
}

if ($sfl == "")  $sfl = "it_name";

$sql_common = " from $g4[yc4_item_table] a ,
                     $g4[yc4_category_table] b
               where (a.ca_id = b.ca_id";
if ($is_admin != 'super')
    $sql_common .= " and b.ca_mb_id = '$member[mb_id]'";
$sql_common .= ") ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row[cnt];

$rows = $config[cf_page_rows];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst) {
    $sst  = "it_id";
    $sod = "desc";
}
$sql_order = "order by $sst $sod";


$sql  = " select *
           $sql_common
           $sql_order
           limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr  = "$qstr&sca=$sca&page=$page";
$qstr  = "$qstr&sca=$sca&page=$page&save_stx=$stx";
?>

<table width=100% cellpadding=4 cellspacing=0>
<form name=flist>
<input type=hidden name=page value="<?=$page?>">
<tr>
    <td width=20%><a href='<?=$_SERVER[PHP_SELF]?>'>처음</a></td>
    <td width=60% align=center>
        <select name="sca">
            <option value=''>전체분류
            <?
            $sql1 = " select ca_id, ca_name from $g4[yc4_category_table] order by ca_id ";
            $result1 = sql_query($sql1);
            for ($i=0; $row1=sql_fetch_array($result1); $i++)
            {
                $len = strlen($row1[ca_id]) / 2 - 1;
                $nbsp = "";
                for ($i=0; $i<$len; $i++) $nbsp .= "&nbsp;&nbsp;&nbsp;";
                echo "<option value='$row1[ca_id]'>$nbsp$row1[ca_name]\n";
            }
            ?>
        </select>
        <script> document.flist.sca.value = '<?=$sca?>';</script>

        <select name=sfl>
            <option value='it_name'>상품명
            <option value='it_id'>상품코드
            <option value='it_maker'>제조사
            <option value='it_origin'>원산지
            <option value='it_sell_email'>판매자 e-mail
        </select>
        <?// if ($sel_field) echo "<script> document.flist.sel_field.value = '$sel_field';</script>"; ?>
        <? if ($sfl) echo "<script> document.flist.sfl.value = '$sfl';</script>"; ?>

        <input type=hidden name=save_stx value='<?=$stx?>'>
        <input type=text name=stx value='<?=$stx?>'>
        <input type=image src='<?=$g4[admin_path]?>/img/btn_search.gif' align=absmiddle>
    </td>
    <td width=20% align=right>건수 : <? echo $total_count ?>&nbsp;</td>
</tr>
</table>


<table cellpadding=0 cellspacing=0 width=100% border=0>
<tr><td colspan=13 height=2 bgcolor=0E87F9></td></tr>
<tr align=center class=ht>
    <td width=70><?=subject_sort_link("it_id", "sca=$sca")?>상품코드</a></td>
    <td width='' colspan=2><?=subject_sort_link("it_name", "sca=$sca")?>상품명</a></td>
    <td width=70><?=subject_sort_link("it_amount", "sca=$sca")?>비회원가격</a><br><?=subject_sort_link("it_cust_amount", "sca=$sca")?>시중가격</a></td>
    <td width=70><?=subject_sort_link("it_amount2", "sca=$sca")?>회원가격</a><br><?=subject_sort_link("it_point", "sca=$sca")?>포인트</a></td>
    <td width=70><?=subject_sort_link("it_amount3", "sca=$sca")?>특별가격</a><br><?=subject_sort_link("it_stock_qty", "sca=$sca")?>재고</a></td>
    <td width=30><?=subject_sort_link("it_order", "sca=$sca")?>순서</a></td>
    <td width=30><?=subject_sort_link("it_use", "sca=$sca", 1)?>판매</a></td>
    <td width=30><?=subject_sort_link("it_hit", "sca=$sca", 1)?>조회</a></td>
    <td width=100><a href='./itemform.php'><img src='<?=$g4[admin_path]?>/img/icon_insert.gif' border=0 title='상품등록'></a></td>
</tr>
<tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</form>

<form name=fitemlistupdate method=post action="./itemlistupdate.php" autocomplete='off'>
<input type=hidden name=sca  value="<?=$sca?>">
<input type=hidden name=sst  value="<?=$sst?>">
<input type=hidden name=sod  value="<?=$sod?>">
<input type=hidden name=sfl  value="<?=$sfl?>">
<input type=hidden name=stx  value="<?=$stx?>">
<input type=hidden name=page value="<?=$page?>">
<?
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $href = "{$g4[shop_path]}/item.php?it_id=$row[it_id]";

    $s_mod = icon("수정", "./itemform.php?w=u&it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr");
    $s_del = icon("삭제", "javascript:del('./itemformupdate.php?w=d&it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr');");
    $s_vie = icon("보기", $href);
    //$s_copy = "<a href=\"javascript:board_copy('$row[bo_table]');\"><img src='img/icon_copy.gif' border=0 title='복사'></a>";
    //$s_copy = icon("복사", "javascript:_copy('".get_text(htmlspecialchars2($row[it_name]))."', 'item_copy_update.php?it_id=$row[it_id]&ca_id=$row[ca_id]&$qstr');");
    $s_copy = icon("복사", "javascript:_copy('$row[it_id]', '$row[ca_id]');");

    $gallery = $row[it_gallery] ? "Y" : "";

    $tmp_ca_list  = "<select id='ca_id_$i' name='ca_id[$i]'>" . $ca_list;
    $tmp_ca_list .= "<script language='javascript'>document.getElementById('ca_id_$i').value='$row[ca_id]';</script>";

    $list = $i%2;
    echo "
    <input type='hidden' name='it_id[$i]' value='$row[it_id]'>
    <tr class='list$list'>
        <td>$row[it_id]</td>
        <td style='padding-top:5px; padding-bottom:5px;'><a href='$href'>".get_it_image("{$row[it_id]}_s", 50, 50)."</a></td>
        <td align=left>$tmp_ca_list<br><input type='text' name='it_name[$i]' value='".htmlspecialchars2(cut_str($row[it_name],250, ""))."' required size=40 class=ed></td>
        <td colspan=3>
            <table width=210 cellpadding=0 cellspacing=0>
            <tr>
                <td>
                    <table cellpadding=0 cellspacing=0>
                    <tr>
                        <td width=70 align=center><input type='text' name='it_amount[$i]' value='$row[it_amount]' class=ed size=7 style='text-align:right; background-color:#DDE6FE;'></td>
                        <td width=70 align=center><input type='text' name='it_amount2[$i]' value='$row[it_amount2]' class=ed size=7 style='text-align:right; background-color:#DDFEDE;'></td>
                        <td width=70 align=center><input type='text' name='it_amount3[$i]' value='$row[it_amount3]' class=ed size=7 style='text-align:right; background-color:#FEDDDD;'></td>
                    </tr>
                    </table></td>
            </tr>
            <tr>
                <td>
                    <table cellpadding=0 cellspacing=0>
                    <tr>
                        <td width=70 align=center><input type='text' name='it_cust_amount[$i]' value='$row[it_cust_amount]' class=ed size=7 style='text-align:right;'></td>
                        <td width=70 align=center><input type='text' name='it_point[$i]' value='$row[it_point]' class=ed size=7 style='text-align:right;'></td>
                        <td width=70 align=center><input type='text' name='it_stock_qty[$i]' value='$row[it_stock_qty]' class=ed size=7 style='text-align:right;'></td>
                    </tr>
                    </table></td>
            </tr>
            </table></td>
        <td><input type='text' name='it_order[$i]' value='$row[it_order]' class=ed size=3 style='text-align:right;'></td>
        <td><input type=checkbox name='it_use[$i]' ".($row[it_use] ? "checked" : "")." value='1'></td>
        <td>$row[it_hit]</td>
        <td>$s_mod $s_del $s_vie $s_copy</td>
    </tr>";
}
if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 한건도 없습니다.</span></td></tr>";
?>
<tr><td colspan=13 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<table width=100%>
<tr>
    <td width=50%><input type=submit class=btn1 value='일괄수정' accesskey='s'></td>
    <td width=50% align=right><?=get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");?></td>
</tr>
</table>
</form>

<script>
function _trim(str)
{
    var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
    return str.replace(pattern, "");
}

/*
function _copy(it_name, link)
{
    var now = new Date();
    var time = now.getTime() + '';
    var new_it_id = prompt("'"+it_name+"' 상품을 복사하시겠습니까? 상품코드를 입력하세요.", time.substring(3,13));
    if (!new_it_id) {
        alert('상품코드를 입력하세요.');
        return;
    }

    if (g4_charset.toUpperCase() == 'EUC-KR') 
        location.href = link+'&new_it_id='+new_it_id;
    else
        location.href = encodeURI(link+'&new_it_id='+new_it_id);
}
*/

function _copy(it_id, ca_id)
{
    window.open('./item_copy.php?it_id='+it_id+'&ca_id='+ca_id, 'copywin', 'left=100, top=100, width=300, height=200, scrollbars=0');
}
</script>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>
