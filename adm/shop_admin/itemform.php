<?php
$sub_menu = '400300';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');

auth_check($auth[$sub_menu], "w");

// it_id type 수정
$sql = " SHOW COLUMNS FROM `{$g4['shop_item_table']}` WHERE field = 'it_id' ";
$row = sql_fetch($sql);
if(intval(preg_replace("/[^0-9]/", "", $row['Type'])) != 20) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_cart_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_qa_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_ps_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_relation_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_item_relation_table']}` MODIFY COLUMN it_id2 VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_event_item_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
    sql_query(" ALTER TABLE `{$g4['shop_wish_table']}` MODIFY COLUMN it_id VARCHAR(20) NOT NULL DEFAULT '' ", false);
}

// 상품요약정보 필드추가
$sql = " select it_info_gubun from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}` ADD `it_info_gubun` VARCHAR(50) NOT NULL DEFAULT '' AFTER `it_tel_inq`,
                    ADD `it_info_value` TEXT NOT NULL AFTER `it_info_gubun` ", false);
}

// 상품이미지 필드추가
$sql = " select it_img1 from {$g4['shop_item_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_img1` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_info_value`,
                    ADD `it_img2` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img1`,
                    ADD `it_img3` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img2`,
                    ADD `it_img4` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img3`,
                    ADD `it_img5` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img4`,
                    ADD `it_img6` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img5`,
                    ADD `it_img7` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img6`,
                    ADD `it_img8` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img7`,
                    ADD `it_img9` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img8`,
                    ADD `it_img10` VARCHAR(255) NOT NULL DEFAULT '' AFTER `it_img9` ", false);
}

// 관련상품 정렬을 위한 ir_no 필드 추가
$sql = " select ir_no from {$g4['shop_item_relation_table']} limit 1 ";
$result = sql_query($sql, false);
if(!$result) {
    sql_query(" ALTER TABLE `{$g4['shop_item_relation_table']}`
                    ADD `ir_no` INT(11) NOT NULL DEFAULT '0' AFTER `it_id2` ", false);
}

$html_title = "상품 ";

if ($w == "")
{
    $html_title .= "입력";

    // 옵션은 쿠키에 저장된 값을 보여줌. 다음 입력을 위한것임
    //$it[ca_id] = _COOKIE[ck_ca_id];
    $it['ca_id'] = get_cookie("ck_ca_id");
    $it['ca_id2'] = get_cookie("ck_ca_id2");
    $it['ca_id3'] = get_cookie("ck_ca_id3");
    if (!$it['ca_id'])
    {
        $sql = " select ca_id from {$g4['shop_category_table']} order by ca_id limit 1 ";
        $row = sql_fetch($sql);
        if (!$row['ca_id'])
            alert("등록된 분류가 없습니다. 우선 분류를 등록하여 주십시오.");
        $it['ca_id'] = $row['ca_id'];
    }
    //$it[it_maker]  = stripslashes($_COOKIE[ck_maker]);
    //$it[it_origin] = stripslashes($_COOKIE[ck_origin]);
    $it['it_maker']  = stripslashes(get_cookie("ck_maker"));
    $it['it_origin'] = stripslashes(get_cookie("ck_origin"));
}
else if ($w == "u")
{
    $html_title .= "수정";

    if ($is_admin != 'super')
    {
        $sql = " select it_id from {$g4['shop_item_table']} a, {$g4['shop_category_table']} b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['it_id'])
            alert("\'{$member['mb_id']}\' 님께서 수정 할 권한이 없는 상품입니다.");
    }

    $sql = " select * from {$g4['shop_item_table']} where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    if (!$ca_id)
        $ca_id = $it['ca_id'];

    $sql = " select * from {$g4['shop_category_table']} where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);
}
else
{
    alert();
}

if (!$it['it_explan_html'])
{
    $it['it_explan'] = get_text($it['it_explan'], 1);
}

if (!isset($it['it_mobile_explan'])) {
    sql_query(" ALTER TABLE `{$g4['shop_item_table']}`
                    ADD `it_mobile_explan` TEXT NOT NULL AFTER `it_explan`,
                    ADD `it_mobile_head_html` TEXT NOT NULL AFTER `it_tail_html`,
                    ADD `it_mobile_tail_html` TEXT NOT NULL AFTER `it_mobile_head_html` ", false);
}

//$qstr1 = 'sel_ca_id='.$sel_ca_id.'&amp;sel_field='.$sel_field.'&amp;search='.$search;
//$qstr = $qstr1.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2.'&amp;page='.$page;
$qstr  = $qstr.'&amp;sca='.$sca.'&amp;page='.$page;

$g4['title'] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');

// 분류리스트
$category_select = '';
$script = '';
$sql = " select * from {$g4['shop_category_table']} ";
if ($is_admin != 'super')
    $sql .= " where ca_mb_id = '{$member['mb_id']}' ";
$sql .= " order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $len = strlen($row['ca_id']) / 2 - 1;

    $nbsp = "";
    for ($i=0; $i<$len; $i++)
        $nbsp .= "&nbsp;&nbsp;&nbsp;";

    $category_select .= "<option value=\"{$row['ca_id']}\">$nbsp{$row['ca_name']}</option>\n";

    $script .= "ca_use['{$row['ca_id']}'] = {$row['ca_use']};\n";
    $script .= "ca_stock_qty['{$row['ca_id']}'] = {$row['ca_stock_qty']};\n";
    //$script .= "ca_explan_html['$row[ca_id]'] = $row[ca_explan_html];\n";
    $script .= "ca_sell_email['{$row['ca_id']}'] = '{$row['ca_sell_email']}';\n";
    $script .= "ca_opt1_subject['{$row['ca_id']}'] = '{$row['ca_opt1_subject']}';\n";
    $script .= "ca_opt2_subject['{$row['ca_id']}'] = '{$row['ca_opt2_subject']}';\n";
    $script .= "ca_opt3_subject['{$row['ca_id']}'] = '{$row['ca_opt3_subject']}';\n";
    $script .= "ca_opt4_subject['{$row['ca_id']}'] = '{$row['ca_opt4_subject']}';\n";
    $script .= "ca_opt5_subject['{$row['ca_id']}'] = '{$row['ca_opt5_subject']}';\n";
    $script .= "ca_opt6_subject['{$row['ca_id']}'] = '{$row['ca_opt6_subject']}';\n";
}

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_sitfrm_cate">상품분류</a></li>
<li><a href="#anc_sitfrm_ini">기본정보</a></li>
<li><a href="#anc_sitfrm_compact">요약정보</a></li>
<li><a href="#anc_sitfrm_cost">가격 및 재고</a></li>
<li><a href="#anc_sitfrm_img">상품이미지</a></li>
<li><a href="#anc_sitfrm_relation">관련상품</a></li>
<li><a href="#anc_sitfrm_event">관련이벤트</a></li>
<li><a href="#anc_sitfrm_optional">상세설명설정</a></li>
</ul>
';
?>

<form name="fitemform" action="./itemformupdate.php" method="post" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="return fitemformcheck(this)">

<input type="hidden" name="codedup" value="<?php echo $default['de_code_dup_use']; ?>">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<!-- <input type="hidden" name="sel_ca_id" value="<?php echo $sel_ca_id; ?>">
<input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
<input type="hidden" name="search" value="<?php echo $search; ?>">
<input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
<input type="hidden" name="sort2" value="<?php echo $sort2; ?>"> -->
<input type="hidden" name="sca" value="<?php echo $sca; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod"  value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx"  value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="it_explan_html" value="1"><!---->

<section id="anc_sitfrm_cate" class="cbox">
    <h2>상품분류</h2>
    <?php echo $pg_anchor; ?>
    <p>기본분류는 반드시 선택하셔야 합니다. 하나의 상품에 최대 3개의 다른 분류를 지정할 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <?php // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-19 ?>
    <tr>
        <th scope="row"><label for="ca_id">기본분류</label></th>
        <td>
            <?php if ($w == "") echo help("기본분류를 선택하면, 판매/재고/HTML사용/판매자 E-mail 등을, 선택한 분류의 기본값으로 설정합니다."); ?>
            <select name="ca_id" id="ca_id" onchange="categorychange(this.form)">
                <option value="">선택하세요</option>
                <?php echo conv_selected_option($category_select, $it['ca_id']); ?>
            </select>
            <script>
                var ca_use = new Array();
                var ca_stock_qty = new Array();
                //var ca_explan_html = new Array();
                var ca_sell_email = new Array();
                var ca_opt1_subject = new Array();
                var ca_opt2_subject = new Array();
                var ca_opt3_subject = new Array();
                var ca_opt4_subject = new Array();
                var ca_opt5_subject = new Array();
                var ca_opt6_subject = new Array();
                <?php echo "\n$script"; ?>
            </script>
        </td>
    </tr>
    <?php for ($i=2; $i<=3; $i++) { ?>
    <tr>
        <th scope="row"><label for="ca_id<?php echo $i; ?>"><?php echo $i; ?>차 분류</label></th>
        <td>
            <?php echo help($i.'차 분류는 기본 분류의 하위 분류 개념이 아니므로 기본 분류 선택시 해당 상품이 포함될 최하위 분류만 선택하시면 됩니다.'); ?>
            <select name="ca_id<?php echo $i; ?>" id="ca_id<?php echo $i; ?>">
                <option value="">선택하세요</option>
                <?php echo conv_selected_option($category_select, $it['ca_id'.$i]); ?>
            </select>
        </td>
    </tr>
    <?php } ?>
    <?php // ##### // 웹 접근성 취약 지점 끝 ?>
    </tbody>
    </table>
</section>

<section id="anc_sitfrm_ini" class="cbox">
    <h2>기본정보</h2>
    <?php echo $pg_anchor; ?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">상품코드</th>
        <td colspan="2">
            <?php if ($w == '') { // 추가 ?>
                <!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
                <!-- <input type=text class=required name=it_id value="<?php echo 10000000000-time()?>" size=12 maxlength=10 required> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
                <?php echo help("상품의 코드는 10자리 숫자로 자동생성합니다. <b>직접 상품코드를 입력할 수도 있습니다.</b>\n상품코드는 영문자, 숫자, - 만 입력 가능합니다."); ?>
                <input type="text" name="it_id" value="<?php echo time(); ?>" id="it_id" required class="frm_input required" size="20" maxlength="20">
                <?php if ($default['de_code_dup_use']) { ?><a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src="<?php echo G4_ADMIN_URL; ?>/img/btn_code.gif"></a><?php } ?>
            <?php } else { ?>
                <input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
                <span class="frm_ca_id"><?php echo $it['it_id']; ?></span>
                <a href="<?php echo G4_SHOP_URL; ?>/item.php?it_id=<?php echo $it_id; ?>" class="btn_frmline">상품확인</a>
                <a href="<?php echo G4_ADMIN_URL; ?>/shop_admin/itempslist.php?sel_field=a.it_id&amp;search=<?php echo $it_id; ?>" class="btn_frmline">사용후기</a>
                <a href="<?php echo G4_ADMIN_URL; ?>/shop_admin/itemqalist.php?sel_field=a.it_id&amp;search=<?php echo $it_id; ?>" class="btn_frmline">상품문의</a>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_name">상품명</label></th>
        <td colspan="2">
            <input type="text" name="it_name" value="<?php echo get_text(cut_str($it['it_name'], 250, "")); ?>" id="it_name" required class="frm_input required" size="95">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_gallery">전시용 상품</label></th>
        <td>
           <?php echo help("이 항목을 체크하면 상품을 전시만 하고, 판매하지 않습니다."); ?>
            <input type="checkbox" name="it_gallery" value="1" id="it_gallery" <?php echo ($it['it_gallery'] ? "checked" : ""); ?>>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_gallery" value="1" id="chk_ca_it_gallery">
            <label for="chk_ca_it_gallery">분류적용</label>
            <input type="checkbox" name="chk_all_it_gallery" value="1" id="chk_all_it_gallery">
            <label for="chk_all_it_gallery">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_order">출력순서</label></th>
        <td>
            <?php echo help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>"); ?>
            <input type="text" name="it_order" value="<?php echo $it['it_order']; ?>" id="it_order" class="frm_input" size="12">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_order" value="1" id="chk_ca_it_order">
            <label for="chk_ca_it_order">분류적용</label>
            <input type="checkbox" name="chk_all_it_order" value="1" id="chk_all_it_order">
            <label for="chk_all_it_order">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">상품유형</th>
        <td>
            <?php echo help("메인화면에 유형별로 출력할때 사용합니다.\n이곳에 체크하게되면 상품리스트에서 유형별로 정렬할때 체크된 상품이 가장 먼저 출력됩니다."); ?>
            <input type="checkbox" name="it_type1" value="1" <?php echo ($it['it_type1'] ? "checked" : ""); ?> id="it_type1">
            <label for="it_type1">히트 <img src="<?php echo G4_URL; ?>/img/shop/icon_hit2.gif" alt=""></label>
            <input type="checkbox" name="it_type2" value="1" <?php echo ($it['it_type2'] ? "checked" : ""); ?> id="it_type2">
            <label for="it_type2">추천 <img src="<?php echo G4_URL; ?>/img/shop/icon_rec2.gif" alt=""></label>
            <input type="checkbox" name="it_type3" value="1" <?php echo ($it['it_type3'] ? "checked" : ""); ?> id="it_type3">
            <label for="it_type3">신상품 <img src="<?php echo G4_URL; ?>/img/shop/icon_new2.gif" alt=""></label>
            <input type="checkbox" name="it_type4" value="1" <?php echo ($it['it_type4'] ? "checked" : ""); ?> id="it_type4">
            <label for="it_type4">인기 <img src="<?php echo G4_URL; ?>/img/shop/icon_best2.gif" alt=""></label>
            <input type="checkbox" name="it_type5" value="1" <?php echo ($it['it_type5'] ? "checked" : ""); ?> id="it_type5">
            <label for="it_type5">할인 <img src="<?php echo G4_URL; ?>/img/shop/icon_discount2.gif" alt=""></label>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_type" value="1" id="chk_ca_it_type">
            <label for="chk_ca_it_type">분류적용</label>
            <input type="checkbox" name="chk_all_it_type" value="1" id="chk_all_it_type">
            <label for="chk_all_it_type">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_maker">제조사</label></th>
        <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_maker" value="<?php echo get_text($it['it_maker']); ?>" id="it_maker" class="frm_input" size="40">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_maker" value="1" id="chk_ca_it_maker">
            <label for="chk_ca_it_maker">분류적용</label>
            <input type="checkbox" name="chk_all_it_maker" value="1" id="chk_all_it_maker">
            <label for="chk_all_it_maker">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_origin">원산지</label></th>
        <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_origin" value="<?php echo get_text($it['it_origin']); ?>" id="it_origin" class="frm_input" size="40">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_origin" value="1" id="chk_ca_it_origin">
            <label for="chk_ca_it_origin">분류적용</label>
            <input type="checkbox" name="chk_all_it_origin" value="1" id="chk_all_it_origin">
            <label for="chk_all_it_origin">전체적용</label>
        </td>
    </tr>
    <?php
    for ($i=1; $i<=3; $i++) {
        $k1=$i*2-1;
        $k2=$i*2;
        $val11 = stripslashes($it["it_opt".$k1."_subject"]);
        $val12 = stripslashes($it["it_opt".$k1]);
        $val21 = stripslashes($it["it_opt".$k2."_subject"]);
        $val22 = stripslashes($it["it_opt".$k2]);
    ?>
    <tr>
        <th scope="row">
            <label for="it_opt<?php echo $k1; ?>_subject">상품옵션명 <?php echo $k1; ?></label><br>
            <input type="text" name="it_opt<?php echo $k1; ?>_subject" value="<?php echo get_text($val11); ?>" id="it_opt<?php echo $k1; ?>_subject" class="frm_input" size="15">
        </th>
        <td colspan="2">
            <label for="it_opt<?php echo $k1; ?>" class="sound_only">상품옵션설정 <?php echo $k1; ?></label>
            <textarea name="it_opt<?php echo $k1; ?>" id="it_opt<?php echo $k1; ?>" class="sit_w_opt"><?php echo $val12; ?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="it_opt<?php echo $k2; ?>_subject">상품옵션명 <?php echo $k2; ?></label><br>
            <input type="text" name="it_opt<?php echo $k2; ?>_subject" value="<?php echo get_text($val21); ?>" id="it_opt<?php echo $k2; ?>_subject" class="frm_input" size="15">
        </th>
        <td colspan="2">
            <label for="it_opt<?php echo $k1; ?>" class="sound_only">상품옵션설정 <?php echo $k2; ?></label>
            <textarea name="it_opt<?php echo $k2; ?>" id="it_opt<?php echo $k2; ?>" class="sit_w_opt"><?php echo $val22; ?></textarea>
        </td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row"><label for="it_basic">기본설명</label></th>
        <td>
            <?php echo help("상품상세페이지의 상품설명 상단에 표시되는 설명입니다. HTML 입력도 가능합니다."); ?>
            <input type="text" name="it_basic" value="<?php echo get_text($it['it_basic']); ?>" id="it_basic" class="frm_input" size="80">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_basic" value="1" id="chk_ca_it_basic">
            <label for="chk_ca_it_basic">분류적용</label>
            <input type="checkbox" name="chk_all_it_basic" value="1" id="chk_all_it_basic">
            <label for="chk_all_it_basic">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">상품설명</th>
        <td colspan="2"> <?php echo editor_html('it_explan', $it['it_explan']); ?></td>
    </tr>
    <tr>
        <th scope="row">모바일 상품설명</th>
        <td colspan="2"> <?php echo editor_html('it_mobile_explan', $it['it_mobile_explan']); ?></td>
    </tr>
    <tr>
        <th scope="row"><label for="it_sell_email">판매자 e-mail</label></th>
        <td>
            <?php echo help("운영자와 실제 판매자가 다른 경우 실제 판매자의 e-mail을 입력하면, 상품 주문 시점을 기준으로 실제 판매자에게도 주문서를 발송합니다."); ?>
            <input type="text" name="it_sell_email" value="<?php echo $it['it_sell_email']; ?>" id="it_sell_email" class="frm_input" size="40">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_sell_email" value="1" id="chk_ca_it_sell_email">
            <label for="chk_ca_it_sell_email">분류적용</label>
            <input type="checkbox" name="chk_all_it_sell_email" value="1" id="chk_all_it_sell_email">
            <label for="chk_all_it_sell_email">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_tel_inq">전화문의</label></th>
        <td>
            <?php echo help("상품 금액 대신 전화문의로 표시됩니다."); ?>
            <input type="checkbox" name="it_tel_inq" value="1" id="it_tel_inq" <?php echo ($it['it_tel_inq']) ? "checked" : ""; ?>> 예
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_tel_inq" value="1" id="chk_ca_it_tel_inq">
            <label for="chk_ca_it_tel_inq">분류적용</label>
            <input type="checkbox" name="chk_all_it_tel_inq" value="1" id="chk_all_it_tel_inq">
            <label for="chk_all_it_tel_inq">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_use">판매가능</label></th>
        <td>
            <?php echo help("잠시 판매를 중단하거나 재고가 없을 경우에 체크를 해제해 놓으면 출력되지 않으며, 주문도 받지 않습니다."); ?>
            <input type="checkbox" name="it_use" value="1" id="it_use" <?php echo ($it['it_use']) ? "checked" : ""; ?>> 예
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_use" value="1" id="chk_ca_it_use">
            <label for="chk_ca_it_use">분류적용</label>
            <input type="checkbox" name="chk_all_it_use" value="1" id="chk_all_it_use">
            <label for="chk_all_it_use">전체적용</label>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="anc_sitfrm_compact" class="cbox">
    <h2>상품요약정보</h2>
    <?php echo $pg_anchor; ?>
    <p><strong>전자상거래 등에서의 상품 등의 정보제공에 관한 고시</strong>에 따라 총 35개 상품군에 대해 상품 특성 등을 양식에 따라 입력할 수 있습니다.</p>

    <div id="sit_compact">
        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="it_info_gubun">상품군</label></th>
            <td>
                <?php echo help("상품군을 선택하면 자동으로 항목이 변환됩니다."); ?>
                <select id="it_info_gubun" name="it_info_gubun">
                    <option value="">상품군을 선택하세요.</option>
                    <?php
                    if(!$it['it_info_gubun']) $it['it_info_gubun'] = 'wear';
                    foreach($item_info as $key=>$value) {
                        $opt_value = $key;
                        $opt_text  = $value['title'];
                        echo '<option value="'.$opt_value.'" '.get_selected($opt_value, $it['it_info_gubun']).'>'.$opt_text.'</option>'.PHP_EOL;
                    }
                    ?>
                </select>
            </td>
        </tr>
        </tbody>
        </table>
        <div id="sit_compact_fields"><?php include_once(G4_ADMIN_PATH.'/shop_admin/iteminfo.php'); ?></div>
        <div id="sit_compact_grpset">
            <input type="checkbox" name="chk_ca_it_info" value="1" id="chk_ca_it_info">
            <label for="chk_ca_it_info">분류적용</label>
            <input type="checkbox" name="chk_all_it_info" value="1" id="chk_all_it_info">
            <label for="chk_all_it_info">전체적용</label>
        </div>
        <span id="sit_compact_line"></span>
    </div>
</section>

<script>
$(function(){
    $("#it_info_gubun").live("change", function() {
        var gubun = $(this).val();
        $.post(
            "<?php echo G4_ADMIN_URL; ?>/shop_admin/iteminfo.php",
            { it_id: "<?php echo $it['it_id']; ?>", gubun: gubun },
            function(data) {
                $("#sit_compact_fields").empty().html(data);
            }
        );
    });
});
</script>

<section id="anc_sitfrm_cost" class="cbox">
    <h2>가격 및 재고</h2>
    <?php echo $pg_anchor; ?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="it_price">판매가격</label></th>
        <td>
            <input type="text" name="it_price" value="<?php echo $it['it_price']; ?>" id="it_price" class="frm_input" size="8"> 원
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_price" value="1" id="chk_ca_it_price">
            <label for="chk_ca_it_price">분류적용</label>
            <input type="checkbox" name="chk_all_it_price" value="1" id="chk_all_it_price">
            <label for="chk_all_it_price">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_cust_price">시중가격</label></th>
        <td>
            <?php echo help("입력하지 않으면 상품상세페이지에 출력하지 않습니다."); ?>
            <input type="text" name="it_cust_price" value="<?php echo $it['it_cust_price']; ?>" id="it_cust_price" class="frm_input" size="8"> 원
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_cust_price" value="1" id="chk_ca_it_cust_price">
            <label for="chk_ca_it_cust_price">분류적용</label>
            <input type="checkbox" name="chk_all_it_cust_price" value="1" id="chk_all_it_cust_price">
            <label for="chk_all_it_cust_price">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_point">포인트</label></th>
        <td>
            <?php echo help("주문완료후 환경설정에서 설정한 주문완료 설정일 후 회원에게 부여하는 포인트입니다.\n또, 포인트부여를 '아니오'로 설정한 경우 신용카드, 계좌이체로 주문하는 회원께는 부여하지 않습니다.\n포인트 기능을 사용해야 동작합니다."); ?>
            <input type="text" name="it_point" value="<?php echo $it['it_point']; ?>" id="it_point" class="frm_input" size="8"> 점
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_point" value="1" id="chk_ca_it_point">
            <label for="chk_ca_it_point">분류적용</label>
            <input type="checkbox" name="chk_all_it_point" value="1" id="chk_all_it_point">
            <label for="chk_all_it_point">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_stock_qty">재고수량</label></th>
        <td>
            <?php echo help("<b>주문관리에서 상품별 상태 변경에 따라 자동으로 재고를 가감합니다.</b> 재고는 규격/색상별이 아닌, 상품별로만 관리됩니다."); ?>
            <input type="text" name="it_stock_qty" value="<?php echo $it['it_stock_qty']; ?>" id="it_stock_qty" class="frm_input" size="8"> 개</span>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_stock_qty" value="1" id="chk_ca_it_stock_qty">
            <label for="chk_ca_it_stock_qty">분류적용</label>
            <input type="checkbox" name="chk_all_it_stock_qty" value="1" id="chk_all_it_stock_qty">
            <label for="chk_all_it_stock_qty">전체적용</label>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="anc_sitfrm_img" class="cbox">
    <h2>이미지</h2>
    <?php echo $pg_anchor; ?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <?php for($i=1; $i<=10; $i++) { ?>
    <tr>
        <th scope="row"><label for="it_img1">이미지 <?php echo $i; ?></label></th>
        <td>
            <input type="file" name="it_img<?php echo $i; ?>" id="it_img<?php echo $i; ?>">
            <?php
            $it_img = G4_DATA_PATH.'/item/'.$it['it_img'.$i];
            if(is_file($it_img) && $it['it_img'.$i]) {
                $size = @getimagesize($it_img);
            ?>
            <label for="it_img<?php echo $i; ?>_del"><span class="sound_only">이미지 <?php echo $i; ?> </span>파일삭제</label>
            <input type="checkbox" name="it_img<?php echo $i; ?>_del" id="it_img<?php echo $i; ?>_del" value="1">
            <span class="sit_wimg_limg<?php echo $i; ?>"></span>
            <div id="limg<?php echo $i; ?>" class="banner_or_img">
                <img src="<?php echo G4_DATA_URL; ?>/item/<?php echo $it['it_img'.$i]; ?>" alt="" width="<?php echo $size[0]; ?>" height="<?php echo $size[1]; ?>">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="it_limg<?php echo $i; ?>_view" class="btn_frmline sit_wimg_view">이미지<?php echo $i; ?> 확인</button>').appendTo('.sit_wimg_limg<?php echo $i; ?>');
            </script>
            <?php } ?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>

    <?php if ($w == 'u') { ?>
    <script>
    $(".banner_or_img").addClass("sit_wimg");
    $(function() {
        $(".sit_wimg_view").bind("click", function() {
            var sit_wimg_id = $(this).attr("id").split("_");
            var $img_display = $("#"+sit_wimg_id[1]);

            var $img = $("#"+sit_wimg_id[1]);
            var width = $img_display.width();
            var height = $img_display.height();
            if(width > 700) {
                var img_width = 700;
                var img_height = Math.round((img_width * height) / width);

                $img_display.children("img").width(img_width).height(img_height);
            }

            $img_display.toggle();

            if($img_display.is(":visible")) {
                $(this).text($(this).text().replace("확인", "닫기"));
            } else {
                $(this).text($(this).text().replace("닫기", "확인"));
            }
        });
        $(".sit_wimg_close").bind("click", function() {
            var $img_display = $(this).parents(".banner_or_img");
            var id = $img_display.attr("id");
            $img_display.toggle();
            var $button = $("#it_"+id+"_view");
            $button.text($button.text().replace("닫기", "확인"));
        });
    });
    </script>
    <?php } ?>

</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./itemlist.php?<?php echo $qstr; ?>">목록</a>
</div>

<section id="anc_sitfrm_relation" class="cbox compare_wrap">
    <h2>관련상품</h2>
    <?php echo $pg_anchor; ?>

    <p>
        등록된 전체상품 목록에서 상품분류를 선택하면 해당 상품 리스트가 연이어 나타납니다.<br>
        상품리스트에서 관련 상품으로 추가하길 원하는 상품을 마우스 더블클릭하거나 키보드 스페이스바를 누르면, 선택된 관련상품 목록에 <strong>함께</strong> 추가됩니다.<br>
        예를 들어, A 상품에 B 상품을 관련상품으로 등록하면 B 상품에도 A 상품이 관련상품으로 자동 추가되며, <strong>확인 버튼을 누르셔야 정상 반영됩니다.</strong><br>
        선택된 관련상품 목록에서 상품을 마우스 더블클릭하거나 키보드 스페이스바를 누르면 선택된 관련상품 목록에서 제거됩니다.
    </p>

    <section class="compare_left">
        <h3>등록된 전체상품 목록</h3>
        <label for="sch_relation" class="sound_only">상품분류</label>
        <span class="sit_relation_selwrap">
            <select id="sch_relation" onchange="search_relation(this)">
                <option value=''>분류별 관련상품</option>
                <?php
                    $sql = " select ca_id, ca_name from {$g4['shop_category_table']} where length(ca_id) = 2 order by ca_id ";
                    $result = sql_query($sql);
                    for ($i=0; $row=sql_fetch_array($result); $i++)  {
                        echo "<option value='{$row['ca_id']}'>{$row['ca_name']}\n";
                    }
                ?>
            </select>
        </span>
        <select id="relation" class="sit_relation_list" size="8" onclick="relation_img(this.value, 'add_span')" ondblclick="relation_add(this);" onkeyup="relation_add(this);">
        </select>
        <div>
            <strong class="sound_only">현재 활성화 된 상품</strong>
            <span id="add_span"></span>
        </div>
        <script>
        function search_relation(fld) {
            if (fld.value) {
                $.post(
                    './itemformrelation.php',
                    { it_id: '<?php echo $it_id; ?>', ca_id: fld.value },
                    function(data) {
                        $("#relation").html(data);
                    }
                );
            }
        }
        </script>
        <script>

            // 김선용 2006.10
            function relation_img(it_id, id)
            {
                if(!it_id) return;
                $.post(
                    "<?php echo G4_ADMIN_URL; ?>/shop_admin/itemformrelationimage.php",
                    { it_id: it_id, width: "100", height: "80" },
                    function(data) {
                        $("#"+id).html(data);
                    }
                );
            }

            function relation_add(fld)
            {
                if(window.event.keyCode && window.event.keyCode != 32)
                    return false;

                var f = document.fitemform;
                var len = f.relationselect.length;
                var find = false;

                for (i=0; i<len; i++) {
                    if (fld.options[fld.selectedIndex].value == f.relationselect.options[i].value) {
                        find = true;
                        break;
                    }
                }

                // 같은 이벤트를 찾지못하였다면 입력
                if (!find) {
                    f.relationselect.length += 1;
                    f.relationselect.options[len].value = fld.options[fld.selectedIndex].value;
                    f.relationselect.options[len].text  = fld.options[fld.selectedIndex].text;
                }

                relation_hidden();
            }

            function relation_del(fld)
            {
                if(window.event.keyCode && window.event.keyCode != 32)
                    return false;

                if (fld.length == 0) {
                    return;
                }

                if (fld.selectedIndex < 0)
                    return;

                for (i=0; i<fld.length; i++) {
                    // 선택된것과 값이 같다면 1을 더한값을 현재것에 복사
                    if (fld.options[i].value == fld.options[fld.selectedIndex].value) {
                        for (k=i; k<fld.length-1; k++) {
                            fld.options[k].value = fld.options[k+1].value;
                            fld.options[k].text  = fld.options[k+1].text;
                        }
                        break;
                    }
                }
                fld.length -= 1;

                relation_hidden();
            }

            // hidden 값을 변경 : 김선용 2006.10 일부수정
            function relation_hidden()
            {
                var f = fitemform;
                //var str = '';
                //var comma = '';
                var str = new Array();
                for (i=0; i<f.relationselect.length; i++) {
                    //str += comma + f.relationselect.options[i].value;
                    //comma = ',';
                    temp = f.relationselect.options[i].value.split("/");
                    str[i] = temp[0]; // 상품ID 만 저장
                }
                //f.it_list.value = str;
                f.it_list.value = str.join(",");
            }
        </script>
    </section>

    <section class="compare_right">
        <h3>선택된 관련상품 목록</h3>
        <span class="sit_relation_selwrap"></span>
        <select name="relationselect" size="8" class="sit_relation_selected" onclick="relation_img(this.value, 'sel_span')" ondblclick="relation_del(this);" onkeyup="relation_del(this);">
        <?php
        $str = array();
        $sql = " select b.ca_id, b.it_id, b.it_name, b.it_price
                   from {$g4['shop_item_relation_table']} a
                   left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id)
                  where a.it_id = '$it_id'
                  order by ir_no asc ";
        $result = sql_query($sql);
        while($row=sql_fetch_array($result))
        {
            $sql2 = " select ca_name from {$g4['shop_category_table']} where ca_id = '{$row['ca_id']}' ";
            $row2 = sql_fetch($sql2);
        ?>
            <option value="<?php echo $row['it_id']; ?>"><?php echo $row2['ca_name']; ?> : <?php echo cut_str(get_text(strip_tags($row['it_name'])),30); ?></option>
        <?php
            $str[] = $row['it_id'];
        }
        $str = implode(",", $str);
        ?>
        </select>
        <div>
            <strong class="sound_only">현재 활성화 된 상품</strong>
            <span id="sel_span"></span>
        </div>
        <input type="hidden" name="it_list" value="<?php echo $str; ?>">
    </section>

</section>

<section id="anc_sitfrm_event" class="cbox compare_wrap">
    <h2>관련이벤트</h2>
    <?php echo $pg_anchor; ?>
    <p>
        등록된 전체이벤트 목록에서 추가하길 원하는 이벤트를 마우스 더블클릭하거나 키보드 스페이스바를 누르면, 선택된 관련이벤트 목록에 추가됩니다.<br>
        선택된 관련이벤트 목록에서 이벤트 선택 후 마우스 더블클릭하거나 키보드 스페이스바를 누르면 선택된 관련이벤트 목록에서 제거됩니다.
    </p>

    <script> var eventselect = new Array(); </script>
    <section class="compare_left">
        <h3>등록된 전체이벤트 목록</h3>
        <select size="8" class="sit_relation_list" ondblclick="event_add(this);" onkeyup="event_add(this);">
        <?php
        $sql = " select ev_id, ev_subject from {$g4['shop_event_table']} order by ev_id desc ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo "<option value='{$row['ev_id']}'>".get_text($row['ev_subject']);
        }
        ?>
        </select>
        <script>
            function event_add(fld)
            {
                if(window.event.keyCode && window.event.keyCode != 32)
                    return false;

                var f = document.fitemform;
                var len = f.eventselect.length;
                var find = false;

                for (i=0; i<len; i++) {
                    if (fld.options[fld.selectedIndex].value == f.eventselect.options[i].value) {
                        find = true;
                        break;
                    }
                }

                // 같은 이벤트를 찾지못하였다면 입력
                if (!find) {
                    f.eventselect.length += 1;
                    f.eventselect.options[len].value = fld.options[fld.selectedIndex].value;
                    f.eventselect.options[len].text  = fld.options[fld.selectedIndex].text;
                }

                event_hidden();
            }

            function event_del(fld)
            {
                if(window.event.keyCode && window.event.keyCode != 32)
                    return false;

                if (fld.length == 0) {
                    return;
                }

                if (fld.selectedIndex < 0)
                    return;

                for (i=0; i<fld.length; i++) {
                    // 선택된것과 값이 같다면 1을 더한값을 현재것에 복사
                    if (fld.options[i].value == fld.options[fld.selectedIndex].value) {
                        for (k=i; k<fld.length-1; k++) {
                            fld.options[k].value = fld.options[k+1].value;
                            fld.options[k].text  = fld.options[k+1].text;
                        }
                        break;
                    }
                }
                fld.length -= 1;

                event_hidden();
            }

            // hidden 값을 변경
            function event_hidden()
            {
                var f = fitemform;

                var str = '';
                var comma = '';
                for (i=0; i<f.eventselect.length; i++) {
                    str += comma + f.eventselect.options[i].value;
                    comma = ',';
                }
                f.ev_list.value = str;
            }
        </script>
    </section>

    <section class="compare_right">
        <h3>선택된 관련이벤트 목록</h3>
        <select name="eventselect" class="sit_relation_selected" size="8" ondblclick="event_del(this);" onkeyup="event_del(this);">
        <?php
        $str = "";
        $comma = "";
        $sql = " select b.ev_id, b.ev_subject
                   from {$g4['shop_event_item_table']} a
                   left join {$g4['shop_event_table']} b on (a.ev_id=b.ev_id)
                  where a.it_id = '$it_id'
                  order by b.ev_id desc ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo "<option value='{$row['ev_id']}'>".get_text($row['ev_subject']);
            $str .= $comma . $row['ev_id'];
            $comma = ",";
        }
        ?>
        </select>
        <input type="hidden" name="ev_list" value="<?php echo $str; ?>">

    </section>

</section>

<section id="anc_sitfrm_optional" class="cbox">
    <h2>상세설명설정</h2>
    <?php echo $pg_anchor; ?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">상단이미지</th>
        <td colspan="2">
            <?php echo help("상품상세설명 페이지 상단에 출력하는 이미지입니다."); ?>
            <input type="file" name="it_himg">
            <?php
            $himg_str = "";
            $himg = G4_DATA_PATH."/item/{$it['it_id']}_h";
            if (file_exists($himg)) {
            ?>
            <label for="it_himg_del">상단이미지 삭제</label>
            <input type="checkbox" name="it_himg_del" value="1" id="it_himg_del">
            <div class="banner_or_img"><img src="<?php echo G4_DATA_URL; ?>/item/<?php echo $it['it_id']; ?>_h" alt=""></div>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단이미지</th>
        <td colspan="2">
            <?php echo help("상품상세설명 페이지 하단에 출력하는 이미지입니다."); ?>
            <input type="file" name="it_timg">
            <?php
            $timg_str = "";
            $timg = G4_DATA_PATH."/item/{$it['it_id']}_t";
            if (file_exists($timg)) {
            ?>
            <label for="it_timg_del">삭제</label>
            <input type="checkbox" name="it_timg_del" value="1" id="it_timg_del">
            <div class="banner_or_img"><img src="<?php echo G4_DATA_URL; ?>/item/<?php echo $it['it_id']; ?>_t" alt=""></div>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">상품상단내용</th>
        <td><?php echo help("상품상세설명 페이지 상단에 출력하는 HTML 내용입니다."); ?><?php echo editor_html('it_head_html', $it['it_head_html']); ?></td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_head_html" value="1" id="chk_ca_it_head_html">
            <label for="chk_ca_it_head_html">분류적용</label>
            <input type="checkbox" name="chk_all_it_head_html" value="1" id="chk_all_it_head_html">
            <label for="chk_all_it_head_html">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">상품하단내용</th>
        <td><?php echo help("상품상세설명 페이지 하단에 출력하는 HTML 내용입니다."); ?><?php echo editor_html('it_tail_html', $it['it_tail_html']); ?></td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_tail_html" value="1" id="chk_ca_it_tail_html">
            <label for="chk_ca_it_tail_html">분류적용</label>
            <input type="checkbox" name="chk_all_it_tail_html" value="1" id="chk_all_it_tail_html">
            <label for="chk_all_it_tail_html">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">모바일 상품상단내용</th>
        <td><?php echo help("모바일 상품상세설명 페이지 상단에 출력하는 HTML 내용입니다."); ?><?php echo editor_html('it_mobile_head_html', $it['it_mobile_head_html']); ?></td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_mobile_head_html" value="1" id="chk_ca_it_mobile_head_html">
            <label for="chk_ca_it_mobile_head_html">분류적용</label>
            <input type="checkbox" name="chk_all_it_mobile_head_html" value="1" id="chk_all_it_mobile_head_html">
            <label for="chk_all_it_mobile_head_html">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row">모바일 상품하단내용</th>
        <td><?php echo help("모바일 상품상세설명 페이지 하단에 출력하는 HTML 내용입니다."); ?><?php echo editor_html('it_mobile_tail_html', $it['it_mobile_tail_html']); ?></td>
        <td class="group_setting">
            <input type="checkbox" name="chk_ca_it_mobile_tail_html" value="1" id="chk_ca_it_mobile_tail_html">
            <label for="chk_ca_it_mobile_tail_html">분류적용</label>
            <input type="checkbox" name="chk_all_it_mobile_tail_html" value="1" id="chk_all_it_mobile_tail_html">
            <label for="chk_all_it_mobile_tail_html">전체적용</label>
        </td>
    </tr>
    <?php if ($w == "u") { ?>
    <tr>
        <th scope="row">입력일시</th>
        <td colspan="2">
            <?php echo help("상품을 처음 입력(등록)한 시간입니다."); ?>
            <?php echo $it['it_time']; ?>
        </td>
    </tr>
    <?php } ?>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./itemlist.php?<?php echo $qstr; ?>">목록</a>
</div>
</form>


<script>
var f = document.fitemform;

function codedupcheck(id)
{
    if (!id) {
        alert('상품코드를 입력하십시오.');
        f.it_id.focus();
        return;
    }

    var it_id = id.replace(/[A-Za-z0-9\-_]/g, "");
    if(it_id.length > 0) {
        alert("상품코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.");
        return false;
    }

    $.post(
        "./codedupcheck.php",
        { it_id: id },
        function(data) {
            if(data.name) {
                alert("코드 '"+data.code+"' 는 '".data.name+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
                return false;
            } else {
                alert("'"+data.code+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
                document.fitemform.codedup.value = '';
            }
        }, "json"
    );
}

function fitemformcheck(f)
{
    if (!f.ca_id.value) {
        alert("기본분류를 선택하십시오.");
        f.ca_id.focus();
        return false;
    }

    if (f.w.value == "") {
        if (f.codedup.value == '1') {
            alert("코드 중복검사를 하셔야 합니다.");
            return false;
        }
    }

    // 옵션값 검사
    for (var i=1; i<=6; i++) {
        var opt = document.getElementsByName("it_opt"+i)[0];
        var arr = opt.value.split("\n");
        for (var k=0; k<arr.length; k++) {
            var str = arr[k];
            if (k==0) {
                if (str.indexOf("&") == -1 && str.indexOf(";") != -1) {
                    alert("옵션의 첫 번째 라인에는 금액을 입력할 수 없습니다.\n\n또는 ; 를 입력할 수 없습니다.");
                    opt.focus();
                    return false;
                }
            }
            else {
                var exp = str.split(";");
                if (typeof exp[1] != "undefined") {
                    var c = exp[1].substr(0,1);
                    if (!(c == "+" || c == "-")) {
                        alert("옵션의 금액 입력 오류입니다.\n\n추가되는 금액은 + 부호를\n\n할인되는 금액은 - 부호를 붙여 주십시오.");
                        opt.focus();
                        return false;
                    }
                }
            }
        }
    }

    <?php echo get_editor_js('it_explan'); ?>
    <?php echo get_editor_js('it_mobile_explan'); ?>
    <?php echo get_editor_js('it_head_html'); ?>
    <?php echo get_editor_js('it_tail_html'); ?>
    <?php echo get_editor_js('it_mobile_head_html'); ?>
    <?php echo get_editor_js('it_mobile_tail_html'); ?>
    return true;
}

function categorychange(f)
{
    var idx = f.ca_id.value;

    if (f.w.value == "" && idx)
    {
        f.it_use.checked = ca_use[idx] ? true : false;
        //f.it_explan_html[ca_explan_html[idx]].checked = true;
        f.it_stock_qty.value = ca_stock_qty[idx];
        f.it_sell_email.value = ca_sell_email[idx];
        f.it_opt1_subject.value = ca_opt1_subject[idx];
        f.it_opt2_subject.value = ca_opt2_subject[idx];
        f.it_opt3_subject.value = ca_opt3_subject[idx];
        f.it_opt4_subject.value = ca_opt4_subject[idx];
        f.it_opt5_subject.value = ca_opt5_subject[idx];
        f.it_opt6_subject.value = ca_opt6_subject[idx];
    }
}

categorychange(document.fitemform);
</script>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
