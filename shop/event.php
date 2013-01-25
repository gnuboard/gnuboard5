<?
include_once("./_common.php");

$sql = " select * from $g4[yc4_event_table] 
          where ev_id = '$ev_id' 
            and ev_use = 1 ";
$ev = sql_fetch($sql);
if (!$ev[ev_id]) 
    alert("등록된 이벤트가 없습니다.");

$g4[title] = $ev[ev_subject];
include_once("./_head.php");

$himg = "$g4[path]/data/event/{$ev_id}_h";
if (file_exists($himg)) 
    echo "<img src='$himg' border=0><br>";

if ($is_admin)
    echo "<p align=center><a href='$g4[shop_admin_path]/itemeventform.php?w=u&ev_id=$ev[ev_id]'><img src='$g4[shop_img_path]/btn_admin_modify.gif' border=0></a></p>";

// 상단 HTML
echo stripslashes($ev[ev_head_html]);
?>

<table width=100% cellpadding=0 cellspacing=0>
    <tr>
        <td>

<?
// 상품 출력순서가 있다면
if ($sort != "")
    $order_by = $sort . " , ";

// 상품 (하위 분류의 상품을 모두 포함한다.)
// 1.02.00 
// a.it_order 추가
/*
$sql_list1 = " select a.ca_id,
                      a.it_id, 
                      a.it_name, 
                      a.it_maker,
                      a.it_point,
                      a.it_amount,
                      a.it_stock_qty,
                      a.it_cust_amount,
                      a.it_amount,
                      a.it_amount2,
                      a.it_amount3,
                      it_basic,
                      it_opt1,
                      it_opt2,
                      it_opt3,
                      it_opt4,
                      it_opt5,
                      it_opt6,
                      a.it_type1,
                      a.it_type2,
                      a.it_type3,
                      a.it_type4,
                      a.it_type5 ";
*/
$sql_list1 = " select * ";
$sql_list2 = " order by $order_by a.it_order, a.it_id desc ";

$sql_common = " from $g4[yc4_item_table] a
                left join $g4[yc4_event_item_table] b on (a.it_id=b.it_id)
               where b.ev_id = '$ev_id'
                 and a.it_use = '1' ";

$error = "<img src='$g4[shop_img_path]/no_item.gif' border=0>";

if ($skin) 
    $ev[ev_skin] = $skin;

$td_width = (int)($mod / 100);

// 리스트 유형별로 출력
$list_file = "$g4[shop_path]/$ev[ev_skin]";
if (file_exists($list_file)) 
{
    $list_mod   = $ev[ev_list_mod];
    $list_row   = $ev[ev_list_row];
    $img_width  = $ev[ev_img_width];
    $img_height = $ev[ev_img_height];

    include "$g4[shop_path]/list.sub.php";
    include "$g4[shop_path]/list.sort.php";

    $sql = $sql_list1 . $sql_common . $sql_list2 . " limit $from_record, $items ";
    $result = sql_query($sql);

    include $list_file;

} 
else 
{
    $i = 0;
    $error = "<p>$ev[ev_skin] 파일을 찾을 수 없습니다.<p>관리자에게 알려주시면 감사하겠습니다.";
}

if ($i==0)
{
    echo "<br>";
    echo "<div align=center>$error</div>";
}
?>

        </td>
    </tr>
</table>

<br>
<div align=center>
<?
$qstr .= "ca_id=$ca_id&skin=$skin&ev_id=$ev_id&sort=$sort";
echo get_paging($config[cf_write_pages], $page, $total_page, "$_SERVER[PHP_SELF]?$qstr&page=");
?>
</div><br>

<?
// 하단 HTML
echo stripslashes($ev[ev_tail_html]);

$timg = "$g4[path]/data/event/{$ev_id}_t";
if (file_exists($timg)) 
    echo "<br><img src='$timg' border=0><br>";

include_once("./_tail.php");
?>
