<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/personalpay.php');
    return;
}

$g5['title'] = '개인결제 리스트';
include_once('./_head.php');
?>

<!-- 상품 목록 시작 { -->
<div id="sct">

    <?php
    // 리스트 유형별로 출력
    $list_file = G5_SHOP_SKIN_PATH.'/personalpay.skin.php';
    if (file_exists($list_file)) {

        $list_mod   = 5;
        $list_row   = 5;
        $img_width  = 225;
        $img_height = 225;

        $sql_common = " from {$g5['g5_shop_personalpay_table']}
                        where pp_use = '1'
                          and pp_tno = '' ";

        // 총몇개 = 한줄에 몇개 * 몇줄
        $items = $list_mod * $list_row;

        $sql = "select COUNT(*) as cnt $sql_common ";
        $row = sql_fetch($sql);
        $total_count = $row['cnt'];

        // 전체 페이지 계산
        $total_page  = ceil($total_count / $items);
        // 페이지가 없으면 첫 페이지 (1 페이지)
        if ($page < 1) $page = 1;
        // 시작 레코드 구함
        $from_record = ($page - 1) * $items;

        $sql = " select *
                    $sql_common
                    order by pp_id desc
                    limit $from_record, $items";
        $result = sql_query($sql);

        include $list_file;
    }
    else
    {
        $i = 0;
        $error = '<p class="sct_nofile">personalpay.skin.php 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</p>';
    }

    if ($i==0)
    {
        echo '<p class="sct_noitem">등록된 개인결제가 없습니다.</p>';
    }
    ?>

    <?php
    echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page=');
    ?>
</div>
<!-- } 상품 목록 끝 -->

<?php
include_once('./_tail.php');