<?
include_once('./_common.php');
include_once(G4_LIB_PATH.'/thumb.lib.php');

$g4['title'] = '사용후기';
include_once('./_head.php');

$sql_common = " from {$g4['yc4_item_ps_table']} where is_confirm = '1' ";
$sql_order = " order by is_time desc ";

$sql = " select count(*) as cnt
         $sql_common
         $sql_search
         $sql_order ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<script>
$(function(){
    // 사용후기 제목을 클릭하면 내용을 가지고 옴
    $(".is_subject").click(function(){
        var $is_content = $(this).parents().next(".is_content");
        if ($is_content.is(":visible")) {
            $is_content.hide();
        } else {
            $is_content.show();
        }
    }).css("cursor","pointer").attr("title","클릭하시면 후기내용을 볼수 있습니다.");
});
</script>

<br>
<table width=100% align=center cellpadding=0 cellspacing=0>
<colgroup width=50></colgroup>
<colgroup width=''></colgroup>
<colgroup width=100></colgroup>
<colgroup width=100></colgroup>
<colgroup width=100></colgroup>
<tr><td colspan="5" height="2" bgcolor="#ededed"></td></tr>
<tr height=30 bgcolor="#f7f7f7" align=center>
 <td>번호</td>
 <td>상품후기</td>
 <td>작성자</td>
 <td>작성일</td>
 <td>평가점수</td>
</tr>
<tr><td colspan="5" height="1" bgcolor="#ededed"></td></tr>
<?
$sql = " select *
          $sql_common
          $sql_search
          $sql_order
          limit $from_record, $rows ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $num = $total_count - ($page - 1) * $rows - $i;
    $star = get_star($row['is_score']);

    $thumb = new g4_thumb(G4_DATA_PATH.'/itemuse', 500);
    $is_content = $thumb->run($row['is_content']);
    $is_time = substr($row['is_time'], 2, 14);
    $small_image = $row['it_id']."_s";

    $row2 = sql_fetch(" select it_name from {$g4['yc4_item_table']} where it_id = '{$row['it_id']}' ");
    $it_href = G4_SHOP_URL."/item.php?it_id={$row['it_id']}";

    echo "
    <tr height=30>
        <td align=center>$num</td>
        <td>
            <table>
            <tr>
                <td width='120' align='center' valign='top'><a href='$it_href'>".get_it_image($small_image, 100, 100)."</a></td>
                <td valign='top'>
                    <div style='padding:5px 0;'><a href='$it_href'>{$row2['it_name']}</a></div>
                    <div class='is_subject'>{$row['is_subject']}</div></td>
            </tr>
            </table>
        </td>
        <td align=center>{$row['is_name']}</td>
        <td align=center>".substr($row['is_time'],0,10)."</td>
        <td align=center><img src='".G4_SHOP_URL."/img/star{$star}.gif' border=0></td>
    </tr>
    <tr class='is_content' style='display:none;'><td colspan='5' style='padding:10px;' class='lh'><div style='padding:20px;border:1px solid #ccc;'>$is_content</div></td></tr>
    <tr><td colspan='5' height='1' bgcolor='#ededed'></td></tr>
    ";
}
if ($i == 0)
    echo "<tr><td colspan=5 align=center height=100>자료가 없습니다.</td></tr>";
?>
</table>
<br><br>

<div style="text-align:center;">
    <?=get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&page=");?>
</div>

<?
include_once('./_tail.php');
?>
