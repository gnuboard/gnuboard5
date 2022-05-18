<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<?php if ($is_admin) {  //관리자이면 ?>
    <div class="sit_admin"><a href="<?php echo G5_ADMIN_URL; ?>/shop_admin/configform.php#anc_scf_etc" class="btn_admin btn"><i class="fa fa-cog fa-spin fa-fw"></i><span class="sound_only">검색 설정</span></a></div>
<?php } ?>
<!-- 검색 시작 { -->
<div id="ssch">
	<h2><strong><?php echo $q; ?></strong> 검색 결과<span class="ssch_result_total">총 <?php echo $total_count; ?>건</span></h2>
    <!-- 상세검색 항목 시작 { -->
    <div id="ssch_frm">
		<div class="ssch_frm_inner">
	        <form name="frmdetailsearch">
	        <input type="hidden" name="qsort" id="qsort" value="<?php echo $qsort ?>">
	        <input type="hidden" name="qorder" id="qorder" value="<?php echo $qorder ?>">
	        <input type="hidden" name="qcaid" id="qcaid" value="<?php echo $qcaid ?>">
	        <div class="ssch_scharea">
	            <label for="ssch_q" class="sound_only" >검색어</label>
	            <input type="text" name="q" value="<?php echo $q; ?>" id="ssch_q" class="ssch_input" size="40" maxlength="30" placeholder="검색어">
	            <button type="submit" class="btn_submit"><i class="fa fa-search" aria-hidden="true"></i> 검색</button>
	            <button type="button" class="tooltip_icon"><i class="fa fa-question-circle-o" aria-hidden="true"></i><span class="sound_only">설명보기</span></button>
	            <span class="tooltip">
		            상세검색을 선택하지 않거나, 상품가격을 입력하지 않으면 전체에서 검색합니다.<br>
		            검색어는 최대 30글자까지, 여러개의 검색어를 공백으로 구분하여 입력 할수 있습니다.
		        </span>
	        </div>
	        <div class="ssch_option chk_box">
	            <strong class="sound_only">검색범위</strong>
	            <input type="checkbox" name="qname" id="ssch_qname" value="1" <?php echo $qname_check?'checked="checked"':'';?>> <label for="ssch_qname"><span></span>상품명</label>
	            <input type="checkbox" name="qexplan" id="ssch_qexplan" value="1" <?php echo $qexplan_check?'checked="checked"':'';?>> <label for="ssch_qexplan"><span></span>상품설명</label>
	            <input type="checkbox" name="qbasic" id="ssch_qbasic" value="1" <?php echo $qbasic_check?'checked="checked"':'';?>> <label for="ssch_qbasic"><span></span>기본설명</label>
	            <input type="checkbox" name="qid" id="ssch_qid" value="1" <?php echo $qid_check?'checked="checked"':'';?>> <label for="ssch_qid"><span></span>상품코드</label>
	            <strong class="sound_only">상품가격 (원)</strong>
	            <label for="ssch_qfrom" class="sound_only">최소 가격</label>
	            <input type="text" name="qfrom" value="<?php echo $qfrom; ?>" id="ssch_qfrom" class="ssch_input" size="10"> 원 ~
	            <label for="ssch_qto" class="sound_only">최대 가격</label>
	            <input type="text" name="qto" value="<?php echo $qto; ?>" id="ssch_qto" class="ssch_input" size="10"> 원
	        </div>
        	</form>
		</div>
		<!-- 검색된 분류 시작 { -->
	    <div id="ssch_cate">
	        <ul>
	        <?php
	        echo '<li><a href="#" onclick="set_ca_id(\'\'); return false;">전체분류 <span>('.$total_count.')</span></a></li>'.PHP_EOL;
            $total_cnt = 0;
	        foreach((array) $categorys as $row){
                if( empty($row) ) continue;
	            echo "<li><a href=\"#\" onclick=\"set_ca_id('{$row['ca_id']}'); return false;\">{$row['ca_name']} (".$row['cnt'].")</a></li>\n";
	            $total_cnt += $row['cnt'];
	        }
	        ?>
	        </ul>
	    </div>
	    <!-- } 검색된 분류 끝 -->

        <ul id="ssch_sort_all">
            <li><a href="#" onclick="set_sort('it_sum_qty', 'desc'); return false;">판매많은순</a></li>
            <li><a href="#" onclick="set_sort('it_price', 'asc'); return false;">낮은가격순</a></li>
            <li><a href="#" onclick="set_sort('it_price', 'desc'); return false;">높은가격순</a></li>
            <li><a href="#" onclick="set_sort('it_use_avg', 'desc'); return false;">평점높은순</a></li>
            <li><a href="#" onclick="set_sort('it_use_cnt', 'desc'); return false;">후기많은순</a></li>
            <li><a href="#" onclick="set_sort('it_update_time', 'desc'); return false;">최근등록순</a></li>
        </ul>
	    <!-- } 상세검색 항목 끝 -->
	</div>	
    <!-- 검색결과 시작 { -->
    <div>
        <?php
        // 리스트 유형별로 출력
        if (isset($list) && is_object($list) && method_exists($list, 'run')) {
            $list->set_is_page(true);
            $list->set_view('it_img', true);
            $list->set_view('it_name', true);
            $list->set_view('it_basic', true);
            $list->set_view('it_cust_price', false);
            $list->set_view('it_price', true);
            $list->set_view('it_icon', true);
            $list->set_view('sns', true);
            $list->set_view('star', true);
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

        $query_string = 'qname='.$qname.'&amp;qexplan='.$qexplan.'&amp;qid='.$qid;
        if($qfrom && $qto) $query_string .= '&amp;qfrom='.$qfrom.'&amp;qto='.$qto;
        $query_string .= '&amp;qcaid='.$qcaid.'&amp;q='.urlencode($q);
        $query_string .='&amp;qsort='.$qsort.'&amp;qorder='.$qorder;
        echo get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$query_string.'&amp;page=');
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

$(function(){
	//tooltip
    $(".tooltip_icon").click(function(){
        $(this).next(".tooltip").fadeIn(400);
    }).mouseout(function(){
        $(this).next(".tooltip").fadeOut();
    });
});

// 검색옵션
$("#ssch_sort_all li a").click(function() {
    $(this).parent().addClass('active');
});
</script>