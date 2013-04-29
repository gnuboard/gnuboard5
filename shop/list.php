<?php
include_once("./_common.php");

$sql = " select *
           from {$g4['shop_category_table']}
          where ca_id = '$ca_id'
            and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    alert("등록된 분류가 없습니다.");

$g4['title'] = $ca['ca_name'] . " 상품리스트";

if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 스킨을 지정했다면 지정한 스킨을 사용함 (스킨의 다양화)
//if ($skin) $ca[ca_skin] = $skin;

$nav_ca_id = $ca_id;
include G4_SHOP_PATH.'/navigation1.inc.php';

$himg = G4_DATA_URL."/category/{$ca_id}_h";
if (file_exists($himg)) {
    echo "<img src='$himg' border=0><br>";
}

// 상단 HTML
echo stripslashes($ca['ca_head_html']);

if ($is_admin)
    echo "<p align=center><a href='".G4_ADMIN_URL."/shop_admin/categoryform.php?w=u&ca_id=$ca_id'><img src='".G4_SHOP_URL."/img/btn_admin_modify.gif' border=0></a></p>";

include G4_SHOP_PATH.'/listcategory2.inc.php';
?>

<table width=100% cellpadding=0 cellspacing=0>
    <tr>
        <td>

<?php
// 상품 출력순서가 있다면
if ($sort != "") {
    $order_by = $sort . " , ";
}

// 상품 (하위 분류의 상품을 모두 포함한다.)
$sql_list1 = " select * ";
$sql_list2 = " order by $order_by it_order, it_id desc ";

// 하위분류 포함
// 판매가능한 상품만
$sql_common = " from {$g4['shop_item_table']}
               where (ca_id like '{$ca_id}%'
                   or ca_id2 like '{$ca_id}%'
                   or ca_id3 like '{$ca_id}%')
                 and it_use = '1' ";

$error = "<img src='".G4_SHOP_URL."/img/no_item.gif' border=0>";

// 리스트 유형별로 출력
$list_file = G4_SHOP_PATH.'/'.$ca['ca_skin'];
if (file_exists($list_file)) {

    //display_type(2, "maintype10.inc.php", 4, 2, 100, 100, $ca[ca_id]);

    $list_mod   = $ca['ca_list_mod'];
    $list_row   = $ca['ca_list_row'];
    $img_width  = $ca['ca_img_width'];
    $img_height = $ca['ca_img_height'];

    include G4_SHOP_PATH.'/list.sub.php';
    include G4_SHOP_PATH.'/list.sort.php';

    $sql = $sql_list1 . $sql_common . $sql_list2 . " limit $from_record, $items ";
    $result = sql_query($sql);

    include $list_file;

}
else
{

    $i = 0;
    $error = "<p>{$ca['ca_skin']} 파일을 찾을 수 없습니다.<p>관리자에게 알려주시면 감사하겠습니다.";

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
<div align=center style='clear:both;'>
<?php
$qstr1 .= "ca_id=$ca_id&skin=$skin&ev_id=$ev_id&sort=$sort";
echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr1&page=");
?>
</div><br>

<?php
// 하단 HTML
echo stripslashes($ca['ca_tail_html']);

$timg = G4_DATA_PATH."/category/{$ca_id}_t";
if (file_exists($timg))
    echo "<br><img src='".G4_DATA_URL."/category/{$ca_id}_t' border=0>";

if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once('./_tail.php');

echo "\n<!-- {$ca['ca_skin']} -->\n";
?>
