<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$ca_id = trim($ca_id);
$it_name = trim(strip_tags($it_name));

if(!$ca_id && !$it_name)
    die('<p>상품의 분류를 선택하시거나 상품명을 입력하신 후 검색하여 주십시오.</p>');

$sql = " select ca_id, it_id, it_name, it_price
           from {$g5['g5_shop_item_table']}
          where it_id <> '$it_id' ";
if($ca_id)
    $sql .= " and ( ca_id like '$ca_id%' or ca_id2 like '$ca_id%' or ca_id3 like '$ca_id%' ) ";

if($it_name)
    $sql .= " and it_name like '%$it_name%' ";

$sql .= " order by ca_id, it_name ";
$result = sql_query($sql);

$list = '';

for($i=0;$row=sql_fetch_array($result);$i++) {
    $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_relation_table']} where it_id = '$it_id' and it_id2 = '{$row['it_id']}' ";
    $row2 = sql_fetch($sql2);
    if ($row2['cnt'])
        continue;

    $it_name = get_it_image($row['it_id'], 50, 50).' '.$row['it_name'];

    $list .= '<li class="list_res">';
    $list .= '<input type="hidden" name="re_it_id[]" value="'.$row['it_id'].'">';
    $list .= '<div class="list_item">'.$it_name.'</div>';
    $list .= '<div class="list_item_btn"><button type="button" class="add_item btn_frmline">추가</button></div>';
    $list .= '</li>'.PHP_EOL;
}

if($list)
    $list = '<ul>'.$list.'</ul>';
else
    $list = '<p>등록된 상품이 없습니다.';

echo $list;