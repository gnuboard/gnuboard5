<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/listtype.php');
    return;
}

$type = $_REQUEST['type'];
if ($type == 1)      $g4['title'] = "히트상품";
else if ($type == 2) $g4['title'] = "추천상품";
else if ($type == 3) $g4['title'] = "최신상품";
else if ($type == 4) $g4['title'] = "인기상품";
else if ($type == 5) $g4['title'] = "할인상품";
else
    alert('상품유형이 아닙니다.');

include_once('./_head.php');

// 한페이지에 출력하는 이미지수 = $list_mod * $list_row
$list_mod   = 3;    // 한줄에 이미지 몇개씩 출력?
$list_row   = 5;    // 한 페이지에 몇라인씩 출력?

$img_width  = 230;  // 출력이미지 폭
$img_height = 230;  // 출력이미지 높이
?>

<?php
// 상품 출력순서가 있다면
if ($sort != "")
    $order_by = $sort . " , ";

// 상품 (하위 분류의 상품을 모두 포함한다.)
$sql_list1 = " select * ";
$sql_list2 = " order by $order_by it_order, it_id desc ";

$sql_common = " from {$g4['shop_item_table']}
               where it_type{$type} = '1'
                 and it_use = '1' ";
if ($ca_id) {
    $sql_common .= " and ca_id = '$ca_id' ";
}

$error = "<img src='".G4_MSHOP_URL."/img/no_item.gif' border=0>";

if (!$skin)
    $skin = "list.skin.10.php";

$td_width = (int)($mod / 100);

// 리스트 유형별로 출력
$list_file = G4_MSHOP_PATH.'/'.$skin;
if (file_exists($list_file)) {

    include G4_MSHOP_PATH."/list.sub.php";
    //include "$cart_dir/list.sort.php";

    $sql = $sql_list1 . $sql_common . $sql_list2 . " limit $from_record, $items ";
    $result = sql_query($sql);

    include $list_file;

}
else
{
    $i = 0;
    $error = "<p>$skin 파일을 찾을 수 없습니다.<p>관리자에게 알려주시면 감사하겠습니다.";
}

if ($i==0)
{
    echo "<br>";
    echo "<div align=center>$error</div>";
}
?>

<?php
$qstr .= '&amp;type='.$type.'&amp;sort='.$sort;
echo get_paging($config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page=");
?>

<?php
// 테스트
// 분류를 링크하는 코드
/*
$bar = "";
$sql = " select ca_id from $g4[shop_item_table]
               where it_type{$type} = '1'
                 and it_use = '1'
          group by ca_id
          order by ca_id ";
$result = sql_query($sql);
for($i=0;$row=sql_fetch_array($result);$i++) {
    $row2 = sql_fetch(" select ca_name from $g4[shop_category_table] where ca_id = '$row[ca_id]' ");
    echo $bar . "<a href='$g4[shop_path]/list.php?ca_id=$row[ca_id]'>$row2[ca_name]</a>";
    $bar = " | ";
}
*/
?>

<?php
include_once('./_tail.php');
?>
