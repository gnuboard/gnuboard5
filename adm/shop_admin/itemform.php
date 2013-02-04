<?
$sub_menu = "400300";
include_once("./_common.php");
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');

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
        $sql = " select ca_id from {$g4['yc4_category_table']} order by ca_id limit 1 ";
        $row = sql_fetch($sql);
        if (!$row['ca_id'])
            alert("등록된 분류가 없습니다. 우선 분류를 등록하여 주십시오.");
        $it['ca_id'] = $row['ca_id'];
    }
    //$it[it_maker]  = stripslashes($_COOKIE[ck_maker]);
    //$it[it_origin] = stripslashes($_COOKIE[ck_origin]);
    $it['it_maker']  = stripslashes(get_cookie("ck_maker"));
    $it['it_origin'] = stripslashes(get_cookie("ck_origin"));

    // 기본배송비
    if($default['de_send_cost_case'] == "개별배송") {
        $it['it_sc_basic'] = $default['de_send_cost_amount'];
    }
}
else if ($w == "u")
{
    $html_title .= "수정";

    if ($is_admin != 'super')
    {
        $sql = " select it_id from $g4[yc4_item_table] a, $g4[yc4_category_table] b
                  where a.it_id = '$it_id'
                    and a.ca_id = b.ca_id
                    and b.ca_mb_id = '$member[mb_id]' ";
        $row = sql_fetch($sql);
        if (!$row[it_id])
            alert("\'{$member[mb_id]}\' 님께서 수정 할 권한이 없는 상품입니다.");
    }

    $sql = " select * from $g4[yc4_item_table] where it_id = '$it_id' ";
    $it = sql_fetch($sql);

    if (!$ca_id)
        $ca_id = $it[ca_id];

    $sql = " select * from $g4[yc4_category_table] where ca_id = '$ca_id' ";
    $ca = sql_fetch($sql);
}
else
{
	alert();
}

if (!$it[it_explan_html])
{
    $it[it_explan] = get_text($it[it_explan], 1);
}

//$qstr1 = "sel_ca_id=$sel_ca_id&sel_field=$sel_field&search=$search";
//$qstr = "$qstr1&sort1=$sort1&sort2=$sort2&page=$page";
$qstr  = "$qstr&sca=$sca&page=$page";

$g4[title] = $html_title;
include_once (G4_ADMIN_PATH.'/admin.head.php');
?>

<style type="text/css">
<!--
ul { margin: 0; padding: 0; list-style: none; }
.handcursor { cursor: pointer; }
-->
</style>

<form id="fitemform" name="fitemform" method=post action="./itemformupdate.php" onsubmit="return fitemformcheck(this)" enctype="MULTIPART/FORM-DATA" autocomplete="off" style="margin:0px;">
<?=subtitle("기본정보")?>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<input type="hidden" id="codedup" name="codedup"     value="<?=$default[de_code_dup_use]?>">
<input type="hidden" id="w" name="w"           value="<?=$w?>">
<!-- <input type="hidden" id="sel_ca_id" name="sel_ca_id"   value="<?=$sel_ca_id?>">
<input type="hidden" id="sel_field" name="sel_field"   value="<?=$sel_field?>">
<input type="hidden" id="search" name="search"      value="<?=$search?>">
<input type="hidden" id="sort1" name="sort1"       value="<?=$sort1?>">
<input type="hidden" id="sort2" name="sort2"       value="<?=$sort2?>"> -->
<input type="hidden" id="sca" name="sca"  value="<?=$sca?>">
<input type="hidden" id="sst" name="sst"  value="<?=$sst?>">
<input type="hidden" id="sod" name="sod"  value="<?=$sod?>">
<input type="hidden" id="sfl" name="sfl"  value="<?=$sfl?>">
<input type="hidden" id="stx" name="stx"  value="<?=$stx?>">
<input type="hidden" id="page" name="page" value="<?=$page?>">
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=15%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=0E87F9></td></tr>
<tr class=ht>
    <td>분류명</td>
    <td colspan=3>
        <select id="ca_id" name="ca_id" onchange="categorychange(this.form)">
            <option value="">= 기본분류 =
            <?
            $script = "";
            $sql = " select * from $g4[yc4_category_table] ";
            if ($is_admin != 'super')
                $sql .= " where ca_mb_id = '$member[mb_id]' ";
            $sql .= " order by ca_id ";
            $result = sql_query($sql);
            for ($i=0; $row=sql_fetch_array($result); $i++)
            {
                $len = strlen($row[ca_id]) / 2 - 1;

                $nbsp = "";
                for ($i=0; $i<$len; $i++)
                    $nbsp .= "&nbsp;&nbsp;&nbsp;";

                $str = "<option value='$row[ca_id]'>$nbsp$row[ca_name]\n";
                $category_select .= $str;
                echo $str;

                $script .= "ca_use['$row[ca_id]'] = $row[ca_use];\n";
                $script .= "ca_stock_qty['$row[ca_id]'] = $row[ca_stock_qty];\n";
                //$script .= "ca_explan_html['$row[ca_id]'] = $row[ca_explan_html];\n";
                $script .= "ca_sell_email['$row[ca_id]'] = '$row[ca_sell_email]';\n";
                $script .= "ca_opt1_subject['$row[ca_id]'] = '$row[ca_opt1_subject]';\n";
                $script .= "ca_opt2_subject['$row[ca_id]'] = '$row[ca_opt2_subject]';\n";
                $script .= "ca_opt3_subject['$row[ca_id]'] = '$row[ca_opt3_subject]';\n";
                $script .= "ca_opt4_subject['$row[ca_id]'] = '$row[ca_opt4_subject]';\n";
                $script .= "ca_opt5_subject['$row[ca_id]'] = '$row[ca_opt5_subject]';\n";
                $script .= "ca_opt6_subject['$row[ca_id]'] = '$row[ca_opt6_subject]';\n";
            }
            ?>
        </select>
        <script> document.fitemform.ca_id.value = '<?=$it[ca_id]?>'; </script>
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

        <? if ($w == "") { ?>
            <?=help("기본분류를 선택하면 선택한 분류의 기본값인 판매, 재고, HTML사용, 판매자 E-mail 을 기본값으로 설정합니다.");?>
        <? } ?>

        <?
        for ($i=2; $i<=3; $i++)
        {
            echo "&nbsp; <select name='ca_id{$i}'><option value=''>= {$i}차 분류 ={$category_select}</select>\n";
            echo "<script> document.fitemform.ca_id{$i}.value = '".$it["ca_id{$i}"]."'; </script>\n";
        }
        ?>
        <?=help("기본분류는 반드시 선택하셔야 합니다.<br><br>하나의 상품에 최대 3개의 다른 분류를 지정할 수 있습니다.<br><br>2차, 3차 분류는 기본 분류의 하위 분류 개념이 아니므로 기본 분류 선택시 해당 상품이 포함될 최하위 분류만 선택하시면 됩니다.");?>
    </td>
</tr>
<tr class=ht>
	<td>상품코드</td>
	<td colspan=3>

	<? if ($w == "") { // 추가 ?>
		<!-- 최근에 입력한 코드(자동 생성시)가 목록의 상단에 출력되게 하려면 아래의 코드로 대체하십시오. -->
		<!-- <input type="text" class=ed id="it_id" name="it_id" value="<?=10000000000-time()?>" size=12 maxlength=10 required nospace alphanumeric itemid="상품코드" name="상품코드"> <a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a> -->
		<input type="text" class=ed id="it_id" name="it_id" value="<?=time()?>" size=12 maxlength=10 required nospace alphanumeric itemid="상품코드" name="상품코드">
        <? if ($default[de_code_dup_use]) { ?><a href='javascript:;' onclick="codedupcheck(document.all.it_id.value)"><img src='./img/btn_code.gif' border=0 align=absmiddle></a><? } ?>
        <?=help("상품의 코드는 10자리 숫자로 자동생성합니다.\n운영자 임의로 상품코드를 입력하실 수 있습니다.\n상품코드는 영문자와 숫자만 입력 가능합니다.");?>
	<? } else { ?>
		<input type="hidden" id="it_id" name="it_id" value="<?=$it[it_id]?>">
		<?=$it[it_id]?>
		<?=icon("보기", G4_SHOP_URL."/item.php?it_id=$it_id");?>
        <a href='./itempslist.php?sel_field=a.it_id&search=<?=$it_id?>'>사용후기</a>
        <a href='./itemqalist.php?sel_field=a.it_id&search=<?=$it_id?>'>상품문의</a>
	<? } ?>

	</td>
</tr>
<tr class=ht>
    <td>상품명</td>
    <td colspan=3>
        <input type="text" id="it_name" name="it_name" value='<?=get_text(cut_str($it[it_name], 250, ""))?>' style='width:97%;' required itemname='상품명' class=ed>
    </td>
</tr>
<tr class=ht>
    <td>출력유형</td>
    <td>
        <input type="checkbox" id="it_gallery" name="it_gallery" value='1' <?=($it[it_gallery] ? "checked" : "")?>> 갤러리로 사용
        <?=help("금액표시는 하지 않고 상품을 구매할 수 없으며 상품설명만 나타낼때 사용합니다.");?>
    </td>
    <td>출력순서</td>
    <td>
        <input type="text" class=ed id="it_order" name="it_order" size=10 value='<? echo $it[it_order] ?>'>
        <?=help("상품의 출력순서를 인위적으로 변경할때 사용합니다.\n숫자를 입력하며 기본은 0 입니다.\n숫자가 작을 수록 상위에 출력됩니다.\n음수 입력도 가능합니다.\n구간 :  -2147483648 ~ 2147483647");?>
    </td>
</tr>
<tr class=ht>
    <td>상품유형</td>
    <td colspan=3>
        <input type="checkbox" id="it_type1" name="it_type1" value='1' <?=($it[it_type1] ? "checked" : "");?>><img src="<?=G4_SHOP_IMG_URL?>/icon_type1.gif" align=absmiddle>
        <input type="checkbox" id="it_type2" name="it_type2" value='1' <?=($it[it_type2] ? "checked" : "");?>><img src="<?=G4_SHOP_IMG_URL?>/icon_type2.gif" align=absmiddle>
        <input type="checkbox" id="it_type3" name="it_type3" value='1' <?=($it[it_type3] ? "checked" : "");?>><img src="<?=G4_SHOP_IMG_URL?>/icon_type3.gif" align=absmiddle>
        <input type="checkbox" id="it_type4" name="it_type4" value='1' <?=($it[it_type4] ? "checked" : "");?>><img src="<?=G4_SHOP_IMG_URL?>/icon_type4.gif" align=absmiddle>
        <input type="checkbox" id="it_type5" name="it_type5" value='1' <?=($it[it_type5] ? "checked" : "");?>><img src="<?=G4_SHOP_IMG_URL?>/icon_type5.gif" align=absmiddle>
        <?=help("메인화면에 유형별로 출력할때 사용합니다.\n\n이곳에 체크하게되면 상품리스트에서 유형별로 정렬할때 체크된 상품이 가장 먼저 출력됩니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>제조사</td>
    <td>
        <input type="text" class=ed id="it_maker" name="it_maker" value='<?=get_text($it[it_maker])?>' size=41>
        <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
    </td>
    <td>원산지</td>
    <td>
        <input type="text" class=ed id="it_origin" name="it_origin" value='<?=get_text($it[it_origin])?>' size=41>
        <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>브랜드</td>
    <td>
        <input type="text" class=ed id="it_brand" name="it_brand" value='<?=get_text($it[it_brand])?>' size=41>
        <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
    </td>
    <td>모델명</td>
    <td>
        <input type="text" class=ed id="it_model" name="it_model" value='<?=get_text($it[it_model])?>' size=41>
        <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.");?>
    </td>
</tr>
<tr class="ht">
    <td>선택옵션</td>
    <td colspan="3"><input type="radio" id="it_option_use" name="it_option_use" value="0" <?php if($w == '' || !$it['it_option_use']) echo 'checked="checked"'; ?> />등록안함&nbsp;&nbsp;&nbsp;<input type="radio" id="it_option_use" name="it_option_use" value="1" <?php if($it['it_option_use']) echo 'checked="checked"'; ?> />등록함&nbsp;&nbsp;&nbsp;
    <a href="./optionform.php?w=<?php echo $w; ?>&amp;it_id=<?php echo $it_id; ?>" target="_blank" onclick="javascript: optionformwindow('<?php echo $w; ?>'); return false;">선택옵션설정</a></td>
</tr>
<tr class="ht">
    <td><input type="text" id="it_opt1_subject" name="it_opt1_subject" value="<?php echo $it['it_opt1_subject']; ?>" /></td>
    <td colspan="3"><input type="text" id="it_opt1" name="it_opt1" value="<?php echo $it['it_opt1']; ?>" style="width: 90%;" /></td>
</tr>
<tr class="ht">
    <td><input type="text" id="it_opt2_subject" name="it_opt2_subject" value="<?php echo $it['it_opt2_subject']; ?>" /></td>
    <td colspan="3"><input type="text" id="it_opt2" name="it_opt2" value="<?php echo $it['it_opt2']; ?>" style="width: 90%;" /></td>
</tr>
<tr class="ht">
    <td><input type="text" id="it_opt3_subject" name="it_opt3_subject" value="<?php echo $it['it_opt3_subject']; ?>" /></td>
    <td colspan="3"><input type="text" id="it_opt3" name="it_opt3" value="<?php echo $it['it_opt3']; ?>" style="width: 90%;" /></td>
</tr>
<tr class="ht">
    <td>추가옵션</td>
    <td colspan="3"><input type="radio" id="it_supplement_use" name="it_supplement_use" value="0" <?php if($w == '' || !$it['it_supplement_use']) echo 'checked="checked"'; ?> />등록안함&nbsp;&nbsp;&nbsp;<input type="radio" id="it_supplement_use" name="it_supplement_use" value="1" <?php if($it['it_supplement_use']) echo 'checked="checked"'; ?> />등록함&nbsp;&nbsp;&nbsp;
    <a href="./supplementform.php?w=<?php echo $w; ?>&amp;it_id=<?php echo $it_id; ?>" target="_blank" onclick="javascript: supplementformwindow('<?php echo $w; ?>'); return false;">추가옵션설정</a></td>
</tr>
<tr>
    <td height=80>가격/포인트/재고</td>
    <td colspan=3>
        <table width=100% cellpadding=0 cellspacing=0>
        <tr>
        	<td width=16%>비회원가격 <?=help("상품의 기본판매가격(로그인 이전 가격)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.", 50);?></td>
        	<td width=16%>회원가격 <?=help("상품의 로그인 이후 가격(회원 권한 2 에만 적용)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.\n\n입력이 없다면 비회원가격으로 대신합니다.", 50);?></td>
        	<td width=16%>특별회원가격 <?=help("상품의 로그인 이후 가격(회원 권한 3 이상에 적용)이며 옵션별로 상품가격이 틀리다면 합산하여 상품상세페이지에 출력합니다.\n\n입력이 없다면 회원가격으로 대신합니다.\n회원가격도 없다면 비회원가격으로 대신합니다.", 50);?></td>
        	<td width=16%>시중가격 <?=help("입력하지 않으면 상품상세페이지에 출력하지 않습니다.", 50);?></td>
        	<td width=16%>포인트 <?=help("주문완료후 환경설정에서 설정한 주문완료 설정일 후 회원에게 부여하는 포인트입니다.\n포인트를 사용하지 않는다면 의미가 없습니다.\n또, 포인트부여를 '아니오'로 설정한 경우 신용카드, 계좌이체로 주문하는 회원께는 부여하지 않습니다.", -150);?></td>
        	<td width=16%>재고수량 <?=help("<span style='width:500px'>재고는 규격, 색상별로 관리되지는 않으며 상품별로 관리됩니다.\n이곳에 100개를 설정하고 상품 10개가 주문,준비,배송,완료 상태에 있다면 현재고는 90개로 나타내어집니다.\n주문관리에서 상품별로 상태가 변경될때 재고를 가감하게 됩니다.</span>", -450, -120);?></td>
        </tr>
        <tr>
            <!-- 비회원가 대비 회원가격은 90%, 특별회원가격은 75%로 자동 설정할 경우의 코드
            <td><input type="text" class=ed id="it_amount" name="it_amount" size=8 value='<?=$it[it_amount]?>' style='text-align:right; background-color:#DDE6FE;' onblur="document.fitemform.it_amount2.value=document.fitemform.it_amount.value*.9;document.fitemform.it_amount3.value=document.fitemform.it_amount.value*.75;"></td>
            -->
            <td><input type="text" class=ed id="it_amount" name="it_amount" size=8 value='<?=$it[it_amount]?>' style='text-align:right; background-color:#DDE6FE;'></td>
            <td><input type="text" class=ed id="it_amount2" name="it_amount2" size=8 value='<?=$it[it_amount2]?>' style='text-align:right; background-color:#DDFEDE;'></td>
            <td><input type="text" class=ed id="it_amount3" name="it_amount3" size=8 value='<?=$it[it_amount3]?>' style='text-align:right; background-color:#FEDDDD;'></td>
            <td><input type="text" class=ed id="it_cust_amount" name="it_cust_amount" size=8 value='<?=$it[it_cust_amount]?>' style='text-align:right;'></td>
            <td><input type="text" class=ed id="it_point" name="it_point" size=8 value='<? echo $it[it_point] ?>' style='text-align:right;'> 점</td>
            <td><input type="text" class=ed id="it_stock_qty" name="it_stock_qty" size=8 value='<? echo $it[it_stock_qty] ?>' style='text-align:right;'> 개</td>
        </table>
    </td>
</tr>
<tr class=ht>
    <td>상품구분</td>
    <td><input type="radio" id="it_notax" name="it_notax" value="0" <? if(!$it['it_notax']) echo "checked=\"checked\""; ?> /> 과세상품
        <input type="radio" id="it_notax" name="it_notax" value="1" <? if($it['it_notax']) echo "checked=\"checked\""; ?> /> 면세상품</td>
    <td>쿠폰제외상품</td>
    <td><input type="checkbox" id="it_nocoupon" name="it_nocoupon" value="1" <? if($it['it_nocoupon']) echo "checked=\"checked\""; ?> /> 예</td>
</tr>
<tr class=ht>
    <td>기본설명</td>
    <td colspan=3>
        <input type="text" class=ed id="it_basic" name="it_basic" style='width:97%;' value='<?=get_text($it[it_basic])?>'>
        <?=help("상품상세페이지의 상품설명 상단에 표시되는 설명입니다.\nHTML 입력도 가능합니다.", -150, -100);?>
    </td>
</tr>
<? if ($it['it_id']) { ?>
<?
$sql = " select distinct ii_gubun from {$g4['yc4_item_info_table']} where it_id = '$it_id' group by ii_gubun ";
$ii = sql_fetch($sql, false);
if ($ii) {
    $item_info_gubun = item_info_gubun($ii['ii_gubun']);
    $item_info_gubun .= $item_info_gubun ? " 등록됨" : "";
} else {
    // 상품상세정보 테이블이 없다고 가정하여 생성
    create_table_item_info();
}
?>
<tr class=ht>
    <td>요약상품정보</td>
    <td colspan=3>
        <input type="button" onclick="window.open('./iteminfo.php?it_id=<?=$it['it_id']?>', '_blank', 'width=670 height=800');" value="상품요약정보 설정" />
        <span id="item_info_gubun"><?=$item_info_gubun?></span>
        <?=help("전자상거래 등에서의 상품 등의 정보제공에 관한 고시에 따라 총 35개 상품군에 대해 상품 특성 등을 양식에 따라 입력할 수 있습니다.");?>
    </td>
</tr>
<?}//if?>
<input type="hidden" id="it_explan_html" name="it_explan_html" value=1>
<tr>
    <td>상품설명</td>
    <td colspan=3 style='padding-top:7px; padding-bottom:7px;'><?=editor_html('it_explan', $it[it_explan]);?></td>
</tr>
<? if($default['de_send_cost_case'] == "개별배송") { ?>
<tr class="ht">
    <td>배송비설정</td>
    <td colspan="3">
        <table width="100%" cellpadding="0" cellspacing="0">
        <colgroup width="20%"></colgroup>
        <colgroup width="40%"></colgroup>
        <colgroup width="20%"></colgroup>
        <colgroup width="20%"></colgroup>
        <tr class="ht">
            <td align="center">배송비유형</td>
            <td align="center">상세조건</td>
            <td align="center">기본배송비</td>
            <td align="center">결제방법</td>
        </tr>
        <tr class="ht">
            <td><input type="radio" id="it_sc_type" name="it_sc_type" value="0" <? if(!$it['it_sc_type'] || $w == '') echo 'checked="checked"'; ?> />무료배송</td>
            <td>무조건 무료배송</td>
            <td align="center">0원</td>
            <td></td>
        </tr>
        <tr class="ht">
            <td><input type="radio" id="it_sc_type" name="it_sc_type" value="1" <? if($it['it_sc_type'] == 1) echo 'checked="checked"'; ?> />조건부 무료</td>
            <td>상품구매액 함계 <input type="text" class="ed" id="it_minimum" name="it_minimum" size="5" value="<? if($it['it_sc_type'] == 1) echo $it['it_sc_condition']; ?>" />원 이상 무료배송</td>
            <td rowspan="3" align="center"><input type="text" class="ed" id="it_sc_basic" name="it_sc_basic" size="5" value="<? echo $it['it_sc_basic']; ?>" /> 원</td>
            <td rowspan="3" align="center">
                <select id="it_sc_method" name="it_sc_method">
                    <option value="0" <? if(!$it['it_sc_method']) echo 'selected="selected"'; ?>>선불</option>
                    <option value="1" <? if($it['it_sc_method'] == 1) echo 'selected="selected"'; ?>>착불</option>
                    <option value="2" <? if($it['it_sc_method'] == 2) echo 'selected="selected"'; ?>>선불 또는 착불</option>
                </select>
            </td>
        </tr>
        <tr class="ht">
            <td><input type="radio" id="it_sc_type" name="it_sc_type" value="2" <? if($it['it_sc_type'] == 2) echo 'checked="checked"'; ?> />유료배송</td>
            <td>고정배송비 부과</td>
        </tr>
        <tr class="ht">
            <td><input type="radio" id="it_sc_type" name="it_sc_type" value="3" <? if($it['it_sc_type'] == 3) echo 'checked="checked"'; ?> />수량별 부과</td>
            <td>수량 <input type="text" class="ed" id="it_count" name="it_count" size="5" value="<? if($it['it_sc_type'] == 3) echo $it['it_sc_condition']; ?>" />개마다 반복부과</td>
        </tr>
        </table>
    </td>
</tr>
<? } ?>
<tr class=ht>
    <td>판매자 e-mail</td>
    <td colspan=3>
        <input type="text" class=ed id="it_sell_email" name="it_sell_email" size=40 value='<? echo $it[it_sell_email] ?>'>
        <?=help("운영자와 판매자가 다른 경우 이곳에 판매자의 e-mail을 입력해 놓으면 이 상품이 주문되는 시점에서 판매자에게 별도의 주문서 메일을 발송합니다.");?>
    </td>
</tr>
<tr class=ht>
    <td>전화문의</td>
    <td>
        <input type="checkbox" id="it_tel_inq" name="it_tel_inq" <? echo ($it[it_tel_inq]) ? "checked" : ""; ?> value='1'> 예
        <?=help("상품 금액 대신 전화문의로 표시됩니다.");?>
    </td>
    <td>판매가능</td>
    <td>
        <input type="checkbox" id="it_use" name="it_use" <? echo ($it[it_use]) ? "checked" : ""; ?> value='1'> 예
        <?=help("잠시 판매를 중단하거나 재고가 없을 경우에 체크하면 이 상품은 출력하지 않으며 주문도 할 수 없습니다.");?>
    </td>
</tr>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p>
<?=subtitle("이미지")?>
<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=4 height=2 bgcolor=0E87F9></td></tr>
<? for ($i=1; $i<=10; $i++) { // 이미지(대)는 10개 ?>
<tr class=ht>
    <td>이미지(대) <?=$i?></td>
    <td colspan=3>
        <input type="file" class=ed id="it_img" name="it_img"<?=$i?> size=40>
        <?
        $idx = 'it_img'.$i;
        $img = G4_DATA_PATH."/item/$it_id/{$it[$idx]}";
        $img_url = G4_DATA_URL."/item/$it_id/{$it[$idx]}";
        if (file_exists($img) && is_file($img)) {
            $size = getimagesize($img);
            echo "<img src='".G4_ADMIN_URL."/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('img$i', $size[0], $size[1]);\"><input type=\"checkbox\" id=\"it_img\" name=\"it_img{$i}_del\" value=\"1\">삭제";
            echo "<span id=\"img{$i}\" style=\"left:0; top:0; z-index:+1; display:none; position:absolute;\"><img src=\"$img_url\" border=1></div>";
        }
        ?>
    </td>
</tr>
<? } ?>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<p align=center>
    <input type="submit" class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type="button" class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./itemlist.php?<?=$qstr?>';">

<p>
<?=subtitle("선택정보")?>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<colgroup width=14%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<colgroup width=3 bgcolor=#FFFFFF></colgroup>
<colgroup width=13%></colgroup>
<colgroup width=35% bgcolor=#FFFFFF></colgroup>
<tr><td colspan=5 height=2 bgcolor=0E87F9></td></tr>
<tr>
	<td colspan=5>
		<table width=100% cellpadding=0 cellspacing=0>
			<tr class=ht align=center>
				<td width=50%><b>선택된 목록</b></td>
                <td width=50%><b>등록된 목록</b></td>
			</tr>
		</table>
	</td>
</tr>
<tr>
    <td align="center">
        선택된 관련상품
        <?=help("오른쪽 등록된 목록의 상품목록에서 더블클릭하면 선택된 관련상품에 추가됩니다.\n만약, 이 상품이 a 이고 b 라는 상품을 관련상품으로 등록하면 b 라는 상품에도 a 라는 상품을 관련상품으로 자동 등록합니다.\n반드시 아래의 확인버튼을 클릭하셔야 정상 등록되므로 이점 유의하여 주십시오", -100);?><br><span id="sel_span" style="line-height:200%"></span>
    </td>
    <td>
        ※ 상품 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>※ 한 번 클릭시 상품이미지/상품금액 출력<br>
        <br>
        <select id="relationselect" name="relationselect" size=8 style='width:250px;' onclick="relation_img(this.value, 'sel_span')" ondblclick="relation_del(this);">
        <?
        $str = array();
        $sql = " select b.ca_id, b.it_id, b.it_name, b.it_amount, b.it_img1, b.it_img2, b.it_img3, b.it_img4, b.it_img5, b.it_img6, b.it_img7, b.it_img8, b.it_img9, b.it_img10
                   from {$g4['yc4_item_relation_table']} a
                   left join {$g4['yc4_item_table']} b on (a.it_id2 = b.it_id)
                  where a.it_id = '$it_id'
                  order by b.ca_id, b.it_name ";
        $result = sql_query($sql);
        while($row=sql_fetch_array($result))
        {
            $sql2 = " select ca_name from {$g4['yc4_category_table']} where ca_id = '{$row['ca_id']}' ";
            $row2 = sql_fetch($sql2);

			// 상품이미지썸네일
            $it_image = "";
            for($k=1;$k<=10; $k++) {
                $idx = 'it_img'.$k;
                $filepath = G4_DATA_PATH.'/item/'.$row['it_id'];
                $filename = $row[$idx];

                if(file_exists($filepath.'/'.$filename) && $filename != "") {
                    $it_image = $filename;
                    break;
                }
            }

            echo "<option value='{$row['it_id']}/$it_image/{$row['it_amount']}'>{$row2['ca_name']} : ".cut_str(get_text(strip_tags($row['it_name'])),30);
            $str[] = $row['it_id'];
        }
		$str = implode(",", $str);
        ?>
        </select>
        <input type="hidden" id="it_list" name="it_list" value='<?=$str?>'>
    </td>
	<td rowspan=2 width=20 bgcolor=#FFFFFF>◀</td>
    <td align="center">상품목록<br><span id="add_span" style="line-height:200%"></span></td>
    <td>
        ※ 상품 선택후 <FONT COLOR="#0E87F9">더블클릭하면 왼쪽에 추가됨</FONT><br>※ 한 번 클릭시 상품이미지/상품금액 출력<br>
        <select onchange="search_relation(this)">
        <option value=''>분류별 관련상품
        <option value=''>----------------------
        <?
            $sql = " select ca_id, ca_name from {$g4['yc4_category_table']} where length(ca_id) = 2 order by ca_id ";
            $result = sql_query($sql);
            for ($i=0; $row=sql_fetch_array($result); $i++)  {
                echo "<option value='$row[ca_id]'>$row[ca_name]\n";
            }
        ?>
        </select><br>
        <select  id="relation" size=8 style='width:250px; background-color:#F6F6F6;' onclick="relation_img(this.value, 'add_span')" ondblclick="relation_add(this);">
        </select>
        <script>
            function search_relation(fld)
            {
                var ca_id = fld.value;
                if(ca_id) {
                    $.post(
                        './itemformrelation.php',
                        { it_id: '<?=$it_id?>', ca_id: ca_id },
                        function(data) {
                            if(data) {
                                $("#relation").html(data);
                            }
                        }
                    );
                }
            }

			// 김선용 2006.10
			function relation_img(name, id)
			{
				var item_image_url = "";
                if(!name) return;
				temp = name.split("/");
				if(temp[1] == ''){
					temp[1] = "no_image.gif";
					item_image_url = "<?=G4_SHOP_IMG_URL?>";
				} else {
                    item_image_url = "<?=G4_DATA_URL?>/item/"+temp[0];
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
        </SCRIPT>
    </td>
</tr>

<script> var eventselect = new Array(); </script>
<tr>
    <td>
        선택된 이벤트<br>
        <?=help("오른쪽 등록된 목록의 이벤트목록에서 더블클릭하면 선택된 이벤트에 추가됩니다.\n이벤트는 분류가 다른 상품들을 묶을 수 있는 또다른 방법입니다.\n이벤트목록은 이벤트관리에서 등록한 내용이 나타납니다.\n반드시 아래의 확인버튼을 클릭하셔야 정상 등록되므로 이점 유의하여 주십시오", -100);?>
    </td>
    <td>
        이벤트 선택후 <FONT COLOR="#FF6600">더블클릭하면 삭제됨</FONT><br>
        <select id="eventselect" name="eventselect" size=6 style='width:250px;' ondblclick="event_del(this);">
        <?
        $str = "";
        $comma = "";
        $sql = " select b.ev_id, b.ev_subject
                   from $g4[yc4_event_item_table] a
                   left join $g4[yc4_event_table] b on (a.ev_id=b.ev_id)
                  where a.it_id = '$it_id'
                  order by b.ev_id desc ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo "<option value='$row[ev_id]'>".get_text($row[ev_subject]);
            $str .= $comma . $row[ev_id];
            $comma = ",";
        }
        ?>
        </select>
        <input type="hidden" id="ev_list" name="ev_list" value='<?=$str?>'>
    </td>
    <td>이벤트목록</td>
    <td>
        이벤트 선택후 <FONT COLOR="#0E87F9">더블클릭하면 왼쪽에 추가됨</FONT><br>
        <select size=6 style='width:250px; background-color:#F6F6F6;' ondblclick="event_add(this);">
        <?
        $sql = " select ev_id, ev_subject from $g4[yc4_event_table] order by ev_id desc ";
        $result = sql_query($sql);
        while ($row=sql_fetch_array($result)) {
            echo "<option value='$row[ev_id]'>".get_text($row[ev_subject]);
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
    </td>
</tr>
</table>

<table width=100% cellpadding=0 cellspacing=0>
<colgroup width=15%></colgroup>
<colgroup width=85% bgcolor=#FFFFFF></colgroup>
<tr class=ht>
    <td>상단이미지</td>
    <td colspan=3>
        <input type="file" class=ed id="it_himg" name="it_himg" size=40>
        <?
        $himg_str = "";
        $himg = G4_DATA_PATH."/item/{$it[it_id]}_h";
        if (file_exists($himg)) {
            echo "<input type=\"checkbox\" id=\"it_himg_del\" name=\"it_himg_del\" value=\"1\">삭제";
            $himg_str = "<img src='$himg' border=0>";
        }
        ?>
        <?=help("상품상세설명 페이지 상단에 출력하는 이미지입니다.");?>
    </td>
</tr>
<? if ($himg_str) { echo "<tr><td colspan=4>$himg_str</td></tr>"; } ?>

<tr class=ht>
    <td>하단이미지</td>
    <td colspan=3>
        <input type="file" class=ed id="it_timg" name="it_timg" size=40>
        <?
        $timg_str = "";
        $timg = G4_DATA_PATH."/item/{$it[it_id]}_t";
        if (file_exists($timg)) {
            echo "<input type=\"checkbox\" id=\"it_timg_del\" name=\"it_timg_del\" value=\"1\">삭제";
            $timg_str = "<img src='$timg' border=0>";
        }
        ?>
        <?=help("상품상세설명 페이지 하단에 출력하는 이미지입니다.");?>
    </td>
</tr>
<? if ($timg_str) { echo "<tr><td colspan=4>$timg_str</td></tr>"; } ?>

<tr>
    <td>상품상단내용 <?=help("상품상세설명 페이지 상단에 출력하는 HTML 내용입니다.", -150);?></td>
    <td colspan=3 align=right style='padding-top:7px; padding-bottom:7px;'><?=editor_html('it_head_html', $it[it_head_html]);?></td>
</tr>
<tr>
    <td>상품하단내용 <?=help("상품상세설명 페이지 상단에 출력하는 HTML 내용입니다.", -150);?></td>
    <td colspan=3 align=right style='padding-top:7px; padding-bottom:7px;'><?=editor_html('it_tail_html', $it[it_tail_html]);?></td>
</tr>

<? if ($w == "u") { ?>
<tr class=ht>
    <td>입력일시</td>
    <td colspan=3>
        <?=$it[it_time]?>
        <?=help("상품을 처음 입력(등록)한 시간입니다.");?>
    </td>
</tr>
<? } ?>
<tr><td colspan=4 height=1 bgcolor=#CCCCCC></td></tr>
</table><br>


<p align=center>
    <input type="submit" class=btn1 accesskey='s' value='  확  인  '>&nbsp;
    <input type="button" class=btn1 accesskey='l' value='  목  록  ' onclick="document.location.href='./itemlist.php?<?=$qstr?>';">
</form>

<script language='javascript'>
var f = document.fitemform;

<?php if($w == 'u') { ?>
$(document).ready(function() {
    // 선택옵션등록 변경
    $("input[name=it_option_use]").click(function() {
        var val = $(this).val();
        if(val == "0") {
            if(!confirm("기존의 선택옵션정보가 삭제됩니다. 계속 하시겠습니까?")) {
                $("input[name=it_option_use]").filter("input[value=1]").attr("checked", true);
            } else {
                $("input[name^=it_opt]:text").val('');
            }
        }
    });

    // 추가옵션등록 변경
    $("input[name=it_supplement_use]").click(function() {
        var val = $(this).val();
        if(val == "0") {
            if(!confirm("기존의 추가옵션정보가 삭제됩니다. 계속 하시겠습니까?")) {
                $("input[name=it_supplement_use]").filter("input[value=1]").attr("checked", true);
            }
        }
    });
});
<?php } ?>

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
        function(data)
        {
            if(data) {
                alert("코드 "+id+" 는 '"+data+"' (으)로 이미 등록되어 있으므로\n\n사용하실 수 없습니다.");
                return false;
            } else {
                alert("'"+id+"' 은(는) 등록된 코드가 없으므로 사용하실 수 있습니다.");
                f.codedup.value = "";
            }
        }
    );
}

// 선택옵션창
function optionformwindow()
{
    var it_id = $.trim($('input[name=it_id]').val());
    if (!it_id) {
        alert('상품코드를 입력하십시오.');
        f.it_id.focus();
        return;
    }

    $('input[name=it_option_use]').filter('input[value=1]').attr('checked', true);
    window.open("./optionform.php?w=<? echo $w; ?>&it_id="+it_id, "optionform", "width=700, height=700, left=100, top=50, scrollbars=yes");
}

// 추가옵션창
function supplementformwindow()
{
    var it_id = $.trim($('input[name=it_id]').val());
    if (!it_id) {
        alert('상품코드를 입력하십시오.');
        f.it_id.focus();
        return;
    }

    $('input[name=it_supplement_use]').filter('input[value=1]').attr('checked', true);
    window.open("./supplementform.php?w=<? echo $w; ?>&it_id="+it_id, "supplementform", "width=700, height=700, left=100, top=50, scrollbars=yes");
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

    // 개별배송비체크
    var sc_type = $("input[name=it_sc_type]:checked").val();
    var sc_basic = $("input[name=it_sc_basic]").val();
    var patt = /[^0-9]/g;

    if(sc_type == "1") { // 조건부무료
        var minimum = $("input[name=it_minimum]").val().replace(patt, "");
        if(minimum == "") {
            alert("구매금액 합계를 입력해 주세요.");
            return false;
        }
    } else if(sc_type == "3") { // 수량별
        var count = $("input[name=it_count]").val().replace(patt, "");
        if(count == "") {
            alert("반복수량을 입력해 주세요.");
            return false;
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
        //f.it_opt1_subject.value = ca_opt1_subject[idx];
        //f.it_opt2_subject.value = ca_opt2_subject[idx];
        //f.it_opt3_subject.value = ca_opt3_subject[idx];
        //f.it_opt4_subject.value = ca_opt4_subject[idx];
        //f.it_opt5_subject.value = ca_opt5_subject[idx];
        //f.it_opt6_subject.value = ca_opt6_subject[idx];
    }
}

categorychange(document.fitemform);

document.fitemform.it_name.focus();
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
