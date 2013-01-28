<?
include_once('./_common.php');

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$rand = rand(4, 6);
$norobot_key = substr($token, 0, $rand);
set_session('ss_norobot_key', $norobot_key);

// 오늘 본 상품 저장 시작
// tv 는 today view 약자
$saved = false;
$tv_idx = (int)get_session("ss_tv_idx");
if ($tv_idx > 0) {
    for ($i=1; $i<=$tv_idx; $i++) {
        if (get_session("ss_tv[$i]") == $it_id) {
            $saved = true;
            break;
        }
    }
}

if (!$saved) {
    $tv_idx++;
    set_session("ss_tv_idx", $tv_idx);
    set_session("ss_tv[$tv_idx]", $it_id);
}
// 오늘 본 상품 저장 끝

// 조회수 증가
if ($_COOKIE['ck_it_id'] != $it_id) {
    sql_query(" update {$g4['yc4_item_table']} set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    setcookie("ck_it_id", $it_id, time() + 3600, $config['cf_cookie_dir'], $config['cf_cookie_domain']); // 1시간동안 저장
}

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*,
                b.ca_name,
                b.ca_use
           from {$g4['yc4_item_table']} a,
                {$g4['yc4_category_table']} b
          where a.it_id = '$it_id'
            and a.ca_id = b.ca_id ";
$it = sql_fetch($sql);
if (!$it['it_id'])
    alert('자료가 없습니다.');
if (!($it['ca_use'] && $it['it_use'])) {
    if (!$is_admin)
        alert('판매가능한 상품이 아닙니다.');
}

// 분류 테이블에서 분류 상단, 하단 코드를 얻음
$sql = " select ca_include_head, ca_include_tail
           from {$g4['yc4_category_table']}
          where ca_id = '{$it['ca_id']}' ";
$ca = sql_fetch($sql);

$g4['title'] = "상품 상세보기 : {$it['ca_name']} - {$it['it_name']} ";

// 분류 상단 코드가 있으면 출력하고 없으면 기본 상단 코드 출력
if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it['ca_id'];
include G4_SHOP_PATH.'/navigation1.inc.php';

$himg = G4_DATA_PATH.'/item/'.$it_id.'_h';
if (file_exists($himg))
    echo '<img src="'.$himg.'" border="0"><br>';

// 상단 HTML
echo stripslashes($it['it_head_html']);

if ($is_admin)
    echo "<p align=center><a href=\"".G4_SHOP_ADMIN_URL."/itemform.php?w=u&it_id=$it_id\"><img src=\"".G4_SHOP_IMG_URL."/btn_admin_modify.gif\" border=0></a></p>";

// 이 분류에 속한 하위분류 출력
include G4_SHOP_PATH.'/listcategory.inc.php';

// 이전 상품보기
$sql = " select it_id, it_name from {$g4['yc4_item_table']}
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = "[이전상품보기] $row[it_name]";
    $prev_href = "<a href=\"./item.php?it_id={$row['it_id']}\">";
} else {
    $prev_title = "[이전상품없음]";
    $prev_href = "";
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g4['yc4_item_table']}
          where it_id < '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if ($row[it_id]) {
    $next_title = "[다음상품보기] {$row['it_name']}";
    $next_href = "<a href=\"./item.php?it_id={$row['it_id']}\">";
} else {
    $next_title = "[다음상품없음]";
    $next_href = "";
}

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from {$g4['yc4_item_relation_table']} a
           left join {$g4['yc4_item_table']} b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '{$it['it_id']}' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];

// 선택옵션 존재하는지 체크
$it_option_count = 0;
if($it['it_option_use']) {
    $sql = " select COUNT(*) as cnt from {$g4['yc4_option_table']} where it_id = '{$it['it_id']}' ";
    $row = sql_fetch($sql);
    $it_option_count = (int)$row['cnt'];
}

// 추가옵션 존재하는지 체크
$it_supplement_count = 0;
if($it['it_supplement_use']) {
    $sql = " select COUNT(*) as cnt from {$g4['yc4_supplement_table']} where it_id = '{$it['it_id']}' ";
    $row = sql_fetch($sql);
    $it_supplement_count = (int)$row['cnt'];
}
?>

<script language="JavaScript" src="<?=G4_JS_URL?>/shop.js"></script>
<script language="JavaScript" src="<?=G4_JS_URL?>/md5.js"></script>

<style type="text/css">
<!--
form { display: inline; }
ul { margin: 0; padding: 0; list-style: none; }
#option-result { display: none; }
#supplement-result { display: none; }
#total-price { display: none; }
.option-delete { cursor: pointer; }
.supplement-delete { cursor: pointer; }
.option-stock { display: none; }
.item-count input { width: 45px; text-align: right; padding-right: 5px; }
.add-item { cursor: pointer; }
.subtract-item { cursor: pointer; }
-->
</style>

<br>
<table width=99% cellpadding=0 cellspacing=0 align=center border=0><tr><td>

<?
if ($g4['https_url'])
    $action_url = G4_HTTPS_URL.'/'.$g4['shop'].'/cartupdate.php';
else
    $action_url = './cartupdate.php';
?>

<form name=fitem id="fitem" method="post" action="<?php echo $action_url?>">
<input type="hidden" name="it_id" value='<?=$it['it_id']?>'>
<input type="hidden" name="it_name" value='<?=$it['it_name']?>'>
<input type="hidden" name="submit_button" value="" />
<input type="hidden" name="total_amount" value="0" />
<table width=100% cellpadding=0 cellspacing=0>
<tr>

    <!-- 상품중간이미지 -->
    <?
    $middle_image = $it['it_id']."_m";
    ?>
    <td align=center valign=top>
        <table cellpadding=0 cellspacing=0>
            <tr><td height=22></td></tr>
            <tr><td colspan=3 align=center>
                <table cellpadding=1 cellspacing=0 bgcolor=#E4E4E4><tr><td><?=get_large_image($it['it_id']."_l1", $it['it_id'], false)?><?=get_it_image($middle_image);?></a></td></tr></table></td></tr>
            <tr><td colspan=3 height=10></td></tr>
            <tr>
                <td colspan=3 align=center>
                <?
                for ($i=1; $i<=5; $i++)
                {
                    if (file_exists(G4_DATA_PATH."/item/{$it_id}_l{$i}"))
                    {
                        echo get_large_image("{$it_id}_l{$i}", $it['it_id'], false);
                        if ($i==1 && file_exists(G4_DATA_PATH."/item/{$it_id}_m"))
                            echo "<img id='middle{$i}' src='".G4_DATA_URL."/item/{$it_id}_m' border=0 width=40 height=40 style='border:1px solid #E4E4E4;' ";
                        else
                            echo "<img id='middle{$i}' src='".G4_DATA_URL."/item/{$it_id}_l{$i}' border=0 width=40 height=40 style='border:1px solid #E4E4E4;' ";
                        echo " onmouseover=\"document.getElementById('$middle_image').src=document.getElementById('middle{$i}').src;\">";
                        echo "</a> &nbsp;";
                    }
                }
                ?>
                </td>
            </tr>
            <tr><td colspan=3 height=7></td></tr>
            <tr><td height=20><?=$prev_href?><img src='<?=G4_SHOP_IMG_URL?>/prev.gif' border=0 title='<?=$prev_title?>'></a></td>
                <td align=center><?=get_large_image($it['it_id']."_l1", $it['it_id'])?></td>
                <td align=right><?=$next_href?><img src='<?=G4_SHOP_IMG_URL?>/next.gif' border=0 title='<?=$next_title?>'></a></td></tr>
        </table>
    </td>
    <!-- 상품중간이미지 END -->

    <td width=460 valign=top align=center>
        <table width=430><tr><td colspan=2 valign=top><span style='font-size:14px; font-family:돋움;'><strong><?=it_name_icon($it, stripslashes($it['it_name']), 0)?></strong></span></td></tr></table>

        <table width=430 cellpadding=0 cellspacing=0 background='<?=G4_SHOP_IMG_URL?>/bg_item.gif'>
        <colgroup width=110></colgroup>
        <colgroup width=20></colgroup>
        <colgroup width=300></colgroup>
        <tr><td colspan=3><img src='<?=G4_SHOP_IMG_URL?>/itembox_01.gif' width=430></td></tr>


        <? if ($score = get_star_image($it['it_id'])) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 고객선호도</td>
            <td align=center>:</td>
            <td><img src='<?=G4_SHOP_IMG_URL."/star{$score}.gif"?>' border=0></td></tr>
        <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
        <? } ?>


        <? if ($it['it_maker']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 제조사</td>
            <td align=center>:</td>
            <td><?=$it['it_maker']?></td></tr>
        <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
        <? } ?>

        <? if ($it['it_brand']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 브랜드</td>
            <td align=center>:</td>
            <td><?=$it['it_brand']?></td></tr>
        <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
        <? } ?>

        <? if ($it['it_model']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 모델명</td>
            <td align=center>:</td>
            <td><?=$it['it_model']?></td></tr>
        <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
        <? } ?>

        <? if ($it['it_origin']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 원산지</td>
            <td align=center>:</td>
            <td><?=$it['it_origin']?></td></tr>
        <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
        <? } ?>

        <? if ($default['de_compound_tax_use']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 상품구분</td>
            <td align=center>:</td>
            <td><? echo $it['it_notax'] ? "면세상품" : "과세상품"; ?></td></tr>
        <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
        <? } ?>

        <? if (!$it['it_gallery']) { // 갤러리 형식이라면 가격, 구매하기 출력하지 않음 ?>

            <? if ($it['it_tel_inq']) { // 전화문의일 경우 ?>

                <tr>
                    <td height=25>&nbsp;&nbsp;&nbsp; · 판매가격</td>
                    <td align=center>:</td>
                    <td><FONT COLOR="#FF5D00">전화문의</FONT></td></tr>
                <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>

            <? } else { ?>

                <? if ($it['it_cust_amount']) { // 1.00.03 ?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 시중가격</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_cust_amount size=12 style='text-align:right; border:none; border-width:0px; font-weight:bold; width:80px; color:#777777; text-decoration:line-through;' readonly value='<?=number_format($it['it_cust_amount'])?>'> 원</td>
                </tr>
                <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
                <? } ?>


                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 판매가격</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_sell_amount size=12 style='text-align:right; border:none; border-width:0px; font-weight:bold; width:80px; font-family:Tahoma;' value="<?php echo number_format(get_amount($it)); ?>" class=amount readonly> 원
                        <input type=hidden name=it_amount value='<?php echo get_amount($it); ?>'>
                    </td>
                </tr>
                <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>

                <?
                /* 재고를 표시하는 경우 주석을 풀어주세요.
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 재고수량</td>
                    <td align=center>:</td>
                    <td><?=number_format(get_it_stock_qty($it_id))?> 개</td>
                </tr>
                <tr><td colspan=3 height=1 background='<?=$g4['shop_img_path']?>/dot_line.gif'></td></tr>
                */
                ?>

                <? if ($config['cf_use_point']) { // 포인트 사용한다면 ?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 포 인 트</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_point size=12 value="<?php echo number_format($it['it_point']); ?>" style='text-align:right; border:none; border-width:0px; width:80px;' readonly> 점
                        <input type=hidden name=it_point value='<?php echo $it['it_point']; ?>'>
                    </td>
                </tr>
                <? } ?>

                <? // 배송비 결제방법선택
                if($default['de_send_cost_case'] == "착불" || ($default['de_send_cost_case'] == "개별배송" && $it['it_sc_method'])) {
                    $pay_option = '';
                    if($default['de_send_cost_case'] == "착불") {
                        $pay_option = '<option value="착불">수령후 지불</option>';
                    } else {
                        if($it['it_sc_method'] == 1) { // 착불
                            $pay_option = '<option value="착불">수령후 지불</option>';
                        } else if($it['it_sc_method'] == 2) { // 선불 또는 착불
                            $pay_option = '<option value="선불">주문시 결제</option>'."\n";
                            $pay_option .= '<option value="착불">수령후 지불</option>';
                        }
                    }
                ?>
                <tr><td colspan=3 height=1 background='<?=G4_SHOP_IMG_URL?>/dot_line.gif'></td></tr>
                <tr height="25">
                    <td>&nbsp;&nbsp;&nbsp; · 배 송 비</td>
                    <td align=center>:</td>
                    <td>
                        <select name="ct_send_cost_pay">
                            <? echo $pay_option; ?>
                        </select>
                    </td>
                </tr>
                <?
                }
                ?>

                <?php // 선택옵션출력
                if($it['it_option_use'] && $it_option_count > 0) {
                    $disabled = '';
                    for($i = 1; $i <= 3; $i++) {
                        if($i > 1) {
                            $disabled = 'disabled';
                        }

                        $str = conv_item_options(trim($it["it_opt{$i}_subject"]), trim($it["it_opt{$i}"]), $i, $disabled);
                        if($str) {
                            echo '<tr><td colspan="3" height="1" background="'.G4_SHOP_IMG_URL.'/dot_line.gif"></td></tr>'."\n";
                            echo '<tr height="25">'."\n";
                            echo '<td>&nbsp;&nbsp;&nbsp; · <span class="opt_subject">'.$it["it_opt{$i}_subject"].'</span></td>';
                            echo '<td align="center">:</td>';
                            echo '<td style="word-break:break-all;">'.$str.'</td></tr>'."\n";
                        }
                    }
                }
                ?>

                <?php // 추가옵션출력
                if($it['it_supplement_use'] && $it_supplement_count > 0) {
                    $subject = get_supplement_subject($it_id);
                    if($subject) {
                        $index = 1;

                        foreach($subject as $value) {
                            $sp_id = $value;
                            $opt = get_supplement_option($it_id, $sp_id, $index);

                            if($opt) {
                                echo '<tr><td colspan="3" height="1" background="'.G4_SHOP_IMG_URL.'/dot_line.gif"></td></tr>'."\n";
                                echo '<tr height="25">'."\n";
                                echo '<td>&nbsp;&nbsp;&nbsp; · <span class="spl_subject">'.$value.'</span></td>';
                                echo '<td align="center">:</td>';
                                echo '<td style="word-break:break-all;">'.$opt.'</td></tr>'."\n";

                                $index++;
                            }
                        }
                    }
                }
                ?>

                <!-- / 옵션 및 가격 -->
                <tr>
                    <td colspan="3">
                        <ul id="option-result">
                            <?php
                            if(!$it['it_option_use'] || $it_option_count < 1) { // 선택옵션이 없을 경우 상품수량 표시
                                echo '<li>'."\n";
                                echo '<input type="hidden" name="is_option[]" value="0" />'."\n";
                                echo '<input type="hidden" name="opt_id[]" value="" />'."\n";
                                echo '<input type="hidden" name="ct_option[]" value="' . $it['it_name'] . '" />'."\n";
                                echo '<input type="hidden" name="ct_amount[]" value="0" />'."\n";
                                echo '<span class="option-stock">'. $it['it_stock_qty'] . '</span>'."\n";
                                echo '<span class="selected-option">' . $it['it_name'] . '</span>'."\n";
                                echo '<span class="option-price"> (+0원)</span>'."\n";
                                echo '<span class="item-count"> <input type="text" name="ct_qty[]" value="1" maxlength="4" /></span>'."\n";
                                echo '<span class="add-item"> + </span><span class="subtract-item"> - </span>'."\n";
                                echo '</li>'."\n";
                            }
                            ?>
                        </ul>
                        <ul id="supplement-result">
                        </ul>
                        <div id="total-price">총 금액 : <span></span></div>
                    </td>
                </tr>
                <tr><td colspan=3><img src='<?=G4_SHOP_IMG_URL?>/itembox_02.gif' width=430></td></tr>

            <? } ?>

        <? } ?>
        </table><BR>

        <table>
        <tr>
            <td>
            <? if (!$it['it_tel_inq'] && !$it['it_gallery']) { ?>
            <input type="submit" id="direct_buy" name="direct_buy" value="direct_buy" />
            <input type="submit" id="cart_update" name="cart_update" value="cart_update" />
            <? } ?>

            <? if (!$it['it_gallery']) { ?>
            <input type="submit" name="wish_update" value="wish_update" />
            <a href="javascript:popup_item_recommend('<?=$it['it_id']?>');"><img src='<?=G4_SHOP_IMG_URL?>/btn_item_recommend.gif' border=0></a>
            <? } ?>
            </td></tr>
        </table></td>
    </tr>
    <tr><td colspan=3 height=20></td></tr>
    <tr><td colspan=3>
        <table cellpadding=0 cellspacing=0 background='<?=G4_SHOP_IMG_URL?>/bg_tab.gif'>
        <tr>
            <td width=30></td>
            <!-- 상품정보 --><td><a href="javascript:click_item('*');"><img src='<?=G4_SHOP_IMG_URL?>/btn_tab01.gif' border=0></a></td>
            <!-- 사용후기 --><td width=109 background='<?=G4_SHOP_IMG_URL?>/btn_tab02.gif' border=0 style='padding-top:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:click_item('item_use');" style="cursor:pointer;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=small style='color:#ff5d00;'>(<span id=item_use_count>0</span>)</span></a></td>
            <!-- 상품문의 --><td width=109 background='<?=G4_SHOP_IMG_URL?>/btn_tab03.gif' border=0 style='padding-top:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:click_item('item_qa');" style="cursor:pointer;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=small style='color:#ff5d00;'>(<span id=item_qa_count>0</span>)</span></a></td>
            <? if ($default['de_baesong_content']) { ?><!-- 배송정보 --><td><a href="javascript:click_item('item_baesong');"><img src='<?=G4_SHOP_IMG_URL?>/btn_tab04.gif' border=0></a></td><?}?>
            <? if ($default['de_change_content']) { ?><!-- 교환/반품 --><td><a href="javascript:click_item('item_change');"><img src='<?=G4_SHOP_IMG_URL?>/btn_tab05.gif' border=0></a></td><?}?>
            <!-- 관련상품 --><td width=109 background='<?=G4_SHOP_IMG_URL?>/btn_tab06.gif' border=0 style='padding-top:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:click_item('item_relation');" style="cursor:pointer;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=small style='color:#ff5d00;'>(<span id=item_relation_count>0</span>)</span></a></td>
        </tr>
        </table>
</td></tr>
</table>
</form>

<!-- 상품설명 -->
<div id='item_explan' style='display:block;'>
<table width=100% cellpadding=0 cellspacing=0>
<tr><td rowspan=2 width=31 valign=top bgcolor=#CACDE2><img src='<?=G4_SHOP_IMG_URL?>/item_t01.gif'></td><td height=2 bgcolor=#CACDE2></td></tr>
<tr><td style='padding:15px'>
    <table width=100% cellspacing=0 border=0>
    <? if ($it['it_basic']) { ?>
    <tr><td height=30><font color='#3179BD'><?=$it['it_basic']?></font></td></tr>
    <? } ?>

    <? if ($it['it_explan']) { ?>
    <tr><td><div id='div_explan'><?=conv_content($it['it_explan'], 1);?></div><td></tr>
    <? } ?>
    </table>
</td></tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>
<!-- 상품설명 end -->



<?
// 사용후기
$use_page_rows = 10;    // 사용후기 페이지당 목록수
include_once('./itemuse.inc.php');


// 상품문의
$qa_page_rows = 10;     // 상품문의 페이지당 목록수
include_once('./itemqa.inc.php');
?>


<? if ($default['de_baesong_content']) { // 배송정보 내용이 있다면 ?>
<!-- 배송정보 -->
<div id='item_baesong' style='display:block;'>
<table width=100% cellpadding=0 cellspacing=0>
<tr><td rowspan=2 width=31 valign=top bgcolor=#D6E1A7><img src='<?=G4_SHOP_IMG_URL?>/item_t04.gif'></td><td height=2 bgcolor=#D6E1A7></td></tr>
<tr><td style='padding:15px' height=130><?=conv_content($default['de_baesong_content'], 1);?></td></tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>
<!-- 배송정보 end -->
<? } ?>


<? if ($default['de_change_content']) { // 교환/반품 내용이 있다면 ?>
<!-- 교환/반품 -->
<div id='item_change' style='display:block;'>
<table width=100% cellpadding=0 cellspacing=0>
<tr><td rowspan=2 width=31 valign=top bgcolor=#F6DBAB><img src='<?=G4_SHOP_IMG_URL?>/item_t05.gif'></td><td height=2 bgcolor=#F6DBAB></td></tr>
<tr><td style='padding:15px' height=130><?=conv_content($default['de_change_content'], 1);?></td></tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>
<!-- 교환/반품 end -->
<? } ?>


<!-- 관련상품 -->
<div id='item_relation' style='display:block;'>
<table width=100% cellpadding=0 cellspacing=0>
<tr><td rowspan=2 width=31 valign=top bgcolor=#E0E0E0><img src='<?=G4_SHOP_IMG_URL?>/item_t06.gif'></td><td height=2 bgcolor=#E0E0E0></td></tr>
<tr><td style='padding:15px' height=130>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr><td align=center>
        <?
        $list_mod   = $default['de_rel_list_mod'];
        $img_width  = $default['de_rel_img_width'];
        $img_height = $default['de_rel_img_height'];
        $td_width = (int)(100 / $list_mod);

        $sql = " select b.*
                   from {$g4['yc4_item_relation_table']} a
                   left join {$g4['yc4_item_table']} b on (a.it_id2=b.it_id)
                  where a.it_id = '{$it['it_id']}'
                    and b.it_use='1' ";
        $result = sql_query($sql);
        $num = @mysql_num_rows($result);
        if ($num)
            include G4_SHOP_PATH."/maintype10.inc.php";
        else
            echo "이 상품과 관련된 상품이 없습니다.";
        ?></td></tr></table></td>
</tr>
<tr><td colspan=2 height=1></td></tr>
</table>
</div>
<!-- 관련상품 end -->



</td></tr></table>


<script language="JavaScript">
$(function() {
    // 선택옵션
    var $option_select = $("select[name^=item-option-]");
    var option_count = $option_select.size();

    // 추가옵션
    var $supplement_select = $("select[name^=item-supplement-]");
    var supplement_count = $supplement_select.size();

    // 옵션이 없을 때 총 금액
    if(!option_count) {
        if($("ul#option-result").is(":hidden")) {
            $("ul#option-result").css("display", "block");
            $("#total-price").css("display", "block");
        }
        calculatePrice();
    }

    // 선택옵션이 1개일 때 옵션항목 갱신
    if(option_count == 1) {
        var opt_id = "";
        $.post(
            "./itemoptiondata.php",
            { it_id: "<? echo $it_id; ?>", opt_id: opt_id, idx: -1, showinfo: "showinfo" },
            function(data) {
                $option_select.html(data);
            }
        );
    }

    // 선택옵션선택
    $option_select.change(function() {
        var idx = $option_select.index($(this));
        var val = $(this).val();

        if((idx + 1) < option_count) {
            if(val == "") {
                $("select[name^=item-option-]:gt(" + idx + ")").val("").attr("disabled", true);
            } else {
                $("select[name^=item-option-]:gt(" + idx + ")").val("").attr("disabled", true);

                var $next_select = $option_select.eq(idx + 1);

                // 옵션정보갱신
                var opt_id = "";
                var deli = "";
                $option_select.each(function(index) {
                    if(idx < index) {
                        return false;
                    }

                    var s_val = $(this).val();
                    if(s_val != "") {
                        opt_id += deli + s_val
                    }

                    deli = chr(30);
                });

                // 마지막 직전 select 변경시 마지막 select 옵션에 가격정보 표시하도록
                var showinfo = "";
                if(idx == (option_count - 2)) {
                    showinfo = "showinfo";
                }

                $.post(
                    "./itemoptiondata.php",
                    { it_id: "<? echo $it_id; ?>", opt_id: opt_id, idx: idx, showinfo: showinfo },
                    function(data) {
                        $option_select.eq(idx + 1).html(data);
                    }
                );

                $next_select.val("");
                if($next_select.is(":disabled")) {
                    $next_select.attr("disabled", false);
                }
            }
        }

        if((idx + 1) == option_count) {
            if(val != "") {
                optionDisplay();
            }
        }
    });

    // 추가옵션선택
    $supplement_select.change(function() {
        var val = $(this).val();
        var idx = $supplement_select.index($(this));

        if(val != "") {
            var subj = $("span.spl_subject:eq("+idx+")").text();
            var sp_id = subj+chr(30)+val;
            var splcontent = "";
            var spladd = true;
            var ct_option = subj+" : "+val;

            // 선택된 옵션체크
            $("ul#supplement-result li span.selected-supplement").each(function() {
                var oldval = $(this).text();
                if(oldval == ct_option) {
                    alert("이미 선택된 옵션입니다.");
                    spladd = false;
                    return false;
                }
            });

            // 추가옵션정보
            $.post(
                "./itemsupplementinfo.php",
                { it_id: "<? echo $it_id; ?>", sp_id: sp_id },
                function(data) {
                    // 재고체크
                    if(parseInt(data.qty) < 1) {
                        alert("해당 상품은 재고가 부족하여 구매할 수 없습니다.");
                        spladd = false;
                        return false;
                    }

                    if(spladd) {
                        splcontent += "<li>";
                        splcontent += "<input type=\"hidden\" name=\"is_option[]\" value=\"2\" />";
                        splcontent += "<input type=\"hidden\" name=\"opt_id[]\" value=\""+ sp_id + "\" />";
                        splcontent += "<input type=\"hidden\" name=\"ct_option[]\" value=\""+ct_option+"\" />";
                        splcontent += "<input type=\"hidden\" name=\"ct_amount[]\" value=\"" + data.amount + "\" />";
                        splcontent += "<span class=\"option-stock\">" + data.qty + "</span>";
                        splcontent += "<span class=\"selected-supplement\">" + ct_option + "</span>";
                        splcontent += "<span class=\"supplement-price\"> (+" + number_format(String(data.amount)) + "원)</span>";
                        splcontent += "<span class=\"item-count\"> <input type=\"text\" name=\"ct_qty[]\" value=\"1\" maxlength=\"4\" /></span>";
                        splcontent += "<span class=\"add-item\"> + </span><span class=\"subtract-item\"> - </span>";
                        splcontent += "<span class=\"supplement-delete\"> 삭제</span>";
                        splcontent += "</li>";

                        if($("ul#supplement-result").is(":hidden")) {
                            $("ul#supplement-result").css("display", "block");
                            $("#total-price").css("display", "block");
                        }

                        var resultcount = $("ul#supplement-result li").size();
                        if(resultcount > 0) {
                            $("ul#supplement-result li:last").after(splcontent);
                        } else {
                            $("ul#supplement-result").html(splcontent);
                        }

                        calculatePrice();
                    }
                }, "json"
            );
        }
    });

    // 상품개수증가
    $("span.add-item").live("click", function() {
        var $cntinput = $(this).closest("li").find("input[name^=ct_qty]");
        var count = parseInt($cntinput.val());
        count++;

        // 재고체크
        var option_stock = $(this).closest("li").find("span.option-stock").text().replace(/[^0-9]/g, "");
        if(option_stock == "") {
            option_stock = 0;
        } else {
            option_stock = parseInt(option_stock);
        }

        if(option_stock < count) {
            alert("해당 상품은 " + count + "개 이상 주문할 수 없습니다.");
            $(this).val(option_stock);
            return false;
        }

        $cntinput.val(count);

        calculatePrice();
    });

    // 상품개수감소
    $("span.subtract-item").live("click", function() {
        var $cntinput = $(this).closest("li").find("input[name^=ct_qty]");
        var count = parseInt($cntinput.val());
        count--;

        if(count < 1) {
            alert("상품개수는 1이상 입력해 주십시오.");
            count = 1;
        }

        $cntinput.val(count);

        calculatePrice();
    });

    // 선택옵션삭제
    $("span.option-delete").live("click", function() {
        $(this).closest('li').remove();

        var resultcount1 = $("ul#option-result li").size();
        var resultcount2 = $("ul#supplement-result li").size();
        if(resultcount1 < 1) {
            $("ul#option-result").css("display", "none");
        }
        if(resultcount1 < 1 && resultcount2 < 1) {
            $("#total-price").css("display", "none");
        }

        calculatePrice();
    });

    // 추가옵션삭제
    $("span.supplement-delete").live("click", function() {
        $(this).closest("li").remove();

        var resultcount1 = $("ul#option-result li").size();
        var resultcount2 = $("ul#supplement-result li").size();
        if(resultcount2 < 1) {
            $("ul#supplement-result").css("display", 'none');
        }
        if(resultcount1 < 1 && resultcount2 < 1) {
            $("#total-price").css("display", "none");
        }

        calculatePrice();
    });

    $("form#fitem input:submit").click(function(e) {
        e.preventDefault();

        var parent_form = $(this).closest("form");
        var name = $(this).attr("name");
        parent_form.data("submit_button", name);
        $("input[name=submit_button]").val(name);

        $("form#fitem").submit();
    });

    // 바로구매, 장바구니, 보관하기
    $("form#fitem").submit(function() {
        var form_ok = true;

        if($(this).data("submit_button") != "wish_update") {
            // 가격체크
            if(parseInt($("input[name=it_amount]").val()) < 0) {
                alert("전화로 문의해 주시면 감사하겠습니다.");
                return false;
            }

            var option_count = $("select[name^=item-option-]").size();

            // 선택옵션체크
            if(option_count > 0 && $("ul#option-result li").size() == 0) {
                // 옵션항목별 체크
                $option_select.each(function(index) {
                    var sval = $(this).val();
                    if(sval == "") {
                        var subj = $("span.opt_subject:eq(" + index + ")").text();
                        alert(subj+"을(를) 선택해 주세요.");
                        form_ok = false;
                        return false;
                    }
                });
            }

            // 수량체크
            $("input[name^=ct_qty]").each(function() {
                var qty = $(this).val().replace(/[^0-9]/g, "");
                if(qty == "") {
                    alert("주문 수량을 입력해 주세요.");
                    form_ok = false;
                    return false;
                } else {
                    qty = parseInt(qty);
                    if(qty < 1) {
                        alert("수량은 1 이상만 가능합니다.");
                        $(this).val(1);
                        form_ok = false;
                        return false;
                    }

                    if(qty > 0 && qty > 9999) {
                        alert("수량은 9999 이하만 가능합니다.");
                        $(this).val(9999);
                        form_ok = false;
                        return false;
                    }
                }
            });
        }

        if(!form_ok) { // 에러 없으면 폼전송
            return false;
        }
    });

    // 수량변경
    $("input[name^=ct_qty]").live("keyup", function() {
        var val = $(this).val().replace(/[^0-9]/g, "");
        if(val == "") {
            //alert('구매수량을 입력해 주세요.');
            return false;
        }

        qty = parseInt(val);

        if(qty < 1) {
            alert("수량은 1이상만 가능합니다.");
            return false;
        }

        if(qty > 9999) {
            alert("수량은 9999이하만 가능합니다.");
            return false;
        }

        // 옵션재고체크
        var option_stock = $(this).closest("li").find("span.option-stock").text().replace(/[^0-9]/g, "");
        if(option_stock == "") {
            option_stock = 0;
        } else {
            option_stock = parseInt(option_stock);
        }

        if(option_stock < qty) {
            alert("해당 상품은 " + qty + "개 이상 주문할 수 없습니다.");
            $(this).val(option_stock);
        }

        calculatePrice();
    });
});

function optionDisplay()
{
    var option = "";
    var opt_id = "";
    var sep = "";
    var deli = "";
    var optionadd = true;

    $("select[name^=item-option-]").each(function(index) {
        var opt = $(this).val();
        var subj = $("span.opt_subject:eq("+index+")").text();

        option += sep + subj + " : " + opt;
        opt_id += deli + opt;

        sep = " / ";
        deli = chr(30);
    });

    // 선택된 옵션체크
    $("ul#option-result li span.selected-option").each(function() {
        var oldoption = $(this).html();

        if(oldoption == option) {
            alert("이미 선택된 옵션입니다.");
            optionadd = false;
            return false;
        }
    });

    if(optionadd) {
        // 옵션정보
        $.post(
            "./itemoptioninfo.php",
            { it_id: "<? echo $it_id; ?>", opt_id: opt_id },
            function(data) {
                if(parseInt(data.qty) < 1) {
                    alert("해당 상품은 재고가 부족하여 구매할 수 없습니다.");
                    return false;
                }

                if($("ul#option-result").is(":hidden")) {
                    $("ul#option-result").css("display", "block");
                    $("#total-price").css("display", "block");
                }

                var resultcount = $("ul#option-result li").size();
                var optioncontent = "<li>";
                optioncontent += "<input type=\"hidden\" name=\"is_option[]\" value=\"1\" />";
                optioncontent += "<input type=\"hidden\" name=\"opt_id[]\" value=\""+ opt_id + "\" />";
                optioncontent += "<input type=\"hidden\" name=\"ct_option[]\" value=\""+ option + "\" />";
                optioncontent += "<input type=\"hidden\" name=\"ct_amount[]\" value=\"" + data.amount + "\" />";
                optioncontent += "<span class=\"option-stock>" + data.qty + "</span>";
                optioncontent += "<span class=\"selected-option\">" + option + "</span>";
                optioncontent += "<span class=\"option-price\"> (+" + number_format(String(data.amount)) + "원)</span>";
                optioncontent += "<span class=\"item-count\"> <input type=\"text\" name=\"ct_qty[]\" value=\"1\" maxlength=\"4\" /></span>";
                optioncontent += "<span class=\"add-item\"> + </span><span class=\"subtract-item\"> - </span>";
                optioncontent += "<span class=\"option-delete\"> 삭제</span>";
                optioncontent += "</li>";

                if(resultcount > 0) {
                    $("ul#option-result li:last").after(optioncontent);
                } else {
                    $("ul#option-result").html(optioncontent);
                }

                calculatePrice();
            }, "json"
        );
    }
}

function calculatePrice()
{
    var itemprice = parseInt($("input[name=it_amount]").val());
    var optiontotalprice = 0;
    var spltotalprice = 0;

    $("ul#option-result li").each(function() {
        var optprc = parseInt($(this).find("input[name^=ct_amount]").val());
        var itcnt = parseInt($(this).find("input[name^=ct_qty]").val());

        optiontotalprice += (itemprice + optprc) * itcnt;
    });

    $("ul#supplement-result li").each(function() {
        var optprc = parseInt($(this).find("input[name^=ct_amount]").val());
        var itcnt = parseInt($(this).find("input[name^=ct_qty]").val());

        spltotalprice += optprc * itcnt;
    });

    $("#total-price span").text(number_format(String(optiontotalprice + spltotalprice)) + "원");
    $("input[name=total_amount]").val((optiontotalprice + spltotalprice));
}

// 추천메일
function popup_item_recommend(it_id)
{
    if (!g4_is_member)
    {
        if (confirm("회원만 추천하실 수 있습니다."))
            document.location.href = "<?=$g4[bbs_path]?>/login.php?url=<?=urlencode("$g4[shop_path]/item.php?it_id=$it_id")?>";
    }
    else
    {
        url = "./itemrecommend.php?it_id=" + it_id;
        opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
        popup_window(url, "itemrecommend", opt);
    }
}

function click_item(id)
{
    <?
    echo "var str = 'item_explan,item_use,item_qa";
    if ($default[de_baesong_content]) echo ",item_baesong";
    if ($default[de_change_content]) echo ",item_change";
    echo ",item_relation';";
    ?>

    var s = str.split(',');

    for (i=0; i<s.length; i++)
    {
        if (id=='*')
            document.getElementById(s[i]).style.display = 'block';
        else
            document.getElementById(s[i]).style.display = 'none';
    }

    if (id!='*')
        document.getElementById(id).style.display = 'block';
}

function addition_write(element_id)
{
    if (element_id.style.display == 'none') { // 안보이면 보이게 하고
        element_id.style.display = 'block';
    } else { // 보이면 안보이게 하고
        element_id.style.display = 'none';
    }
}


var save_use_id = null;
function use_menu(id)
{
    if (save_use_id != null)
        document.getElementById(save_use_id).style.display = "none";
    menu(id);
    save_use_id = id;
}

var save_qa_id = null;
function qa_menu(id)
{
    if (save_qa_id != null)
        document.getElementById(save_qa_id).style.display = "none";
    menu(id);
    save_qa_id = id;
}

if (document.getElementById("item_use_count"))
    document.getElementById("item_use_count").innerHTML = "<?=$use_total_count?>";
if (document.getElementById("item_qa_count"))
    document.getElementById("item_qa_count").innerHTML = "<?=$qa_total_count?>";
if (document.getElementById("item_relation_count"))
    document.getElementById("item_relation_count").innerHTML = "<?=$item_relation_count?>";

// 상품상세설명에 있는 이미지의 사이즈를 줄임
function explan_resize_image()
{
    var image_width = 600;
    var div_explan = document.getElementById('div_explan');
    if (div_explan) {
        var explan_img = div_explan.getElementsByTagName('img');
        for(i=0;i<explan_img.length;i++)
        {
            //document.write(explan_img[i].src+"<br>");
            img = explan_img[i];
            if (img.width) {
                imgx = parseInt(img.width);
                imgy = parseInt(img.height);
            }
            else {
                imgx = parseInt(img.style.width);
                imgy = parseInt(img.style.height);
            }
            if (imgx > image_width)
            {
                image_height = parseFloat(imgx / imgy)
                if (img.width) {
                    img.width = image_width;
                    img.height = parseInt(image_width / image_height);
                }
                else {
                    img.style.width = image_width;
                    img.style.height = parseInt(image_width / image_height);
                }
            }
            /*
            // 이미지를 가운데로 정렬하는 경우에 주석을 풀어줌
            img.style.position = 'relative';
            img.style.left = '50%';
            img.style.marginLeft = '-300px'; // image_width 의 절반
            */
        }
    }
}
</script>

<script type="text/javascript">
$(function() {
    explan_resize_image();
});
</script>

<?
// 하단 HTML
echo stripslashes($it['it_tail_html']);

$timg = G4_DATA_PATH."/item/{$it_id}_t";
if (file_exists($timg))
    echo "<img src='$timg' border=0><br>";

if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once("./_tail.php");
?>
