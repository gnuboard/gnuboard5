<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/list.php');
    return;
}

$sql = " select *
           from {$g4['shop_category_table']}
          where ca_id = '$ca_id'
            and ca_use = '1'  ";
$ca = sql_fetch($sql);
if (!$ca['ca_id'])
    alert('등록된 분류가 없습니다.');

if(!$is_admin) {
    // 본인확인체크
    if($ca['ca_hp_cert_use'] && !$member['mb_hp_certify']) {
        if($is_member)
            alert('회원정보 수정에서 휴대폰 본인확인 후 이용해 주십시오.');
        else
            alert('휴대폰 본인확인된 로그인 회원만 이용할 수 있습니다.');
    }

    // 성인인증체크
    if($ca['ca_adult_cert_use'] && !$member['mb_adult']) {
        if($is_member)
            alert('휴대폰 본인확인으로 성인인증된 회원만 이용할 수 있습니다.\\n회원정보 수정에서 휴대폰 본인확인을 해주십시오.');
        else
            alert('휴대폰 본인확인으로 성인인증된 회원만 이용할 수 있습니다.');
    }
}

$g4['title'] = $ca['ca_name'].' 상품리스트';

if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 스킨을 지정했다면 지정한 스킨을 사용함 (스킨의 다양화)
//if ($skin) $ca[ca_skin] = $skin;

if ($is_admin)
    echo '<div class="sct_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/categoryform.php?w=u&amp;ca_id='.$ca_id.'" class="btn_admin">분류 관리</a></div>';
?>

<!-- 상품 목록 시작 { -->
<div id="sct">

    <?php
    $nav_ca_id = $ca_id;
    include G4_SHOP_PATH.'/navigation1.inc.php';

    // 상단 이미지
    $himg = G4_DATA_PATH.'/category/'.$ca_id.'_h';
    if (file_exists($himg)) {
        echo '<div id="sct_himg" class="sct_img"><img src="'.G4_DATA_URL.'/category/'.$ca_id.'_h" alt=""></div>';
    }

    // 상단 HTML
    echo '<div id="sct_hhtml">'.stripslashes($ca['ca_head_html']).'</div>';

    include G4_SHOP_PATH.'/listcategory3.inc.php';

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

    echo '<div class="sct_wrap">';
    include $list_file;
    echo '</div>';

    }
    else
    {
        $i = 0;
        $error = '<p class="sct_nofile">'.$ca['ca_skin'].' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</p>';
    }

    if ($i==0)
    {
        echo '<div>'.$error.'</div>';
    }
    ?>

    <?php
    // 하단 HTML
    echo '<div id="sct_thtml">'.stripslashes($ca['ca_tail_html']).'</div>';

    // 하단 이미지
    $timg = G4_DATA_PATH.'/category/'.$ca_id.'_t';
    if (file_exists($timg))
        echo '<div id="sct_timg" class="sct_img"><img src="'.G4_DATA_URL.'/category/'.$ca_id.'_t" alt="">';
?>
</div>

<?php
$qstr1 .= 'ca_id='.$ca_id;
if($skin)
    $qstr1 .= '&amp;skin='.$skin;
$qstr1 .='&amp;ev_id='.$ev_id.'&amp;sort='.$sort.'&amp;sortodr='.$sortodr;
echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr1.'&amp;page=');
?>
<!-- } 상품 목록 끝 -->

<?php
if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once('./_tail.php');

echo "\n<!-- {$ca['ca_skin']} -->\n";
?>
