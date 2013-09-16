<?php
$sub_menu = '400300';
include_once('./_common.php');

$sql = " select ca_id, it_id, it_name, it_price
           from {$g5['g5_shop_item_table']}
          where it_id <> '$it_id'
            and ( ca_id like '$ca_id%' or ca_id2 like '$ca_id%' or ca_id3 like '$ca_id%' )
          order by ca_id, it_name ";
$result = sql_query($sql);

$list = '';

for($i=0;$row=sql_fetch_array($result);$i++) {
    $sql2 = " select count(*) as cnt from {$g5['g5_shop_item_relation_table']} where it_id = '$it_id' and it_id2 = '{$row['it_id']}' ";
    $row2 = sql_fetch($sql2);
    if ($row2['cnt'])
        continue;

    $it_name = get_it_image($row['it_id'], 50, 50).' '.$row['it_name'];

    $list .= '<li>';
    $list .= '<input type="hidden" name="re_it_id[]" value="'.$row['it_id'].'">';
    $list .= $it_name;
    $list .= '<button type="button" class="add_item">추가</button>';
    $list .= '</li>'.PHP_EOL;
}

if($list)
    $list = '<ul>'.$list.'</ul>';
else
    $list = '<p>등록된 상품이 없습니다.';

echo $list;
?>