<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<!-- 검색 시작 { -->
<div id="ssch">

    <!-- 상세검색 항목 시작 { -->
    <div id="ssch_frm">
        <h2><span><strong><?php echo $q; ?></strong> 검색결과</span> (총 <strong><?php echo $total_count; ?></strong> 건 )</h2>
        <form name="frmdetailsearch">
        <input type="hidden" name="qsort" id="qsort" value="<?php echo $qsort ?>">
        <input type="hidden" name="qorder" id="qorder" value="<?php echo $qorder ?>">
        <input type="hidden" name="qcaid" id="qcaid" value="<?php echo $qcaid ?>">
        <div>
            <strong class="sound_only">검색범위</strong>
            <input type="checkbox" name="qname" id="ssch_qname" <?php echo $qname_check?'checked="checked"':'';?>> <label for="ssch_qname">상품명</label>
            <input type="checkbox" name="qexplan" id="ssch_qexplan" <?php echo $qexplan_check?'checked="checked"':'';?>> <label for="ssch_qexplan"><span class="sound_only">상품</span>설명</label>
            <input type="checkbox" name="qbasic" id="ssch_qbasic" value="1" <?php echo $qbasic_check?'checked="checked"':'';?>> <label for="ssch_qbasic">기본설명</label>
            <input type="checkbox" name="qid" id="ssch_qid" <?php echo $qid_check?'checked="checked"':'';?>> <label for="ssch_qid"><span class="sound_only">상품</span>코드</label><br>
            <strong class="sound_only">상품가격 (원)</strong>
            <label for="ssch_qfrom" class="sound_only">최소 가격</label>
            <input type="text" name="qfrom" value="<?php echo $qfrom; ?>" id="ssch_qfrom" class="ssch_input" size="5"> 원 ~
            <label for="ssch_qto" class="sound_only">최대 가격</label>
            <input type="text" name="qto" value="<?php echo $qto; ?>" id="ssch_qto" class="ssch_input" size="5"> 원<br>
            <label for="ssch_q" class="ssch_lbl sound_only">검색어</label>
            <input type="text" name="q" value="<?php echo $q; ?>" id="ssch_q" class="ssch_input" maxlength="30">
            <input type="submit" value="검색" class="btn_submit">
        </div>
        <p>
            상세검색을 선택하지 않거나, 상품가격을 입력하지 않으면 전체에서 검색합니다.<br>
            검색어는 최대 30글자까지, 여러개의 검색어를 공백으로 구분하여 입력 할수 있습니다.
        </p>
        </form>

    </div>
    <!-- } 상세검색 항목 끝 -->

    <!-- 검색된 분류 시작 { -->
    <div id="ssch_cate">
        <ul>
        <?php
        $total_cnt = 0;
        foreach( $categorys as $row ){
            echo "<li><a href=\"#\" onclick=\"set_ca_id('{$row['ca_id']}'); return false;\">{$row['ca_name']} (".$row['cnt'].")</a></li>\n";
            $total_cnt += $row['cnt'];
        }
        echo '<li><a href="#" onclick="set_ca_id(\'\'); return false;">전체분류 <span>('.$total_cnt.')</span></a></li>'.PHP_EOL;
        ?>
        </ul>
    </div>
    <!-- } 검색된 분류 끝 -->
    <!--  상품 정렬 선택 { -->
    <div id="ssch_sort">
        <button type="button" class="btn_sort"><i class="fa fa-arrows-v" aria-hidden="true"></i> 상품정렬</button>

        <ul>

            <li><a href="#" onclick="set_sort('it_sum_qty', 'desc'); return false;">판매많은순</a></li>
            <li><a href="#" onclick="set_sort('it_price', 'asc'); return false;">낮은가격순</a></li>
            <li><a href="#" onclick="set_sort('it_price', 'desc'); return false;">높은가격순</a></li>
            <li><a href="#" onclick="set_sort('it_use_avg', 'desc'); return false;">평점높은순</a></li>
            <li><a href="#" onclick="set_sort('it_use_cnt', 'desc'); return false;">후기많은순</a></li>
            <li><a href="#" onclick="set_sort('it_update_time', 'desc'); return false;">최근등록순</a></li>
        </ul>

        </div>
    <!-- } 상품 정렬 선택  -->

    <!-- 검색결과 시작 { -->
    <div>
        <?php
        // 리스트 유형별로 출력
        define('G5_SHOP_CSS_URL', G5_MSHOP_SKIN_URL);
        $list_file = G5_MSHOP_SKIN_PATH.'/'.$default['de_mobile_search_list_skin'];
        if (file_exists($list_file)) {
            $list = new item_list($list_file, $default['de_mobile_search_list_mod'], $default['de_mobile_search_list_row'], $default['de_mobile_search_img_width'], $default['de_mobile_search_img_height']);
            $list->set_query(" select * $sql_common $sql_where {$order_by} limit $from_record, $items ");
            $list->set_is_page(true);
            $list->set_mobile(true);
            $list->set_view('it_img', true);
            $list->set_view('it_id', false);
            $list->set_view('it_name', true);
            $list->set_view('it_basic', true);
            $list->set_view('it_cust_price', false);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            echo $list->run();
        }
        else
        {
            $i = 0;
            $error = '<p class="sct_nofile">'.$list_file.' 파일을 찾을 수 없습니다.<br>관리자에게 알려주시면 감사하겠습니다.</p>';
        }

        if ($i==0)
        {
            echo '<div>'.$error.'</div>';
        }

        $query_string = 'qname='.$qname.'&amp;qexplan='.$qexplan.'&amp;qid='.$qid.'&amp;qbasic='.$qbasic;
        if($qfrom && $qto) $query_string .= '&amp;qfrom='.$qfrom.'&amp;qto='.$qto;
        $query_string .= '&amp;qcaid='.$qcaid.'&amp;q='.urlencode($q);
        $query_string .='&amp;qsort='.$qsort.'&amp;qorder='.$qorder;
        echo get_paging($config['cf_mobile_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$query_string.'&amp;page=');
        ?>
    </div>
    <!-- } 검색결과 끝 -->

</div>
<!-- } 검색 끝 -->

<script>
function set_sort(qsort, qorder)
{
    var f = document.frmdetailsearch;
    f.qsort.value = qsort;
    f.qorder.value = qorder;
    f.submit();
}

function set_ca_id(qcaid)
{
    var f = document.frmdetailsearch;
    f.qcaid.value = qcaid;
    f.submit();
}

jQuery(function($){
    $(".btn_sort").click(function(){
        $("#ssch_sort ul").show();
    });
    $(document).mouseup(function (e){
        var container = $("#ssch_sort ul");
        if( container.has(e.target).length === 0)
        container.hide();
    });
});
</script>