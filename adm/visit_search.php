<?php 
$sub_menu = '200810';
include_once('./_common.php');
include_once(G5_PATH.'/lib/visit.lib.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '접속자검색';
include_once('./admin.head.php');

$search_word = escape_trim($_GET['search_word']);
$search_sort = escape_trim($_GET['search_sort']);

$colspan = 5;
$qstr = "search_word=$search_word&search_sort=$search_sort"; //페이징 처리관련 변수
$listall = "<a href='{$_SERVER['PHP_SELF']}' class=tt>처음</a>"; //페이지 처음으로 (초기화용도)
?>

<table width="100%" cellpadding="3" cellspacing="1">
<form name="fvisit" method="get" onsubmit="return fvisit_submit(this);">
<tr>
    <td class="sch_wrp">
        <?=$listall?>
        <label for="sch_sort">검색분류</label>
        <select name="search_sort" id="sch_sort" class="search_sort">
            <?php 
            //echo '<option value="vi_ip" '.($search_sort=='vi_ip'?'selected="selected"':'').'>IP</option>'; //selected 추가
            if($search_sort=='vi_ip'){ //select 안의 옵셥값이 vi_ip면
                echo '<option value="vi_ip" selected="selected">IP</option>'; //selected 추가
            }else{
                echo '<option value="vi_ip">IP</option>';
            }
            if($search_sort=='vi_referer'){ //select 안의 옵셥값이 vi_referer면
                echo '<option value="vi_referer" selected="selected">접속경로</option>'; //selected 추가
            }else{
                echo '<option value="vi_referer">접속경로</option>';
            }
            if($search_sort=='vi_date'){ //select 안의 옵셥값이 vi_date면
                echo '<option value="vi_date" selected="selected">날짜</option>'; //selected 추가
            }else{
                echo '<option value="vi_date">날짜</option>';
            }
            ?>
        </select>
        <input type="text" name="search_word" size="20" value="<?=$search_word?>" id="sch_word" class="ed">
        <input type="submit" value="검색">
    </td>
</tr>
</form>
</table>

<table width="100%" cellpadding="0" cellspacing="1" border="0">
<colgroup width="100">
<colgroup width="350">
<colgroup width="100">
<colgroup width="100">
<colgroup width="">
<tr><td colspan="<?=$colspan?>" class="line1"></td></tr>
<tr class="bgcol1 bold col1 ht center">
    <td>IP</td>
    <td>접속 경로</td>
    <td>브라우저</td>
    <td>OS</td>
    <td>일시</td>
</tr>
<tr><td colspan="<?=$colspan?>" class="line2"></td></tr>
<?php 
$sql_common = " from {$g5['visit_table']} ";
if ($search_sort) {
    if($search_sort=='vi_ip' || $search_sort=='vi_date'){
        $sql_search = " where $search_sort like '$search_word%' ";
    }else{
        $sql_search = " where $search_sort like '%$search_word%' ";
    }
}
$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select * 
            {$sql_common}
            {$sql_search}
            order by vi_id desc
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++) {
    $brow = get_brow($row['vi_agent']);
    $os   = get_os($row['vi_agent']);

    $link = "";
    $referer = "";
    $title = "";
    if ($row['vi_referer']) {

        $referer = get_text(cut_str($row[vi_referer], 255, ""));
        $referer = urldecode($referer);

        if (strtolower($g4['charset']) == 'utf-8') {
            if (!is_utf8($referer)) {
                $referer = iconv('euc-kr', 'utf-8', $referer);
            }
        }
        else {
            if (is_utf8($referer)) {
                $referer = iconv('utf-8', 'euc-kr', $referer);
            }
        }

        $title = str_replace(array("<", ">"), array("&lt;", "&gt;"), $referer);
        $link = "<a href='$row[vi_referer]' target=_blank title='$title '>";
    }

    if ($is_admin == 'super')
        $ip = $row['vi_ip'];
    else
        $ip = preg_replace("/([0-9]+).([0-9]+).([0-9]+).([0-9]+)/", "\\1.♡.\\3.\\4", $row['vi_ip']);

    if ($brow == '기타') { $brow = "<span title='$row[vi_agent]'>$brow</span>"; }
    if ($os == '기타') { $os = "<span title='$row[vi_agent]'>$os</span>"; }

    $list = ($i%2);
    echo "
    <tr class='list$list col1 ht center'>
        <td align='left'>&nbsp;<a href='{$_SERVER['PHP_SELF']}?search_sort=vi_ip&amp;search_word=$ip'>$ip</a></td>
        <td align=left><nobr style='display:block; overflow:hidden; width:350;'>$link$title</a></nobr></td>
        <td>$brow</td>
        <td>$os</td>
        <td><a href='{$_SERVER['PHP_SELF']}?search_sort=vi_date&amp;search_word={$row['vi_date']}'>$row[vi_date]</a> $row[vi_time]</td>
    </tr>";
}

if ($i == 0)
    echo "<tr><td colspan='$colspan' height=100 align=center>자료가 없습니다.</td></tr>"; 

echo "<tr><td colspan='$colspan' class='line2'></td></tr>";
echo "</table>";

$page = get_paging($config['cf_write_pages'], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&domain=$domain&page=");
if ($page) {
    echo "<table width=100% cellpadding=3 cellspacing=1><tr><td align=right>$page</td></tr></table>";
}
?>

<script>
$(function(){
    $("#sch_sort").change(function(){ // select #sch_sort의 옵션이 바뀔때
        if($(this).val()=="vi_date"){ // 해당 value 값이 vi_date이면
            $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
        }else{ // 아니라면
            $("#sch_word").datepicker("destroy"); // datepicker 미실행
        }
    });
    if($("#sch_sort option:selected").val()=="vi_date"){ // select #sch_sort 의 옵션중 selected 된것의 값이 vi_date라면
        $("#sch_word").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+0d" }); // datepicker 실행
    }
});

function fvisit_submit(f) 
{
    return true;
}
</script>

<?php 
include_once('./admin.tail.php');
?>
