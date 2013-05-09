<?php
include_once('./_common.php');

$sql = " select *
           from {$g4['shop_category_table']}
          where ca_id = '$ca_id'
            and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    alert('등록된 분류가 없습니다.');

$g4['title'] = $ca['ca_name'].' 상품리스트';

include_once(G4_MSHOP_PATH.'/_head.php');

// 스킨을 지정했다면 지정한 스킨을 사용함 (스킨의 다양화)
//if ($skin) $ca[ca_skin] = $skin;

if ($is_admin)
    echo '<div class="sct_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/categoryform.php?w=u&amp;ca_id='.$ca_id.'" class="btn_admin">분류 관리</a></div>';
?>

<div id="sct">

    <?
    $nav_ca_id = $ca_id;
    include G4_MSHOP_PATH.'/navigation1.inc.php';

    // 상단 HTML
    echo '<div id="sct_hhtml">'.stripslashes($ca['ca_mobile_head_html']).'</div>';

    include G4_MSHOP_PATH.'/listcategory3.inc.php';

    // 상품 출력순서가 있다면
    if ($sort != "") {
        $order_by = $sort . ' '.$sortodr. ' , ';
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

    $error = '<p class="sct_noitem">등록된 상품이 없습니다.</p>';

    // 리스트 유형별로 출력
    $list_file = G4_MSHOP_PATH.'/'.$ca['ca_mobile_skin'];
    if (file_exists($list_file)) {
        $list_row   = $ca['ca_mobile_list_row'];
        $img_width  = $ca['ca_mobile_img_width'];
        $img_height = $ca['ca_mobile_img_height'];

        include G4_MSHOP_PATH.'/list.sub.php';
        include G4_MSHOP_PATH.'/list.sort.php';

        $sql = $sql_list1 . $sql_common . $sql_list2 . " limit $from_record, $items ";
        $result = sql_query($sql);

    echo '<div class="sct_wrap">';
    include $list_file;
    echo '</div>';

    }
    else
    {
        $i = 0;
        $error = '<p class="sct_nofile">'.$ca['ca_mobile_skin'].' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</p>';
    }

    if ($i==0)
    {
        echo '<div>'.$error.'</div>';
    }
    ?>

    <?php
    // 하단 HTML
    echo '<div id="sct_thtml">'.stripslashes($ca['ca_mobile_tail_html']).'</div>';
?>
</div>

<?php
$qstr1 .= 'ca_id='.$ca_id;
if($skin)
    $qstr1 .= '&amp;skin='.$skin;
$qstr1 .='&amp;ev_id='.$ev_id.'&amp;sort='.$sort.'&amp;sortodr='.$sortodr;
echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr1.'&amp;page=');
?>

<?php
include_once(G4_MSHOP_PATH.'/_tail.php');

echo "\n<!-- {$ca['ca_mobile_skin']} -->\n";
?>
