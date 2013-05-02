<?php
include_once('./_common.php');
include_once(G4_LIB_PATH.'/iteminfo.lib.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');

$captcha_html = captcha_html();

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

$rand = rand(4, 6);
$norobot_key = substr($token, 0, $rand);
set_session('ss_norobot_key', $norobot_key);

// 분류사용, 상품사용하는 상품의 정보를 얻음
$sql = " select a.*,
                b.ca_name,
                b.ca_use
           from {$g4['shop_item_table']} a,
                {$g4['shop_category_table']} b
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
           from {$g4['shop_category_table']}
          where ca_id = '{$it['ca_id']}' ";
$ca = sql_fetch($sql);

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
    sql_query(" update {$g4['shop_item_table']} set it_hit = it_hit + 1 where it_id = '$it_id' "); // 1증가
    set_cookie("ck_it_id", $it_id, time() + 3600); // 1시간동안 저장
}

$g4['title'] = $it['it_name'].' &gt; '.$it['ca_name'];

// 분류 상단 코드가 있으면 출력하고 없으면 기본 상단 코드 출력
if ($ca['ca_include_head'])
    @include_once($ca['ca_include_head']);
else
    include_once('./_head.php');

// 분류 위치
// HOME > 1단계 > 2단계 ... > 6단계 분류
$ca_id = $it['ca_id'];
include G4_SHOP_PATH.'/navigation1.inc.php';

// 이 분류에 속한 하위분류 출력
include G4_SHOP_PATH.'/listcategory.inc.php';

$himg = G4_DATA_PATH.'/item/'.$it_id.'_h';
if (file_exists($himg))
    echo '<div id="sit_himg" class="sit_img"><img src="'.G4_DATA_URL.'/item/'.$it_id.'_h" alt=""></div>';

// 상단 HTML
echo '<div id="sit_hhtml">'.stripslashes($it['it_head_html']).'</div>';

// 이전 상품보기
$sql = " select it_id, it_name from {$g4['shop_item_table']}
          where it_id > '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id asc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $prev_title = '이전상품보기 '.$row['it_name'];
    $prev_href = '<a href="./item.php?it_id='.$row['it_id'].'">';
} else {
    $prev_title = '이전상품없음';
    $prev_href = '';
}

// 다음 상품보기
$sql = " select it_id, it_name from {$g4['shop_item_table']}
          where it_id < '$it_id'
            and SUBSTRING(ca_id,1,4) = '".substr($it['ca_id'],0,4)."'
            and it_use = '1'
          order by it_id desc
          limit 1 ";
$row = sql_fetch($sql);
if ($row['it_id']) {
    $next_title = '다음상품보기 '.$row['it_name'];
    $next_href = '<a href="./item.php?it_id='.$row['it_id'].'">';
} else {
    $next_title = '다음상품없음';
    $next_href = '';
}

// 관련상품의 갯수를 얻음
$sql = " select count(*) as cnt
           from {$g4['shop_item_relation_table']} a
           left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id and b.it_use='1')
          where a.it_id = '{$it['it_id']}' ";
$row = sql_fetch($sql);
$item_relation_count = $row['cnt'];
?>

<script src="<?php echo G4_JS_URL; ?>/shop.js"></script>
<script src="<?php echo G4_JS_URL; ?>/md5.js"></script>

<?php
if (G4_HTTPS_DOMAIN)
    $action_url = G4_HTTPS_DOMAIN.'/'.G4_SHOP_DIR.'/cartupdate.php';
else
    $action_url = './cartupdate.php';
?>

<form name="fitem" action="<?php echo $action_url; ?>" method="post">
<input type="hidden" name="it_id" value="<?php echo $it['it_id']; ?>">
<input type="hidden" name="it_name" value="<?php echo $it['it_name']; ?>">
<input type="hidden" name="sw_direct">
<input type="hidden" name="url">

<!-- item title --><?php echo it_name_icon($it, stripslashes($it['it_name']), 0); ?>

<?php
if ($is_admin)
    echo '<div class="sit_admin"><a href="'.G4_ADMIN_URL.'/shop_admin/itemform.php?w=u&it_id='.$it_id.'">상품 관리</a></div>';
?>

<!-- 이미지 미리보기 -->
<div>
    <?php
    $middle_image = $it['it_id']."_m";
    ?>
    <?php echo get_large_image($it['it_id']."_l1", $it['it_id'], false); ?><?php echo get_it_image($middle_image); ?>
    <?php
    for ($i=1; $i<=5; $i++)
    {
        if (file_exists(G4_DATA_PATH."/item/{$it_id}_l{$i}"))
        {
            echo get_large_image("{$it_id}_l{$i}", $it['it_id'], false);
            if ($i==1 && file_exists(G4_DATA_PATH."/item/{$it_id}_m"))
                echo "<img id=\"middle{$i}\" src=\"".G4_DATA_URL."/item/{$it_id}_m\" border=\"0\" width=\"40\" height=\"40\" style=\"border:1px solid #E4E4E4;\" ";
            else
                echo "<img id=\"middle{$i}\" src=\"".G4_DATA_URL."/item/{$it_id}_l{$i}\" border=\"0\" width=\"40\" height=\"40\" style=\"border:1px solid #E4E4E4;\" ";
            echo " onmouseover=\"document.getElementById('$middle_image').src=document.getElementById('middle{$i}').src;\">";
            echo "</a> &nbsp;";
        }
    }
    ?>
    <?php echo $prev_href; ?><img src='<?php echo G4_SHOP_URL; ?>/img/prev.gif' border=0 title='<?php echo $prev_title; ?>'></a>
    <?php echo get_large_image($it['it_id']."_l1", $it['it_id']); ?>
    <?php echo $next_href; ?><img src='<?php echo G4_SHOP_URL; ?>/img/next.gif' border=0 title='<?php echo $next_title; ?>'></a>
</div>

<!-- 상품간략정보 및 구매 -->
<div>
        <table width=430 cellpadding=0 cellspacing=0 background='<?php echo G4_SHOP_URL; ?>/img/bg_item.gif'>
        <?php if ($score = get_star_image($it['it_id'])) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 고객선호도</td>
            <td align=center>:</td>
            <td><img src='<?php echo G4_SHOP_URL."/img/star{$score}.gif"; ?>' border=0></td></tr>
        <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>
        <?php } ?>


        <?php if ($it['it_maker']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 제조사</td>
            <td align=center>:</td>
            <td><?php echo $it['it_maker']; ?></td></tr>
        <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>
        <?php } ?>


        <?php if ($it['it_origin']) { ?>
        <tr>
            <td height=25>&nbsp;&nbsp;&nbsp; · 원산지</td>
            <td align=center>:</td>
            <td><?php echo $it['it_origin']; ?></td></tr>
        <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>
        <?php } ?>


        <?php
        // 선택옵션 출력
        for ($i=1; $i<=6; $i++)
        {
            // 옵션에 문자가 존재한다면
            $str = get_item_options(trim($it["it_opt{$i}_subject"]), trim($it["it_opt{$i}"]), $i);
            if ($str)
            {
                echo "<tr height=25>";
                echo "<td>&nbsp;&nbsp;&nbsp; · ".$it["it_opt{$i}_subject"]."</td>";
                echo "<td align=center>:</td>";
                echo "<td style='word-break:break-all;'>$str</td></tr>\n";
                echo "<tr><td colspan=3 height=1 background='".G4_SHOP_URL."/img/dot_line.gif'></td></tr>\n";
            }
        }
        ?>


        <?php if (!$it['it_gallery']) { // 갤러리 형식이라면 가격, 구매하기 출력하지 않음 ?>

            <?php if ($it['it_tel_inq']) { // 전화문의일 경우 ?>

                <tr>
                    <td height=25>&nbsp;&nbsp;&nbsp; · 판매가격</td>
                    <td align=center>:</td>
                    <td><FONT COLOR="#FF5D00">전화문의</FONT></td></tr>
                <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>

            <?php } else { ?>

                <?php if ($it['it_cust_amount']) { // 1.00.03 ?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 시중가격</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_cust_amount size=12 style='text-align:right; border:none; border-width:0px; font-weight:bold; width:80px; color:#777777; text-decoration:line-through;' readonly value='<?php echo number_format($it['it_cust_amount']); ?>'> 원</td>
                </tr>
                <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>
                <?php } ?>


                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 판매가격</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_sell_amount size=12 style='text-align:right; border:none; border-width:0px; font-weight:bold; width:80px; font-family:Tahoma;' class=amount readonly> 원
                        <input type=hidden name=it_amount value='0'>
                    </td>
                </tr>
                <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>

                <?php
                /* 재고를 표시하는 경우 주석을 풀어주세요.
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 재고수량</td>
                    <td align=center>:</td>
                    <td><?php echo number_format(get_it_stock_qty($it_id)); ?> 개</td>
                </tr>
                <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>
                */
                ?>

                <?php if ($config['cf_use_point']) { // 포인트 사용한다면 ?>
                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 포 인 트</td>
                    <td align=center>:</td>
                    <td><input type=text name=disp_point size=12 style='text-align:right; border:none; border-width:0px; width:80px;' readonly> 점
                        <input type=hidden name=it_point value='0'>
                    </td>
                </tr>
                <tr><td colspan=3 height=1 background='<?php echo G4_SHOP_URL; ?>/img/dot_line.gif'></td></tr>
                <?php } ?>

                <tr height=25>
                    <td>&nbsp;&nbsp;&nbsp; · 수 량</td>
                    <td align=center>:</td>
                    <td>
                        <input type=text name=ct_qty value='1' size=4 maxlength=4 class=ed autocomplete='off' style='text-align:right;' onkeyup='amount_change()'>
                        <img src='<?php echo G4_SHOP_URL; ?>/img/qty_control.gif' border=0 align=absmiddle usemap="#qty_control_map"> 개
                        <map name="qty_control_map">
                        <area shape="rect" coords="0, 0, 10, 9" href="javascript:qty_add(+1);">
                        <area shape="rect" coords="0, 10, 10, 19" href="javascript:qty_add(-1);">
                        </map></td>
                </tr>
            <?php } ?>

        <?php } ?>


        <tr><td colspan=3><img src='<?php echo G4_SHOP_URL; ?>/img/itembox_02.gif' width=430></td></tr>


        </table>

        <?php if (!$it['it_tel_inq'] && !$it['it_gallery']) { ?>
            <a href="javascript:fitemcheck(document.fitem, 'direct_buy');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn2_now_buy.gif' border=0></a>
            <a href="javascript:fitemcheck(document.fitem, 'cart_update');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn2_cart.gif' border=0></a>
            <?php } ?>

            <?php if (!$it['it_gallery']) { ?>
            <a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn2_wish.gif' border=0></a>
            <a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn_item_recommend.gif' border=0></a>
            <?php } ?>

            <script language="JavaScript">
            // 상품보관
            function item_wish(f, it_id)
            {
                f.url.value = "<?php echo G4_SHOP_URL; ?>/wishupdate.php?it_id="+it_id;
                f.action = "<?php echo G4_SHOP_URL; ?>/wishupdate.php";
                f.submit();
            }

            // 추천메일
            function popup_item_recommend(it_id)
            {
                if (!g4_is_member)
                {
                    if (confirm("회원만 추천하실 수 있습니다."))
                        document.location.href = "<?php echo G4_BBS_URL; ?>/login.php?url=<?php echo urlencode(G4_SHOP_URL."/item.php?it_id=$it_id"); ?>";
                }
                else
                {
                    url = "./itemrecommend.php?it_id=" + it_id;
                    opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
                    popup_window(url, "itemrecommend", opt);
                }
            }
            </script>
</div>

<!-- pg_anchor -->
<div>
            <!-- 상품정보 --><td><a href="javascript:click_item('*');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn_tab01.gif' border=0></a></td>
            <!-- 사용후기 --><td width=109 background='<?php echo G4_SHOP_URL; ?>/img/btn_tab02.gif' border=0 style='padding-top:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:click_item('item_use');" style="cursor:pointer;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=small style='color:#ff5d00;'>(<span id=item_use_count>0</span>)</span></a></td>
            <!-- 상품문의 --><td width=109 background='<?php echo G4_SHOP_URL; ?>/img/btn_tab03.gif' border=0 style='padding-top:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:click_item('item_qa');" style="cursor:pointer;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=small style='color:#ff5d00;'>(<span id=item_qa_count>0</span>)</span></a></td>
            <?php if ($default['de_baesong_content']) { ?><!-- 배송정보 --><td><a href="javascript:click_item('item_baesong');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn_tab04.gif' border=0></a></td><?php } ?>
            <?php if ($default['de_change_content']) { ?><!-- 교환/반품 --><td><a href="javascript:click_item('item_change');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn_tab05.gif' border=0></a></td><?php } ?>
            <!-- 관련상품 --><td width=109 background='<?php echo G4_SHOP_URL; ?>/img/btn_tab06.gif' border=0 style='padding-top:2px;'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="javascript:click_item('item_relation');" style="cursor:pointer;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class=small style='color:#ff5d00;'>(<span id=item_relation_count>0</span>)</span></a>
</div>
</form>


<script>
function click_item(id)
{
    <?php
    echo "var str = 'item_explan,item_use,item_qa";
    if ($default['de_baesong_content']) echo ",item_baesong";
    if ($default['de_change_content']) echo ",item_change";
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
</script>



<!-- 상품설명 -->
<div id='item_explan' style='display:block;'>
    <?php
    $sql = " select * from {$g4['shop_item_info_table']} where it_id = '$it_id' order by ii_id ";
    $result = sql_query($sql, false);
    if (@mysql_num_rows($result)) {
    ?>
    <!-- 상품정보고시 -->
    <table class="item_info_open">
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++) {
    ?>
    <tr valign="top">
        <th scope="row" style="padding:8px 15px;width:30%;border-bottom:1px solid #f6dbab;background:#f8f4ee;font-size:12px;text-align:left"><?php echo $row['ii_title']; ?></th>
        <td style="padding:8px 15px;border-bottom:1px solid #f6dbab"><?php echo $row['ii_value']; ?></th>
    </tr>
    <?php } //for?>
    </tbody>
    </table>
    <!-- 상품정보고시 end -->
    <?php } //if?>

    <table width=100% cellspacing=0 border=0>
    <?php if ($it['it_basic']) { ?>
    <?php echo $it['it_basic']; ?>
    <?php } ?>

    <?php if ($it['it_explan']) { ?>
    <div id='div_explan'><?php echo conv_content($it['it_explan'], 1); ?></div>
    <?php } ?>
</div>
<!-- 상품설명 end -->



<?php
// 사용후기
$use_page_rows = 10;    // 사용후기 페이지당 목록수
include_once('./itemuse.inc.php');


// 상품문의
$qa_page_rows = 10;     // 상품문의 페이지당 목록수
include_once('./itemqa.inc.php');
?>


<?php if ($default['de_baesong_content']) { // 배송정보 내용이 있다면 ?>
<!-- 배송정보 -->
<div id='item_baesong' style='display:block;'>
<?php echo conv_content($default['de_baesong_content'], 1); ?>
</div>
<!-- 배송정보 end -->
<?php } ?>


<?php if ($default['de_change_content']) { // 교환/반품 내용이 있다면 ?>
<!-- 교환/반품 -->
<div id='item_change' style='display:block;'>
<?php echo conv_content($default['de_change_content'], 1); ?>
</div>
<!-- 교환/반품 end -->
<?php } ?>


<!-- 관련상품 -->
<div id='item_relation' style='display:block;'>
        <?php
        $list_mod   = $default['de_rel_list_mod'];
        $img_width  = $default['de_rel_img_width'];
        $img_height = $default['de_rel_img_height'];
        $td_width = (int)(100 / $list_mod);

        $sql = " select b.*
                   from {$g4['shop_item_relation_table']} a
                   left join {$g4['shop_item_table']} b on (a.it_id2=b.it_id)
                  where a.it_id = '{$it['it_id']}'
                    and b.it_use='1' ";
        $result = sql_query($sql);
        $num = @mysql_num_rows($result);
        if ($num)
            include G4_SHOP_PATH.'/maintype10.inc.php';
        else
            echo '이 상품과 관련된 상품이 없습니다.';
        ?>
</div>
<!-- 관련상품 end -->

<script>
function qty_add(num)
{
    var f = document.fitem;
    var qty = parseInt(f.ct_qty.value);
    if (num < 0 && qty <= 1)
    {
        alert("수량은 1 이상만 가능합니다.");
        qty = 1;
    }
    else if (num > 0 && qty >= 9999)
    {
        alert("수량은 9999 이하만 가능합니다.");
        qty = 9999;
    }
    else
    {
        qty = qty + num;
    }

    f.ct_qty.value = qty;

    amount_change();
}

function get_amount(data)
{
    var str = data.split(";");
    var num = parseInt(str[1]);
    if (isNaN(num)) {
        return 0;
    } else {
        return num;
    }
}

function amount_change()
{
    var basic_amount = parseInt("<?php echo get_amount($it); ?>");
    var basic_point  = parseFloat("<?php echo $it['it_point']; ?>");
    var cust_amount  = parseFloat("<?php echo $it['it_cust_amount']; ?>");

    var f = document.fitem;
    var opt1 = 0;
    var opt2 = 0;
    var opt3 = 0;
    var opt4 = 0;
    var opt5 = 0;
    var opt6 = 0;
    var ct_qty = 0;

    if (typeof(f.ct_qty) != 'undefined')
        ct_qty = parseInt(f.ct_qty.value);

    if (typeof(f.it_opt1) != 'undefined') opt1 = get_amount(f.it_opt1.value);
    if (typeof(f.it_opt2) != 'undefined') opt2 = get_amount(f.it_opt2.value);
    if (typeof(f.it_opt3) != 'undefined') opt3 = get_amount(f.it_opt3.value);
    if (typeof(f.it_opt4) != 'undefined') opt4 = get_amount(f.it_opt4.value);
    if (typeof(f.it_opt5) != 'undefined') opt5 = get_amount(f.it_opt5.value);
    if (typeof(f.it_opt6) != 'undefined') opt6 = get_amount(f.it_opt6.value);

    var amount = basic_amount + opt1 + opt2 + opt3 + opt4 + opt5 + opt6;
    var point  = parseInt(basic_point);

    if (typeof(f.it_amount) != 'undefined')
        f.it_amount.value = amount;

    if (typeof(f.disp_sell_amount) != 'undefined')
        f.disp_sell_amount.value = number_format(String(amount * ct_qty));

    if (typeof(f.disp_cust_amount) != 'undefined')
        f.disp_cust_amount.value = number_format(String(cust_amount * ct_qty));

    if (typeof(f.it_point) != 'undefined') {
        f.it_point.value = point;
        f.disp_point.value = number_format(String(point * ct_qty));
    }
}

<?php if (!$it['it_gallery']) { echo "amount_change();"; } // 처음시작시 한번 실행 ?>

// 바로구매 또는 장바구니 담기
function fitemcheck(f, act)
{
    // 판매가격이 0 보다 작다면
    if (f.it_amount.value < 0)
    {
        alert("전화로 문의해 주시면 감사하겠습니다.");
        return;
    }

    for (i=1; i<=6; i++)
    {
        if (typeof(f.elements["it_opt"+i]) != 'undefined')
        {
            if (f.elements["it_opt"+i].value == '선택') {
                alert(f.elements["it_opt"+i+"_subject"].value + '을(를) 선택하여 주십시오.');
                f.elements["it_opt"+i].focus();
                return;
            }
        }
    }

    if (act == "direct_buy") {
        f.sw_direct.value = 1;
    } else {
        f.sw_direct.value = 0;
    }

    if (!f.ct_qty.value) {
        alert("수량을 입력해 주십시오.");
        f.ct_qty.focus();
        return;
    } else if (isNaN(f.ct_qty.value)) {
        alert("수량을 숫자로 입력해 주십시오.");
        f.ct_qty.select();
        f.ct_qty.focus();
        return;
    } else if (parseInt(f.ct_qty.value) < 1) {
        alert("수량은 1 이상 입력해 주십시오.");
        f.ct_qty.focus();
        return;
    }

    amount_change();

    f.submit();
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
    document.getElementById("item_use_count").innerHTML = "<?php echo $use_total_count; ?>";
if (document.getElementById("item_qa_count"))
    document.getElementById("item_qa_count").innerHTML = "<?php echo $qa_total_count; ?>";
if (document.getElementById("item_relation_count"))
    document.getElementById("item_relation_count").innerHTML = "<?php echo $item_relation_count; ?>";

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

<?php
// 하단 HTML
echo stripslashes($it['it_tail_html']);

$timg = G4_DATA_PATH."/item/{$it_id}_t";
if (file_exists($timg))
    echo "<img src='".G4_DATA_URL."/item/{$it_id}_t' border=0><br>";

if ($ca['ca_include_tail'])
    @include_once($ca['ca_include_tail']);
else
    include_once('./_tail.php');
?>
