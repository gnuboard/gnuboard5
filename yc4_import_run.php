<?php
include_once('./_common.php');

$g4 = array();

if( isset($_REQUEST['g4']) || isset($_GET['g4']) || isset($_POST['g4']) ){
    die('잘못된 요청입니다');
}

ob_end_clean();

include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');

set_time_limit ( 0 );
ini_set('memory_limit', '100M');

$g5['title'] = '영카트4 DB 데이터 이전';
include_once(G5_PATH.'/head.sub.php');

echo '<link rel="stylesheet" href="'.G5_URL.'/g4_import.css">';

if(empty($_POST))
    alert('올바른 방법으로 이용해 주십시오.', G5_URL);

if(get_session('yc4_tables_copied') == 'done')
    alert('DB 데이터 변환을 이미 실행하였습니다. 중복 실행시 오류가 발생할 수 있습니다.', G5_URL);

if($is_admin != 'super')
    alert('최고관리자로 로그인 후 실행해 주십시오.', G5_URL);

$g4_config_file = trim($_POST['file_path']);

if(!$g4_config_file)
    alert('config.php 파일의 경로를 입력해 주십시오.');

$g4_config_file = preg_replace('#/config.php$#i', '', $g4_config_file).'/config.php';

if(!is_file($g4_config_file))
    alert('입력하신 경로에 config.php 파일이 존재하지 않습니다.');

$shop_config_file = str_replace('config.php', 'shop.config.php', $g4_config_file);

if(!is_file($shop_config_file))
    alert('입력하신 경로에 shop.config.php 파일이 존재하지 않습니다.\\nshop.config.php 파일은 config.php 파일과 동일한 위치에 있어야 합니다.');

$item_img_path = str_replace('config.php', 'data/item', $g4_config_file);

if(!file_exists($item_img_path))
    alert('상품이미지 폴더를 확인할 수 없습니다. 상품이미지 폴더의 상대경로가 '.$item_img_path.' 이 아니라면\\nyc4_import_run.php 파일에서 $item_img_path 의 값을 수정하신 후 실행해 주십시오.');

$is_euckr = false;
?>
<script>
// 새로고침 방지
function noRefresh()
{
    /* CTRL + N키 막음. */
    if ((event.keyCode == 78) && (event.ctrlKey == true))
    {
        event.keyCode = 0;
        return false;
    }
    /* F5 번키 막음. */
    if(event.keyCode == 116)
    {
        event.keyCode = 0;
        return false;
    }
}

document.onkeydown = noRefresh ;
</script>

<style>
#g4_import_run {}
#g4_import_run ol {margin: 0;padding: 0 0 0 25px;border: 1px solid #E9E9E9;border-bottom: 0;background: #f5f8f9;list-style:none;zoom:1}
#g4_import_run li {padding:7px 10px;border-bottom:1px solid #e9e9e9}
#g4_import_run #run_msg {padding:30px 0;text-align:center}
</style>

<!-- 상단 시작 { -->
<div id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/logo.jpg" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>

        <fieldset id="hd_sch">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <label for="sch_stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" id="sch_stx" maxlength="20">
            <input type="submit" id="sch_submit" value="검색">
            </form>

            <script>
            function fsearchbox_submit(f)
            {
                if (f.stx.value.length < 2) {
                    alert("검색어는 두글자 이상 입력하십시오.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
                var cnt = 0;
                for (var i=0; i<f.stx.value.length; i++) {
                    if (f.stx.value.charAt(i) == ' ')
                        cnt++;
                }

                if (cnt > 1) {
                    alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                return true;
            }
            </script>
        </fieldset>

        <ul id="tnb">
            <?php if ($is_member) {  ?>
            <?php if ($is_admin) {  ?>
            <li><a href="<?php echo G5_ADMIN_URL ?>"><b>관리자</b></a></li>
            <?php }  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php } else {  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php"><b>로그인</b></a></li>
            <?php }  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/qalist.php">1:1문의</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/current_connect.php">접속자 <?php echo connect(); // 현재 접속자수  ?></a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/new.php">새글</a></li>
        </ul>

        <div id="text_size">
            <!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
            <button id="size_down" onclick="font_resize('container', 'ts_up ts_up2', '');"><img src="<?php echo G5_URL; ?>/img/ts01.gif" alt="기본"></button>
            <button id="size_def" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up');"><img src="<?php echo G5_URL; ?>/img/ts02.gif" alt="크게"></button>
            <button id="size_up" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2');"><img src="<?php echo G5_URL; ?>/img/ts03.gif" alt="더크게"></button>
        </div>
    </div>

    <hr>

    <nav id="gnb">
        <h2>메인메뉴</h2>
        <ul id="gnb_1dul">
            <li class="gnb_empty">메뉴는 표시하지 않습니다.</li>
        </ul>
    </nav>
</div>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
    <div id="aside">
        <?php // echo outlogin('basic'); // 외부 로그인  ?>
    </div>
    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) { ?><div id="container_title"><?php echo $g5['title'] ?></div><?php } ?>

        <div id="g4_import_run">
            <ol>
        <?php
        flush();

        // yc4의 confing.php, shop.config.php
        require("./".$g4_config_file);
        require("./".$shop_config_file);

        if( $g4 && is_array($g4) ){
           foreach($g4 as $k=>$v){
               if( preg_match('/_table$/i', $k) ){
                    $g4[$k] = preg_replace('/[^0-9A-Za-z_]/', '', $v);
               }
           }
        }

        if(preg_replace('/[^a-z]/', '', strtolower($g4['charset'])) == 'euckr')
            $is_euckr = true;

        /*
        // content table 복사
        $sql = " select * from {$g4['yc4_content_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['content_table']} SET $sql_common ");
        }
        echo '<li>content table 복사</li>'.PHP_EOL;

        // new win table 복사
        $sql = " select * from {$g4['yc4_new_win_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if($key == 'nw_id')
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['new_win_table']} SET $sql_common ");
        }
        echo '<li>new win table 복사</li>'.PHP_EOL;

        // faq table 복사
        $sql = " select * from {$g4['yc4_faq_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['faq_table']} SET $sql_common ");
        }
        echo '<li>faq table 복사</li>'.PHP_EOL;

        // faq master table 복사
        $sql = " select * from {$g4['yc4_faq_master_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['faq_master_table']} SET $sql_common ");
        }
        echo '<li>faq master table 복사</li>'.PHP_EOL;
        */

        // banner table 복사
        $sql = " select * from {$g4['yc4_banner_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if($key == 'bn_id')
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_banner_table']} SET $sql_common ");
        }
        echo '<li>banner table 복사</li>'.PHP_EOL;

        // event table 복사
        $sql = " select * from {$g4['yc4_event_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if($key == 'ev_id')
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_event_table']} SET $sql_common ");
        }
        echo '<li>event table 복사</li>'.PHP_EOL;

        // event item table 복사
        $sql = " select * from {$g4['yc4_event_item_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_event_item_table']} SET $sql_common ");
        }
        echo '<li>event item table 복사</li>'.PHP_EOL;

        // item ps table 복사
        $sql = " select * from {$g4['yc4_item_ps_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if($key == 'is_id')
                    continue;

                if($key == 'is_score')
                    $val = (int)($val / 2);

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_item_use_table']} SET $sql_common ");
        }
        echo '<li>item ps table 복사</li>'.PHP_EOL;

        // item qa table 복사
        $sql = " select * from {$g4['yc4_item_qa_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if($key == 'iq_id')
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_item_qa_table']} SET $sql_common ");
        }
        echo '<li>item qa table 복사</li>'.PHP_EOL;

        // item relation table 복사
        $sql = " select * from {$g4['yc4_item_relation_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_item_relation_table']} SET $sql_common ");
        }
        echo '<li>event item table 복사</li>'.PHP_EOL;

        // category table 복사
        $sql = " select * from {$g4['yc4_category_table']} ";
        $result = sql_query($sql);
        $excl_fld = array('ca_skin', 'ca_opt1_subject', 'ca_opt2_subject', 'ca_opt3_subject', 'ca_opt4_subject', 'ca_opt5_subject', 'ca_opt6_subject');
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if(in_array($key, $excl_fld))
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_category_table']} SET $sql_common, ca_skin = 'list.10.skin.php' ");
        }
        echo '<li>category table 복사</li>'.PHP_EOL;

        // item table 복사
        $sql = " select * from {$g4['yc4_item_table']} ";
        $result = sql_query($sql);
        $excl_fld = array('it_opt1_subject', 'it_opt2_subject', 'it_opt3_subject', 'it_opt4_subject', 'it_opt5_subject', 'it_opt6_subject', 'it_opt1', 'it_opt2', 'it_opt3', 'it_opt4', 'it_opt5', 'it_opt6', 'it_amount2', 'it_amount3', 'it_gallery', 'it_explan_html');
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if(in_array($key, $excl_fld))
                    continue;

                if($key == 'it_amount')
                    $key = 'it_price';

                if($key == 'it_cust_amount')
                    $key = 'it_cust_price';

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            // 상품이미지처리
            $idx = 1;
            for($k=1; $k<=5; $k++) {
                $item_img_file = $item_img_path.'/'.$row['it_id'].'_l'.$k;
                if(is_file($item_img_file)) {
                    $size = @getimagesize($item_img_file);

                    if($size[2] < 1 || $size[2] > 16)
                        continue;

                    switch($size[2]) {
                        case 1:
                            $ext = 'gif';
                            break;
                        case 2:
                            $ext = 'jpg';
                            break;
                        case 3:
                            $ext = 'png';
                            break;
                        case 6:
                            $ext = 'bmp';
                            break;
                        default:
                            continue;
                            break;
                    }

                    // 이미지복사
                    @mkdir(G5_DATA_PATH.'/item/'.$row['it_id'], G5_DIR_PERMISSION);
                    @chmod(G5_DATA_PATH.'/item/'.$row['it_id'], G5_DIR_PERMISSION);

                    if(copy($item_img_file, G5_DATA_PATH.'/item/'.$row['it_id'].'/'.$row['it_id'].'_l'.$idx.'.'.$ext)) {
                        @chmod(G5_DATA_PATH.'/item/'.$row['it_id'].'/'.$row['it_id'].'_l'.$idx.'.'.$ext, G5_FILE_PERMISSION);
                        $sql_common .= $comma . " it_img{$idx} = '".$row['it_id'].'/'.$row['it_id'].'_l'.$idx.'.'.$ext."' ";
                        $idx++;
                    }
                }
            }

            sql_query(" INSERT INTO {$g5['g5_shop_item_table']} SET $sql_common ");

            // 사용후기의 확인된 건수를 상품테이블에 저장
            update_use_cnt($row['it_id']);

            // 사용후기의 선호도(별) 평균을 상품테이블에 저장
            update_use_avg($row['it_id']);
        }
        echo '<li>item table 복사</li>'.PHP_EOL;

        // order table 복사
        $sql = " select * from {$g4['yc4_order_table']} ";
        $result = sql_query($sql);
        $excl_fld = array('on_uid', 'od_temp_bank', 'od_temp_card', 'od_temp_hp', 'od_temp_point', 'od_receipt_card', 'od_receipt_bank', 'od_receipt_hp', 'od_bank_time', 'od_card_time', 'od_hp_time', 'od_cancel_card', 'od_dc_amount', 'od_refund_amount', 'dl_id', 'od_escrow1', 'od_escrow2', 'od_escrow3', 'od_cash_no', 'od_cash_receipt_no', 'od_cash_app_time', 'od_cash_reg_stat', 'od_cash_reg_desc', 'od_cash_tr_code', 'od_cash_id_info', 'od_cash', 'od_cash_allthegate_gubun_cd', 'od_cash_allthegate_confirm_no', 'od_cash_allthegate_adm_no', 'od_cash_tgcorp_mxissueno', 'od_cash_inicis_noappl', 'od_cash_inicis_pgauthdate', 'od_cash_inicis_pgauthtime', 'od_cash_inicis_tid', 'od_cash_inicis_ruseopt', 'od_cash_receiptnumber', 'od_cash_kspay_revatransactionno');

        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if(in_array($key, $excl_fld))
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            $od_receipt_price = $row['od_receipt_bank'] + $row['od_receipt_card'] + $row['od_receipt_hp'];
            $od_refund_price = $row['od_refund_amount'];
            $od_receipt_time = $row['od_bank_time'];
            if(!is_null_time($row['od_card_time']))
                $od_receipt_time = $row['od_card_time'];
            else if(!is_null_time($row['od_hp_time']))
                $od_receipt_time = $row['od_hp_time'];

            // 배송정보
            $od_status = '주문';
            $od_delivery_company = '';
            if($row['dl_id'] && $row['od_invoice']) {
                $dl = sql_fetch(" select dl_company from {$g4['yc4_delivery_table']} where dl_id = '{$row['dl_id']}' ");
                $od_delivery_company = addslashes($dl['dl_company']);

                $od_status = '배송';
            }

            $sql_common .= $comma . " od_receipt_price = '$od_receipt_price', od_refund_price = '$od_refund_price', od_status = '$od_status', od_delivery_company = '$od_delivery_company', od_receipt_time = '$od_receipt_time' ";

            sql_query(" INSERT INTO {$g5['g5_shop_order_table']} SET $sql_common ");

            // 장바구니자료복사
            $sql2 = " select * from {$g4['yc4_cart_table']} where on_uid = '{$row['on_uid']}' ";
            $result2 = sql_query($sql2);
            $excl_fld2 = array('ct_id', 'on_uid', 'it_opt1', 'it_opt2', 'it_opt3', 'it_opt4', 'it_opt5', 'it_opt6', 'ct_amount', 'ct_send_cost');
            for($k=0; $row2=sql_fetch_array($result2); $k++) {
                if($is_euckr)
                    $row2 = array_map('iconv_utf8', $row2);

                $comma = '';
                $sql_common2 = '';

                foreach($row2 as $key=>$val) {
                    if(in_array($key, $excl_fld2))
                        continue;

                    $sql_common2 .= $comma . " $key = '".addslashes($val)."' ";

                    $comma = ',';
                }

                $od_id = $row['od_id'];

                $ct_price = $row2['ct_amount'];

                // 상품명
                $it = sql_fetch(" select it_name from {$g5['g5_shop_item_table']} where it_id = '{$row2['it_id']}' ");
                $it_name = addslashes($it['it_name']);

                // 주문옵션
                $ct_option = '';
                $deli = '';
                for($j=1; $j<=6; $j++) {
                    if($row2['it_opt'.$j]) {
                        $ct_option .= $deli . $row2['it_opt'.$j];
                    }
                }

                if($ct_option)
                    $ct_option = addslashes($ct_option);

                $sql_common2 .= $comma . " ct_price = '$ct_price', it_name = '$it_name', ct_option = '$ct_option' ";

                sql_query(" INSERT INTO {$g5['g5_shop_cart_table']} SET od_id = '$od_id', $sql_common2 , ct_select = '1' ");
            }

            // 주문상품의 상태체크
            $cnt1 = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_cart_table']} where od_id = '$od_id' ");
            $cnt2 = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_cart_table']} where od_id = '$od_id' and ct_status = '완료' ");
            if($cnt1['cnt'] == $cnt2['cnt'] && $cnt2['cnt'] > 0)
                $od_status = '완료';

            // 미수금 등의 정보
            $info = get_order_info($od_id);

            if(!$info)
                continue;

            $sql = " update {$g5['g5_shop_order_table']}
                        set od_cart_price   = '{$info['od_cart_price']}',
                            od_cart_coupon  = '{$info['od_cart_coupon']}',
                            od_coupon       = '{$info['od_coupon']}',
                            od_send_coupon  = '{$info['od_send_coupon']}',
                            od_cancel_price = '{$info['od_cancel_price']}',
                            od_misu         = '{$info['od_misu']}',
                            od_tax_mny      = '{$info['od_tax_mny']}',
                            od_vat_mny      = '{$info['od_vat_mny']}',
                            od_free_mny     = '{$info['od_free_mny']}',
                            od_status       = '$od_status'
                        where od_id = '$od_id' ";
            sql_query($sql);
        }
        echo '<li>order table 복사</li>'.PHP_EOL;

        // wish table 복사
        $sql = " select * from {$g4['yc4_wish_table']} ";
        $result = sql_query($sql);
        for($i=0; $row=sql_fetch_array($result); $i++) {
            if($is_euckr)
                $row = array_map('iconv_utf8', $row);

            $comma = '';
            $sql_common = '';

            foreach($row as $key=>$val) {
                if($key == 'wi_id')
                    continue;

                $sql_common .= $comma . " $key = '".addslashes($val)."' ";

                $comma = ',';
            }

            sql_query(" INSERT INTO {$g5['g5_shop_wish_table']} SET $sql_common ");
        }
        echo '<li>event item table 복사</li>'.PHP_EOL;

        echo '</ol>'.PHP_EOL;

        echo '<div id="run_msg">영카트4 DB 데이터 이전 완료</div>'.PHP_EOL;

        // 실행완료 세션에 기록
        set_session('yc4_tables_copied', 'done');
        ?>
        </div>

    </div>
</div>

<!-- } 콘텐츠 끝 -->

<hr>

<!-- 하단 시작 { -->
<div id="ft">
    <div id="ft_catch"><img src="<?php echo G5_IMG_URL; ?>/ft.png" alt="<?php echo G5_VERSION ?>"></div>
    <div id="ft_copy">
        <p>
            Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
            <a href="#">상단으로</a>
        </p>
    </div>
</div>

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');