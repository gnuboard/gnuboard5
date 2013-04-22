<?
$sub_menu = '400300';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');

/*
// 상품테이블에 분류 필드 추가
sql_query(" ALTER TABLE `$g4[shop_item_table]` ADD `ca_id2` VARCHAR( 255 ) NOT NULL AFTER `ca_id` ", FALSE);
sql_query(" ALTER TABLE `$g4[shop_item_table]` ADD `ca_id3` VARCHAR( 255 ) NOT NULL AFTER `ca_id2` ", FALSE);

// 사용후기 테이블에 이름, 패스워드 필드 추가
sql_query(" ALTER TABLE `$g4[shop_item_ps_table]` ADD `is_name` VARCHAR( 255 ) NOT NULL AFTER `mb_id` ", FALSE);
sql_query(" ALTER TABLE `$g4[shop_item_ps_table]` ADD `is_password` VARCHAR( 255 ) NOT NULL AFTER `is_name` ", FALSE);

// 상품문의 테이블에 이름, 패스워드 필드 추가
sql_query(" ALTER TABLE `$g4[shop_item_qa_table]` ADD `iq_name` VARCHAR( 255 ) NOT NULL AFTER `mb_id` ", FALSE);
sql_query(" ALTER TABLE `$g4[shop_item_qa_table]` ADD `iq_password` VARCHAR( 255 ) NOT NULL AFTER `iq_name` ", FALSE);

// 회원권한별 상품가격 틀리게 적용하는 필드 추가
// it_amount  : 비회원가격
// it_amount2 : 회원가격
// it_amount3 : 특별회원가격
sql_query(" ALTER TABLE `$g4[shop_item_table]` ADD `it_amount2` INT NOT NULL AFTER `it_amount` ", FALSE);
sql_query(" ALTER TABLE `$g4[shop_item_table]` ADD `it_amount3` INT NOT NULL AFTER `it_amount2` ", FALSE);
*/

auth_check($auth[$sub_menu], "w");

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
<li><a href="#anc_sitfrm_img">상품이미지</a></li>
<li><a href="#anc_sitfrm_relation">관련상품</a></li>
<li><a href="#anc_sitfrm_event">관련이벤트</a></li>
<li><a href="#anc_sitfrm_optional">상세설명설정</a></li>
</ul>
';
?>

<form name="fitemform" action="./itemformupdate.php" method="post" enctype="MULTIPART/FORM-DATA" autocomplete="off" onsubmit="return fitemformcheck(this)">

<input type="hidden" name="codedup" value="<?=$default['de_code_dup_use']?>">
<input type="hidden" name="w" value="<?=$w?>">
<!-- <input type="hidden" name="sel_ca_id" value="<?=$sel_ca_id?>">
<input type="hidden" name="sel_field" value="<?=$sel_field?>">
<input type="hidden" name="search" value="<?=$search?>">
<input type="hidden" name="sort1" value="<?=$sort1?>">
<input type="hidden" name="sort2" value="<?=$sort2?>"> -->
<input type="hidden" name="sca" value="<?=$sca?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod"  value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx"  value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="it_explan_html" value="1"><!---->

<section id="anc_sitfrm_cate" class="cbox">
    <h2>상품분류</h2>
    <?=$pg_anchor?>
    <p>기본분류는 반드시 선택하셔야 합니다. 하나의 상품에 최대 3개의 다른 분류를 지정할 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <? // ##### // 웹 접근성 취약 지점 시작 - 지운아빠 2013-04-19 ?>
    <tr>
        <th scope="row"><label for="ca_id">기본분류</label></th>
        <td>
            <? if ($w == "") echo help("기본분류를 선택하면, 판매/재고/HTML사용/판매자 E-mail 등을, 선택한 분류의 기본값으로 설정합니다."); ?>
            <select name="ca_id" id="ca_id" onchange="categorychange(this.form)">
                <option value="">선택하세요</option>
                <?=conv_selected_option($category_select, $it['ca_id'])?>
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
                <?="\n$script"?>
            </script>
        </td>
    </tr>
    <? for ($i=2; $i<=3; $i++) { ?>
    <tr>
        <th scope="row"><label for="ca_id<?=$i?>"><?=$i?>차 분류</label></th>
        <td>
            <?=help($i.'차 분류는 기본 분류의 하위 분류 개념이 아니므로 기본 분류 선택시 해당 상품이 포함될 최하위 분류만 선택하시면 됩니다.')?>
            <select name="ca_id<?=$i?>" id="ca_id<?=$i?>">
                <option value="">선택하세요</option>
                <?=conv_selected_option($category_select, $it['ca_id'.$i]);?>
            </select>
        </td>
    </tr>
    <? } ?>
    <? // ##### // 웹 접근성 취약 지점 끝 ?>
    </tbody>
    </table>
</section>

<section id="anc_sitfrm_ini" class="cbox">
    <h2>기본정보</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">상품코드</th>
        <td>
            <? if ($w == '') { // 추가 ?>
                <!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
                <!-- <input type=text class=required name=it_id value="<?=10000000000-time()?>" size=12 maxlength=10 required> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
                <?=help("상품의 코드는 10자리 숫자로 자동생성합니다. <b>직접 상품코드를 입력할 수도 있습니다.</b>\n상품코드는 영문자와 숫자만 입력 가능합니다.");?>
                <input type="text" name="it_id" value="<?=time()?>" id="it_id" required class="frm_input required" size="12" maxlength="10">
                <? if ($default['de_code_dup_use']) { ?><a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src="<?=G4_ADMIN_URL?>/img/btn_code.gif"></a><? } ?>
            <? } else { ?>
                <input type="hidden" name="it_id" value="<?=$it['it_id']?>">
                <span class="frm_ca_id"><?=$it['it_id']?></span>
                <a href="<?=G4_SHOP_URL?>/item.php?it_id=<?=$it_id?>" class="btn_frmline">상품확인</a>
                <a href="<?=G4_ADMIN_URL?>/shop_admin/itempslist.php?sel_field=a.it_id&amp;search=<?=$it_id?>" class="btn_frmline">사용후기</a>
                <a href="<?=G4_ADMIN_URL?>/shop_admin/itemqalist.php?sel_field=a.it_id&amp;search=<?=$it_id?>" class="btn_frmline">상품문의</a>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_name">상품명</label></th>
        <td>
            <input type="text" name="it_name" value="<?=get_text(cut_str($it['it_name'], 250, ""))?>" id="it_name" required class="frm_input required" size="95">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_gallery">전시용 상품</label></th>
        <td>
           <?=help("이 항목을 체크하면 상품을 전시만 하고, 판매하지 않습니다.");?>
            <input type="checkbox" name="it_gallery" value="1" id="it_gallery" <?=($it['it_gallery'] ? "checked" : "")?>>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_order">출력순서</label></th>
        <td>
            <?=help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>");?>
            <input type="text" name="it_order" value="<? echo $it['it_order'] ?>" id="it_order" class="frm_input" size="12">
        </td>
    </tr>
    <tr>
        <th scope="row">상품유형</th>
        <td>
            <?=help("메인화면에 유형별로 출력할때 사용합니다.\n이곳에 체크하게되면 상품리스트에서 유형별로 정렬할때 체크된 상품이 가장 먼저 출력됩니다.");?>
            <input type="checkbox" name="it_type1" value="1" <?=($it['it_type1'] ? "checked" : "");?> id="it_type1">
            <label for="it_type1"><img src="<?=G4_SHOP_URL?>/img/icon_type1.gif" alt="HIT상품"></label>
            <input type="checkbox" name="it_type2" value="1" <?=($it['it_type2'] ? "checked" : "");?> id="it_type2">
            <label for="it_type2"><img src="<?=G4_SHOP_URL?>/img/icon_type2.gif" alt="추천상품"></label>
            <input type="checkbox" name="it_type3" value="1" <?=($it['it_type3'] ? "checked" : "");?> id="it_type3">
            <label for="it_type3"><img src="<?=G4_SHOP_URL?>/img/icon_type3.gif" alt="NEW상품"></label>
            <input type="checkbox" name="it_type4" value="1" <?=($it['it_type4'] ? "checked" : "");?> id="it_type4">
            <label for="it_type4"><img src="<?=G4_SHOP_URL?>/img/icon_type4.gif" alt="인기상품"></label>
            <input type="checkbox" name="it_type5" value="1" <?=($it['it_type5'] ? "checked" : "");?> id="it_type5">
            <label for="it_type5"><img src="<?=G4_SHOP_URL?>/img/icon_type5.gif" alt="DC상품"></label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_maker">제조사</label></th>
        <td>
            <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
            <input type="text" name="it_maker" value="<?=get_text($it['it_maker'])?>" id="it_maker" class="frm_input" size="40">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_origin">원산지</label></th>
        <td>
            <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
            <input type="text" name="it_origin" value="<?=get_text($it['it_origin'])?>" id="it_origin" class="frm_input" size="40">
        </td>
    </tr>
    <?
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
            <label for="it_opt<?=$k1?>_subject">상품옵션명 <?=$k1?></label><br>
            <input type="text" name="it_opt<?=$k1?>_subject" value="<?=get_text($val11)?>" id="it_opt<?=$k1?>_subject" class="frm_input" size="15">
        </th>
        <td>
            <label for="it_opt<?=$k1?>" class="sound_only">상품옵션설정 <?=$k1?></label>
            <textarea name="it_opt<?=$k1?>" id="it_opt<?=$k1?>" class="sit_w_opt"><?=$val12?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row">
            <label for="it_opt<?=$k2?>_subject">상품옵션명 <?=$k2?></label><br>
            <input type="text" name="it_opt<?=$k2?>_subject" value="<?=get_text($val21)?>" id="it_opt<?=$k2?>_subject" class="frm_input" size="15">
        </th>
        <td>
            <label for="it_opt<?=$k1?>" class="sound_only">상품옵션설정 <?=$k2?></label>
            <textarea name="it_opt<?=$k2?>" id="it_opt<?=$k2?>" class="sit_w_opt"><?=$val22?></textarea>
        </td>
    </tr>
    <? } ?>
    <tr>
        <th scope="row"><label for="it_amount">비회원가격</label></th>
        <td>
            <?=help("상품의 기본판매가격(로그인 이전 가격)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.");?>
            <input type="text" name="it_amount" value="<?=$it['it_amount']?>" id="it_amount" class="frm_input" size="8"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_amount2">회원가격</label></th>
        <td>
            <?=help("상품의 로그인 이후 가격(회원 권한 2 에만 적용)되며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.\n<strong>입력이 없다면 비회원가격으로 대신합니다.</strong>");?>
            <input type="text" name="it_amount2" value="<?=$it['it_amount2']?>" id="it_amount2" class="frm_input"  size="8"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_amount3">특별회원가격</label></th>
        <td>
            <?=help("상품의 로그인 이후 가격(회원 권한 3 이상에 적용)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.\n<strong>입력이 없다면 회원가격으로 대신합니다. 회원가격도 없다면 비회원가격으로 대신합니다.</strong>");?>
            <input type="text" name="it_amount3" value="<?=$it['it_amount3']?>" id="it_amount3" class="frm_input" size="8"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_cust_amount">시중가격</label></th>
        <td>
            <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
            <input type="text" name="it_cust_amount" value="<?=$it['it_cust_amount']?>" id="it_cust_amount" class="frm_input" size="8"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_point">포인트</label></th>
        <td>
            <?=help("주문완료후 환경설정에서 설정한 주문완료 설정일 후 회원에게 부여하는 포인트입니다.\n또, 포인트부여를 '아니오'로 설정한 경우 신용카드, 계좌이체로 주문하는 회원께는 부여하지 않습니다.\n포인트 기능을 사용해야 동작합니다.");?>
            <input type="text" name="it_point" value="<? echo $it['it_point'] ?>" id="it_point" class="frm_input" size="8"> 점
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_stock_qty">재고수량</label></th>
        <td>
            <?=help("<b>주문관리에서 상품별 상태 변경에 따라 자동으로 재고를 가감합니다.</b> 재고는 규격/색상별이 아닌, 상품별로만 관리됩니다.");?>
            <input type="text" name="it_stock_qty" value="<? echo $it['it_stock_qty'] ?>" id="it_stock_qty" class="frm_input" size="8"> 개</span>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_basic">기본설명</label></th>
        <td>
            <?=help("상품상세페이지의 상품설명 상단에 표시되는 설명입니다. HTML 입력도 가능합니다.");?>
            <input type="text" name="it_basic" value="<?=get_text($it['it_basic'])?>" id="it_basic" class="frm_input" size="90">
        </td>
    </tr>
    <? if ($it['it_id']) { ?>
    <?
    $sql = " select distinct ii_gubun from {$g4['shop_item_info_table']} where it_id = '$it_id' group by ii_gubun ";
    $ii = sql_fetch($sql, false);
    if ($ii) {
        $item_info_gubun = item_info_gubun($ii['ii_gubun']);
        $item_info_gubun .= $item_info_gubun ? " 등록됨" : "";
    } else {
        // 상품상세정보 테이블이 없다고 가정하여 생성
        create_table_item_info();
    }
    ?>
    <tr>
        <th scope="row">요약상품정보</th>
        <td>
            <?=help("<strong>전자상거래 등에서의 상품 등의 정보제공에 관한 고시</strong>에 따라 총 35개 상품군에 대해 상품 특성 등을 양식에 따라 입력할 수 있습니다.");?>
            <button type="button" class="btn_frmline" onclick="window.open('./iteminfo.php?it_id=<?=$it['it_id']?>', '_blank', 'width=670 height=800 scrollbars=yes');">상품요약정보 설정</button>
            <span id="item_info_gubun"><?=$item_info_gubun?></span>
        </td>
    </tr>
    <?}//if?>
    <tr>
        <th scope="row">상품설명</th>
        <td> <?=editor_html('it_explan', $it['it_explan']);?></td>
    </tr>
    <tr>
        <th scope="row"><label for="it_sell_email">판매자 e-mail</label></th>
        <td>
            <?=help("운영자와 실제 판매자가 다른 경우 실제 판매자의 e-mail을 입력하면, 상품 주문 시점을 기준으로 실제 판매자에게도 주문서를 발송합니다.");?>
            <input type="text" name="it_sell_email" value="<? echo $it['it_sell_email'] ?>" id="it_sell_email" class="frm_input" size="40">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_tel_inq">전화문의</label></th>
        <td>
            <?=help("상품 금액 대신 전화문의로 표시됩니다.");?>
            <input type="checkbox" name="it_tel_inq" value="1" id="it_tel_inq" <? echo ($it['it_tel_inq']) ? "checked" : ""; ?>> 예
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_use">판매가능</label></th>
        <td>
            <?=help("잠시 판매를 중단하거나 재고가 없을 경우에 체크를 해제해 놓으면 출력되지 않으며, 주문도 받지 않습니다.");?>
            <input type="checkbox" name="it_use" value="1" id="it_use" <? echo ($it['it_use']) ? "checked" : ""; ?>> 예
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="anc_sitfrm_img" class="cbox">
    <h2>이미지</h2>
    <?=$pg_anchor?>
    <p>이미지 자동생성 기능을 이용하시면, 이미지(대) 1장만 업로드 해서 자동으로 이미지(중), 이미지(소) 를 생성할 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <? if (function_exists("imagecreatefromjpeg")) { ?>
    <tr>
        <th scope="row"><label for="createimage">이미지 자동생성</label></th>
        <td>
            <?=help("<strong>JPG 파일만 가능합니다.</strong> 이미지(대) 를 기준으로 이미지(중)과 이미지(소) 의 사이즈를 환경설정에서 정한 폭과 높이로 자동생성합니다.");?>
            <input type="checkbox" name="createimage" value="1" id="createimage"> 사용
        </td>
    </tr>
    <? } ?>
    <tr>
        <th scope="row"><label for="it_limg1">이미지(대)</label></th>
        <td>
            <input type="file" name="it_limg1" id="it_limg1">
            <?
            $limg1 = G4_DATA_PATH.'/item/'.$it['it_id'].'_l1';
            if (file_exists($limg1)) {
                $size = getimagesize($limg1);
            ?>
            <label for="it_limg1_del"><span class="sound_only">이미지(대) </span>파일삭제</label>
            <input type="checkbox" name="it_limg1_del" value="1">
            <span class="sit_wimg_limg1"></span>
            <div id="limg1" class="banner_or_img">
                <img src="<?=G4_DATA_URL?>/item/<?=$it['it_id']?>_l1" alt="" width="<?=$size[0]?>" height="<?=$size[1]?>">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="it_limg1_view" class="btn_frmline sit_wimg_view">이미지(대) 확인</button>').appendTo('.sit_wimg_limg1');
            </script>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_mimg">이미지(중)</label></th>
        <td>
            <?=help("이미지 자동생성 기능을 사용하지 않거나, 이미지를 업로드 하지 않으면 기본 noimage 로 출력합니다.");?>
            <input type="file" name="it_mimg" id="it_mimg">
            <?
            $mimg = G4_DATA_PATH.'/item/'.$it['it_id'].'_m';
            if (file_exists($mimg)) {
                $size = getimagesize($mimg);
            ?>
            <label for="it_mimg_del"><span class="sound_only">이미지(중) </span>파일삭제</label>
            <input type="checkbox" name="it_mimg_del" value="1" id="it_mimg_del">
            <span class="sit_wimg_mimg"></span>
            <div id="mimg" class="banner_or_img">
                <img src="<?=G4_DATA_URL?>/item/<?=$it['it_id']?>_m" alt="" width="<?=$size[0]?>" height="<?=$size[1]?>">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="it_mimg_view" class="btn_frmline sit_wimg_view">이미지(중) 확인</button>').appendTo('.sit_wimg_mimg');
            </script>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="it_simg">이미지(소)</label></th>
        <td>
            <?=help("이미지 자동생성 기능을 사용하지 않거나, 이미지를 업로드 하지 않으면 기본 noimage 로 출력합니다.");?>
            <input type="file" name="it_simg" id="it_simg">
            <?
            $simg = G4_DATA_PATH.'/item/'.$it['it_id'].'_s';
            if (file_exists($simg)) {
                $size = getimagesize($simg);
            ?>
            <label for="it_simg_del"><span class="sound_only">이미지(소) </span>파일삭제</label>
            <input type="checkbox" name="it_simg_del" value="1" id="it_simg_del">
            <span class="sit_wimg_simg"></span>
            <div id="simg" class="banner_or_img">
                <img src="<?=G4_DATA_URL?>/item/<?=$it['it_id']?>_s" alt="" width="<?=$size[0]?>" height="<?=$size[1]?>">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="it_simg_view" class="btn_frmline sit_wimg_view">이미지(소) 확인</button>').appendTo('.sit_wimg_simg');
            </script>
            <? } ?>
        </td>
    </tr>
    <? for ($i=2; $i<=5; $i++) { // 이미지(대)는 5개 ?>
    <tr>
        <th scope="row"><label for="it_limg<?=$i?>">이미지(대)<?=$i?></label></th>
        <td>
            <input type="file" name="it_limg<?=$i?>" id="it_limg<?=$i?>">
            <?
            $limg = G4_DATA_PATH.'/item/'.$it['it_id'].'_l'.$i;
            if (file_exists($limg)) {
                $size = getimagesize($limg);
            ?>
            <label for="it_limg<?=$i?>_del"><span class="sound_only">이미지(대)<?=$i?> </span>파일삭제</label>
            <input type="checkbox" name="it_limg<?=$i?>_del" value="1" id="it_limg<?=$i?>_del">
            <span class="sit_wimg_limg<?=$i?>"></span>
            <div id="limg<?=$i?>" class="banner_or_img">
                <img src="<?=G4_DATA_URL?>/item/<?=$it['it_id']?>_l<?=$i?>">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <? } ?>
            <script>
            $('<button type="button" id="it_limg<?=$i?>_view" class="btn_frmline sit_wimg_view">이미지(대)<?=$i?> 확인</button>').appendTo('.sit_wimg_limg<?=$i?>');
            </script>
        </td>
    </tr>
    <? } ?>
    </tbody>
    </table>

    <? if (file_exists($limg1) || file_exists($mimg) || file_exists($simg)) { ?>
    <script>
    $(".banner_or_img").addClass("sit_wimg");
    $(function() {
        $(".sit_wimg_view").bind("click", function() {
            var sit_wimg_id = $(this).attr("id").split("_");
            var $img_display = $("#"+sit_wimg_id[1]);

            if(sit_wimg_id[1].search("limg") > -1) {
                var $img = $("#"+sit_wimg_id[1]);
                var width = $img_display.width();
                var height = $img_display.height();
                if(width > 750) {
                    var img_width = 750;
                    var img_height = Math.round((img_width * height) / width);

                    $img_display.children("img").width(img_width).height(img_height);
                }
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
    <? } ?>

</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./itemlist.php?<?=$qstr?>">목록</a>
</div>

<section id="anc_sitfrm_relation" class="cbox compare_wrap">
    <h2>관련상품</h2>
    <?=$pg_anchor?>

    <p>
        오른쪽 등록된 전체상품 목록에서 상품을 더블클릭하면 선택된 관련상품 목록에 추가됩니다.<br>
        예를 들어, A 상품에 B 상품을 관련상품으로 등록하면, B 상품에도 A 상품이 관련상품으로 자동 추가되며, <strong>확인 버튼을 누르셔야 정상 반영됩니다.</strong>
    </p>

    <section class="compare_left">
        <h3>등록된 전체상품 목록</h3>
        <span id="add_span" style="line-height:200%"></span>
        <script>
        function search_relation(fld) {
            if (fld.value) {
                $.post(
                    './itemformrelation.php',
                    { it_id: '<?=$it_id?>', ca_id: fld.value },
                    function(data) {
                        if(data) {
                            $("#relation").html(data);
                        }
                    }
                );
            }
        }
        </script>
        <label for="sch_relation" class="sound_only">상품분류</label>
        <select id="sch_relation" onchange="search_relation(this)">
            <option value=''>분류별 관련상품</option>
            <?
                $sql = " select ca_id, ca_name from {$g4['shop_category_table']} where length(ca_id) = 2 order by ca_id ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)  {
                    echo "<option value='{$row['ca_id']}'>{$row['ca_name']}\n";
                }
            ?>
        </select><br>
        <select id="relation" size=8 style='width:250px; background-color:#F6F6F6;' onclick="relation_img(this.value, 'add_span')" ondblclick="relation_add(this);">
        <?
        /*
        $sql = " select ca_id, it_id, it_name, it_amount
                   from $g4[shop_item_table]
                  where it_id <> '$it_id'
                  order by ca_id, it_name ";
        $result = sql_query($sql);
        for ($i=0; $row=sql_fetch_array($result); $i++)
        {
            $sql2 = " select ca_name from $g4[shop_category_table] where ca_id = '$row[ca_id]' ";
            $row2 = sql_fetch($sql2);

            // 김선용 2006.10
            if(file_exists("{$g4['path']}/data/item/{$row['it_id']}_s"))
                $it_image = "{$row['it_id']}_s";
            else
                $it_image = "";

            echo "<option value='$row[it_id]/$it_image/{$row['it_amount']}'>$row2[ca_name] : ".cut_str(get_text(strip_tags($row[it_name])),30);
        }
        */
        ?>
        </select>
        <script>

            // 김선용 2006.10
            function relation_img(name, id)
            {
                var item_image_url = "";
                if(!name) return;
                temp = name.split("/");
                if(temp[1] == ''){
                    temp[1] = "no_image.gif";
                    item_image_url = "<?=G4_SHOP_URL?>/img";
                } else {
                    item_image_url = "<?=G4_DATA_URL?>/item";
                }
                view_span = document.getElementById(id);
                item_price = number_format(String(temp[2]));
                view_span.innerHTML = "<a href=\"<?=G4_SHOP_URL?>/item.php?it_id="+temp[0]+"\" target=\"_blank\"><img src=\""+item_image_url+"/"+temp[1]+"\"width=\"100\" height=\"80\" border=\"1\" style=\"border-color:#333333;\" title=\"상품 새창으로 보기\"></a><br>"+item_price+" 원";
            }

            function relation_add(fld)
            {
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
        <span id="sel_span" style="line-height:200%"></span>
        ※ 상품 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>※ 한 번 클릭시 상품이미지/상품금액 출력<br>
        <br>
        <select name='relationselect' size=8 style='width:250px;' onclick="relation_img(this.value, 'sel_span')" ondblclick="relation_del(this);">
        <?
        $str = array();
        $sql = " select b.ca_id, b.it_id, b.it_name, b.it_amount
                   from {$g4['shop_item_relation_table']} a
                   left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id)
                  where a.it_id = '$it_id'
                  order by b.ca_id, b.it_name ";
        $result = sql_query($sql);
        while($row=sql_fetch_array($result))
        {
            $sql2 = " select ca_name from {$g4['shop_category_table']} where ca_id = '{$row['ca_id']}' ";
            $row2 = sql_fetch($sql2);

            // 김선용 2006.10
            if(file_exists(G4_DATA_PATH."/item/{$row['it_id']}_s"))
                $it_image = "{$row['it_id']}_s";
            else
                $it_image = "";

            echo "<option value='{$row['it_id']}/$it_image/{$row['it_amount']}'>{$row2['ca_name']} : ".cut_str(get_text(strip_tags($row['it_name'])),30);
            $str[] = $row['it_id'];
        }
        $str = implode(",", $str);
        ?>
        </select>
        <input type='hidden' name='it_list' value='<?=$str?>'>
    </section>

</section>

<section id="anc_sitfrm_event" class="cbox compare_wrap">
    <h2>관련이벤트</h2>
    <?=$pg_anchor?>

    <script> var eventselect = new Array(); </script>
    <section class="compare_left">
        <h3>등록된 전체이벤트 목록</h3>
                이벤트 선택후 <FONT COLOR="#0E87F9">더블클릭하면 왼쪽에 추가됨</FONT><br>
        <select size=6 style='width:250px; background-color:#F6F6F6;' ondblclick="event_add(this);">
        <?
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
        이벤트 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>
        <select name=eventselect size=6 style='width:250px;' ondblclick="event_del(this);">
        <?
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
        <input type="hidden" name="ev_list" value="<?=$str?>">

    </section>

</section>

<section id="anc_sitfrm_optional" class="cbox">
    <h2>상세설명설정</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_5">
        <col class="grid_3">
        <col class="grid_5">
    </colgroup>
    <tbody>
    <tr>
        <!--김혜련 2013-04-02 선택된 목록 / 등록된 목록 넣을 곳-->
        <th scope="row"></th>
        <td></td>
        <th scope="row"></th>
        <td></td>
    </tr>
    <tr>
        <th scope="row">상단이미지</th>
        <td colspan="3">
            <?=help("상품상세설명 페이지 상단에 출력하는 이미지입니다.");?>
            <input type="file" name="it_himg">
            <?
            $himg_str = "";
            $himg = G4_DATA_PATH."/item/{$it['it_id']}_h";
            if (file_exists($himg)) {
            ?>
            <label for="it_himg_del">상단이미지 삭제</label>
            <input type="checkbox" name="it_himg_del" value="1" id="it_himg_del">
            <div class="banner_or_img"><img src="<?=G4_DATA_URL?>/item/<?=$it['it_id']?>_h" alt=""></div>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">하단이미지</th>
        <td colspan="3">
            <?=help("상품상세설명 페이지 하단에 출력하는 이미지입니다.");?>
            <input type="file" name="it_timg">
            <?
            $timg_str = "";
            $timg = G4_DATA_PATH."/item/{$it['it_id']}_t";
            if (file_exists($timg)) {
            ?>
            <label for="it_timg_del">삭제</label>
            <input type="checkbox" name="it_timg_del" value="1" id="it_timg_del">
            <div class="banner_or_img"><img src="<?=G4_DATA_URL?>/item/<?=$it['it_id']?>_t" alt=""></div>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">상품상단내용</th>
        <td colspan="3"><?=help("상품상세설명 페이지 상단에 출력하는 HTML 내용입니다.", -150);?><?=editor_html('it_head_html', $it['it_head_html']);?></td>
    </tr>
    <tr>
        <th scope="row">상품하단내용</th>
        <td colspan="3"><?=help("상품상세설명 페이지 하단에 출력하는 HTML 내용입니다.", -150);?><?=editor_html('it_tail_html', $it['it_tail_html']);?></td>
    </tr>
    <? if ($w == "u") { ?>
    <tr>
        <th scope="row">입력일시</th>
        <td colspan="3">
            <?=help("상품을 처음 입력(등록)한 시간입니다.");?>
            <?=$it['it_time']?>
        </td>
    </tr>
    <? } ?>
    </tbody>
    </table>
</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./itemlist.php?<?=$qstr?>">목록</a>
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

    <?=get_editor_js('it_explan');?>
    <?=get_editor_js('it_head_html');?>
    <?=get_editor_js('it_tail_html');?>
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

/*document.fitemform.it_name.focus(); 포커스제거*/
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
