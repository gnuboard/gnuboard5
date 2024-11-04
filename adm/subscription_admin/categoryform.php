<?php
$sub_menu = '600200';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, "w");

$sc_id = isset($_GET['sc_id']) ? preg_replace('/[^0-9a-z]/i', '', $_GET['sc_id']) : '';
$ca = array(
'sc_skin_dir'=>'',
'sc_mobile_skin_dir'=>'',
'sc_name'=>'',
'sc_order'=>'',
'sc_mb_id'=>'',
'sc_cert_use'=>0,
'sc_adult_use'=>0,
'sc_sell_email'=>'',
'sc_nocoupon'=>0,
'sc_include_head'=>'',
'sc_include_tail'=>'',
'sc_head_html'=>'',
'sc_tail_html'=>'',
'sc_mobile_head_html'=>'',
'sc_mobile_tail_html'=>'',
);

for($i=0;$i<=10;$i++){
    $ca['sc_'.$i.'_subj'] = '';
    $ca['sc_'.$i] = '';
}

$sql_common = " from {$g5['g5_subscription_category_table']} ";
if ($is_admin != 'super')
    $sql_common .= " where sc_mb_id = '{$member['mb_id']}' ";

if ($w == "")
{
    if ($is_admin != 'super' && !$sc_id)
        alert("최고관리자만 1단계 분류를 추가할 수 있습니다.");

    $len = strlen($sc_id);
    if ($len == 10)
        alert("분류를 더 이상 추가할 수 없습니다.\\n\\n5단계 분류까지만 가능합니다.");

    $len2 = $len + 1;

    $sql = " select MAX(SUBSTRING(sc_id,$len2,2)) as max_subid from {$g5['g5_subscription_category_table']}
              where SUBSTRING(sc_id,1,$len) = '$sc_id' ";
    $row = sql_fetch($sql);

    $subid = base_convert((string)$row['max_subid'], 36, 10);
    $subid += 36;
    if ($subid >= 36 * 36)
    {
        //alert("분류를 더 이상 추가할 수 없습니다.");
        // 빈상태로
        $subid = "  ";
    }
    $subid = base_convert($subid, 10, 36);
    $subid = substr("00" . $subid, -2);
    $subid = $sc_id . $subid;

    $sublen = strlen($subid);

    if ($sc_id) // 2단계이상 분류
    {
        $sql = " select * from {$g5['g5_subscription_category_table']} where sc_id = '$sc_id' ";
        $ca = sql_fetch($sql);
        $html_title = $ca['sc_name'] . " 하위분류추가";
        $ca['sc_name'] = "";
    }
    else // 1단계 분류
    {
        $html_title = "1단계분류추가";
        $ca['sc_use'] = 1;
        $ca['sc_explan_html'] = 1;
        $ca['sc_img_width']  = $default['de_simg_width'];
        $ca['sc_img_height'] = $default['de_simg_height'];
        $ca['sc_mobile_img_width']  = $default['de_simg_width'];
        $ca['sc_mobile_img_height'] = $default['de_simg_height'];
        $ca['sc_list_mod'] = 3;
        $ca['sc_list_row'] = 5;
        $ca['sc_mobile_list_mod'] = 3;
        $ca['sc_mobile_list_row'] = 5;
        $ca['sc_stock_qty'] = 99999;
    }
    $ca['sc_skin'] = "list.10.skin.php";
    $ca['sc_mobile_skin'] = "list.10.skin.php";
}
else if ($w == "u")
{
    $sql = " select * from {$g5['g5_subscription_category_table']} where sc_id = '$sc_id' ";
    $ca = sql_fetch($sql);
    if (! (isset($ca['sc_id']) && $ca['sc_id']))
        alert("자료가 없습니다.");

    $html_title = $ca['sc_name'] . " 수정";
    $ca['sc_name'] = get_text($ca['sc_name']);
}


$g5['title'] = $html_title;
include_once (G5_ADMIN_PATH.'/admin.head.php');

$pg_anchor ='<ul class="anchor">
<li><a href="#anc_scatefrm_basic">필수입력</a></li>
<li><a href="#anc_scatefrm_optional">선택입력</a></li>
<li><a href="#anc_scatefrm_extra">여분필드</a></li>';
if ($w == 'u') $pg_anchor .= '<li><a href="#frm_etc">기타설정</a></li>';
$pg_anchor .= '</ul>';

// 스킨 Path
if(!$ca['sc_skin_dir'])
    $g5_subscription_skin_path = G5_SUBSCRIPTION_SKIN_PATH;
else {
    if(preg_match('#^theme/(.+)$#', $ca['sc_skin_dir'], $match))
        $g5_subscription_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1];
    else
        $g5_subscription_skin_path  = G5_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$ca['sc_skin_dir'];
}

if(!$ca['sc_mobile_skin_dir'])
    $g5_msubscription_skin_path = G5_MSUBSCRIPTION_SKIN_PATH;
else {
    if(preg_match('#^theme/(.+)$#', $ca['sc_mobile_skin_dir'], $match))
        $g5_msubscription_skin_path = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$match[1];
    else
        $g5_msubscription_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/'.G5_SUBSCRIPTION_DIR.'/'.$ca['sc_mobile_skin_dir'];
}
?>

<form name="fcategoryform" action="./categoryformupdate.php" onsubmit="return fcategoryformcheck(this);" method="post" enctype="multipart/form-data">

<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="sc_explan_html" value="<?php echo $ca['sc_explan_html']; ?>">

<section id="anc_scatefrm_basic">
    <h2 class="h2_frm">필수입력</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>분류 추가 필수입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="sc_id">분류코드</label></th>
            <td>
            <?php if ($w == "") { ?>
                <?php echo help("자동으로 보여지는 분류코드를 사용하시길 권해드리지만 직접 입력한 값으로도 사용할 수 있습니다.\n분류코드는 나중에 수정이 되지 않으므로 신중하게 결정하여 사용하십시오.\n\n분류코드는 2자리씩 10자리를 사용하여 5단계를 표현할 수 있습니다.\n0~z까지 입력이 가능하며 한 분류당 최대 1296가지를 표현할 수 있습니다.\n그러므로 총 3656158440062976가지의 분류를 사용할 수 있습니다."); ?>
                <input type="text" name="sc_id" value="<?php echo $subid; ?>" id="sc_id" required class="required frm_input" size="<?php echo $sublen; ?>" maxlength="<?php echo $sublen; ?>">
            <?php } else { ?>
                <input type="hidden" name="sc_id" value="<?php echo $ca['sc_id']; ?>">
                <span class="frm_sc_id"><?php echo $ca['sc_id']; ?></span>
                <a href="<?php echo subscription_category_url($sc_id); ?>" class="btn_frmline">미리보기</a>
                <a href="./categoryform.php?sc_id=<?php echo $sc_id; ?>&amp;<?php echo $qstr; ?>" class="btn_frmline">하위분류 추가</a>
                <a href="./itemlist.php?sca=<?php echo $ca['sc_id']; ?>" class="btn_frmline">상품리스트</a>
            <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_name">분류명</label></th>
            <td><input type="text" name="sc_name" value="<?php echo $ca['sc_name']; ?>" id="sc_name" size="38" required class="required frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_order">출력순서</label></th>
            <td>
                <?php echo help("숫자가 작을 수록 상위에 출력됩니다. 음수 입력도 가능하며 입력 가능 범위는 -2147483648 부터 2147483647 까지입니다.\n<b>입력하지 않으면 자동으로 출력됩니다.</b>"); ?>
                <input type="text" name="sc_order" value="<?php echo $ca['sc_order']; ?>" id="sc_order" class="frm_input" size="12">
            </td>
        </tr>
        <tr>
            <th scope="row"><?php if ($is_admin == 'super') { ?><label for="sc_mb_id"><?php } ?>관리 회원아이디<?php if ($is_admin == 'super') { ?></label><?php } ?></th>
            <td>
                <?php if ($is_admin == 'super') { ?>
                    <input type="text" name="sc_mb_id" value="<?php echo get_sanitize_input($ca['sc_mb_id']); ?>" id="sc_mb_id" class="frm_input" maxlength="20">
                <?php } else { ?>
                    <input type="hidden" name="sc_mb_id" value="<?php echo get_sanitize_input($ca['sc_mb_id']); ?>">
                    <?php echo $ca['sc_mb_id']; ?>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_skin_dir">PC용 스킨명</label></th>
            <td>
                <?php echo get_skin_select('subscription', 'sc_skin_dir', 'sc_skin_dir', $ca['sc_skin_dir']); ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_mobile_skin_dir">모바일용 스킨명</label></th>
            <td>
                <?php echo get_mobile_skin_select('subscription', 'sc_mobile_skin_dir', 'sc_mobile_skin_dir', $ca['sc_mobile_skin_dir']); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">본인확인 체크</th>
            <td>
                <input type="radio" name="sc_cert_use" value="1" id="sc_cert_use_yes" <?php if($ca['sc_cert_use']) echo 'checked="checked"'; ?>>
                <label for="sc_cert_use_yes">사용함</label>
                <input type="radio" name="sc_cert_use" value="0" id="sc_cert_use_no" <?php if(!$ca['sc_cert_use']) echo 'checked="checked"'; ?>>
                <label for="sc_cert_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row">성인인증 체크</th>
            <td>
                <input type="radio" name="sc_adult_use" value="1" id="sc_adult_use_yes" <?php if($ca['sc_adult_use']) echo 'checked="checked"'; ?>>
                <label for="sc_adult_use_yes">사용함</label>
                <input type="radio" name="sc_adult_use" value="0" id="sc_adult_use_no" <?php if(!$ca['sc_adult_use']) echo 'checked="checked"'; ?>>
                <label for="sc_adult_use_no">사용안함</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_skin">출력스킨</label></th>
            <td>
                <?php echo $g5_subscription_skin_path; ?>
                <?php echo help('기본으로 제공하는 스킨은 '.str_replace(G5_PATH.'/', '', $g5_subscription_skin_path).'/list.*.skin.php 입니다.'); ?>
                <select id="sc_skin" name="sc_skin" required class="required">
                    <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", $g5_subscription_skin_path, $ca['sc_skin']); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_img_width">출력이미지 폭</label></th>
            <td>
                <?php echo help("쇼핑몰환경설정 &gt; 이미지(소) 넓이가 기본값으로 설정됩니다.\n".G5_SUBSCRIPTION_URL."/list.php에서 출력되는 이미지의 폭입니다."); ?>
                <input type="text" name="sc_img_width" value="<?php echo $ca['sc_img_width']; ?>" id="sc_img_width" required class="required frm_input" size="5" > 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_img_height">출력이미지 높이</label></th>
            <td>
                <?php echo help("쇼핑몰환경설정 &gt; 이미지(소) 높이가 기본값으로 설정됩니다.\n".G5_SUBSCRIPTION_URL."/list.php에서 출력되는 이미지의 높이입니다."); ?>
                <input type="text" name="sc_img_height"  value="<?php echo $ca['sc_img_height']; ?>" id="sc_img_height" required class="required frm_input" size="5" > 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_list_mod">1줄당 이미지 수</label></th>
            <td>
                <?php echo help("한 줄에 설정한 값만큼의 상품을 출력하지만 스킨에 따라 한 줄에 하나의 상품만 출력할 수도 있습니다."); ?>
                <input type="text" name="sc_list_mod" size="3" value="<?php echo $ca['sc_list_mod']; ?>" id="sc_list_mod" required class="required frm_input"> 개
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_list_row">이미지 줄 수</label></th>
            <td>
                <?php echo help("한 페이지에 출력할 이미지 줄 수를 설정합니다.\n한 페이지에서 표시하는 상품수는 (1줄당 이미지 수 x 줄 수) 입니다."); ?>
                <input type="text" name="sc_list_row" value='<?php echo $ca['sc_list_row']; ?>' id="sc_list_row" required class="required frm_input" size="3"> 줄
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_mobile_skin">모바일 출력스킨</label></th>
            <td>
                <?php echo help('기본으로 제공하는 스킨은 '.str_replace(G5_PATH.'/', '', $g5_msubscription_skin_path).'/list.*.skin.php 입니다.'); ?>
                <select id="sc_mobile_skin" name="sc_mobile_skin" required class="required">
                    <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", $g5_msubscription_skin_path, $ca['sc_mobile_skin']); ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_mobile_img_width">모바일 출력이미지 폭</label></th>
            <td>
                <?php echo help("쇼핑몰환경설정 &gt; 이미지(소) 넓이가 기본값으로 설정됩니다.\n".G5_SUBSCRIPTION_URL."/list.php에서 출력되는 이미지의 폭입니다."); ?>
                <input type="text" name="sc_mobile_img_width" value="<?php echo $ca['sc_mobile_img_width']; ?>" id="sc_mobile_img_width" required class="required frm_input" size="5" > 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_mobile_img_height">모바일 출력이미지 높이</label></th>
            <td>
                <?php echo help("쇼핑몰환경설정 &gt; 이미지(소) 높이가 기본값으로 설정됩니다.\n".G5_SUBSCRIPTION_URL."/list.php에서 출력되는 이미지의 높이입니다."); ?>
                <input type="text" name="sc_mobile_img_height"  value="<?php echo $ca['sc_mobile_img_height']; ?>" id="sc_mobile_img_height" required class="required frm_input" size="5" > 픽셀
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_mobile_list_mod">모바일 1줄당 이미지 수</label></th>
            <td>
                <?php echo help("한 줄에 설정한 값만큼의 상품을 출력하지만 스킨에 따라 한 줄에 하나의 상품만 출력할 수도 있습니다."); ?>
                <input type="text" name="sc_mobile_list_mod" value='<?php echo $ca['sc_mobile_list_mod']; ?>' id="sc_mobile_list_mod" required class="required frm_input" size="3"> 개
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_mobile_list_row">모바일 이미지 줄 수</label></th>
            <td>
                <?php echo help("한 페이지에 출력할 이미지 줄 수를 설정합니다.\n한 페이지에서 표시하는 상품수는 (1줄당 이미지 수 x 줄 수) 입니다."); ?>
                <input type="text" name="sc_mobile_list_row" value='<?php echo $ca['sc_mobile_list_row']; ?>' id="sc_mobile_list_row" required class="required frm_input" size="3"> 줄
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_stock_qty">재고수량</label></th>
            <td>
                <?php echo help("상품의 기본재고 수량을 설정합니다.\n재고를 사용하지 않는다면 숫자를 크게 입력하여 주십시오. 예) 999999"); ?>
                <input type="text" name="sc_stock_qty" size="10" value="<?php echo $ca['sc_stock_qty']; ?>" id="sc_stock_qty" class="frm_input"> 개
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_sell_email">판매자 E-mail</label></th>
            <td>
                <?php echo help("운영자와 판매자가 다른 경우에 사용합니다.\n이 분류에 속한 상품을 등록할 경우에 기본값으로 입력됩니다."); ?>
                <input type="text" name="sc_sell_email" size="40" value="<?php echo get_sanitize_input($ca['sc_sell_email']); ?>" id="sc_sell_email" class="frm_input">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_use">판매가능</label></th>
            <td>
                <?php echo help("재고가 없거나 일시적으로 판매를 중단하시려면 체크 해제하십시오.\n체크 해제하시면 상품 출력을 하지 않으며, 주문도 받지 않습니다."); ?>
                <input type="checkbox" name="sc_use" <?php echo ($ca['sc_use']) ? "checked" : ""; ?> value="1" id="sc_use">
                예
            </td>
        </tr>
        </tbody>
        </table>
    </div>
    <button type="button" class="subscription_category btn_02 btn">테마설정 가져오기</button>
</section>


<section id="anc_scatefrm_optional">
    <h2 class="h2_frm">선택 입력</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>분류 추가 선택입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="sc_include_head">상단파일경로</label></th>
            <td>
                <?php echo help("입력하지 않으면 기본 상단 파일을 사용합니다.<br>상단 내용과 달리 PHP 코드를 사용할 수 있습니다."); ?>
                <input type="text" name="sc_include_head" value="<?php echo $ca['sc_include_head']; ?>" id="sc_include_head" class="frm_input" size="60">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="sc_include_tail">하단 파일 경로</label></th>
            <td>
                <?php echo help("입력하지 않으면 기본 하단 파일을 사용합니다.<br>하단 내용과 달리 PHP 코드를 사용할 수 있습니다."); ?>
                <input type="text" name="sc_include_tail" value="<?php echo $ca['sc_include_tail']; ?>" id="sc_include_tail" class="frm_input" size="60">
            </td>
        </tr>
        <tr id="admin_captcha_box" style="display:none;">
            <th scope="row">자동등록방지</th>
            <td>
                <?php
                echo help("파일 경로를 입력 또는 수정시 캡챠를 반드시 입력해야 합니다.");

                include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');
                $captcha_html = captcha_html();
                $captcha_js   = chk_captcha_js();
                echo $captcha_html;
                ?>
                <script>
                jQuery("#captcha_key").removeAttr("required").removeClass("required");
                </script>
            </td>
        </tr>
        <tr>
            <th scope="row">상단내용</th>
            <td>
                <?php echo help("상품리스트 페이지 상단에 출력하는 HTML 내용입니다."); ?>
                <?php echo editor_html('sc_head_html', get_text(html_purifier($ca['sc_head_html']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">하단내용</th>
            <td>
                <?php echo help("상품리스트 페이지 하단에 출력하는 HTML 내용입니다."); ?>
                <?php echo editor_html('sc_tail_html', get_text(html_purifier($ca['sc_tail_html']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 상단내용</th>
            <td>
                <?php echo help("상품리스트 페이지 상단에 출력하는 HTML 내용입니다."); ?>
                <?php echo editor_html('sc_mobile_head_html', get_text(html_purifier($ca['sc_mobile_head_html']), 0)); ?>
            </td>
        </tr>
        <tr>
            <th scope="row">모바일 하단내용</th>
            <td>
                <?php echo help("상품리스트 페이지 하단에 출력하는 HTML 내용입니다."); ?>
                <?php echo editor_html('sc_mobile_tail_html', get_text(html_purifier($ca['sc_mobile_tail_html']), 0)); ?>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_scatefrm_extra">
    <h2>여분필드 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <?php for ($i=1; $i<=10; $i++) { ?>
        <tr>
            <th scope="row">여분필드<?php echo $i ?></th>
            <td class="td_extra">
                <label for="sc_<?php echo $i ?>_subj">여분필드 <?php echo $i ?> 제목</label>
                <input type="text" name="sc_<?php echo $i ?>_subj" id="sc_<?php echo $i ?>_subj" value="<?php echo get_text($ca['sc_'.$i.'_subj']) ?>" class="frm_input">
                <label for="sc_<?php echo $i ?>">여분필드 <?php echo $i ?> 값</label>
                <input type="text" name="sc_<?php echo $i ?>" value="<?php echo get_text($ca['sc_'.$i]) ?>" id="sc_<?php echo $i ?>" class="frm_input">
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>


<?php if ($w == "u") { ?>
<section id="frm_etc">
    <h2 class="h2_frm">기타설정</h2>
    <?php echo $pg_anchor; ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>분류 추가 기타설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">하위분류</th>
            <td>
                <?php echo help("이 분류의 코드가 10 이라면 10 으로 시작하는 하위분류의 설정값을 이 분류와 동일하게 설정합니다.\n<strong>이 작업은 실행 후 복구할 수 없습니다.</strong>"); ?>
                <label for="sub_category">이 분류의 하위분류 설정을, 이 분류와 동일하게 일괄수정</label>
                <input type="checkbox" name="sub_category" value="1" id="sub_category" onclick="if (this.checked) if (confirm('이 분류에 속한 하위 분류의 속성을 똑같이 변경합니다.\n\n이 작업은 되돌릴 방법이 없습니다.\n\n그래도 변경하시겠습니까?')) return ; this.checked = false;">
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<?php } ?>
<div class="btn_fixed_top">
    <input type="submit" value="확인" class="btn_submit btn" accesskey="s">
    <a href="./categorylist.php?<?php echo $qstr; ?>" class="btn_02 btn">목록</a>
</div>
</form>

<script>
<?php if ($w == 'u') { ?>
$(".banner_or_img").addClass("sit_wimg");
$(function() {
    $(".sit_wimg_view").bind("click", function() {
        var sit_wimg_id = $(this).attr("id").split("_");
        var $img_display = $("#"+sit_wimg_id[1]);

        $img_display.toggle();

        if($img_display.is(":visible")) {
            $(this).text($(this).text().replace("확인", "닫기"));
        } else {
            $(this).text($(this).text().replace("닫기", "확인"));
        }

        var $img = $("#"+sit_wimg_id[1]).children("img");
        var width = $img.width();
        var height = $img.height();
        if(width > 700) {
            var img_width = 700;
            var img_height = Math.round((img_width * height) / width);

            $img.width(img_width).height(img_height);
        }
    });
    $(".sit_wimg_close").bind("click", function() {
        var $img_display = $(this).parents(".banner_or_img");
        var id = $img_display.attr("id");
        $img_display.toggle();
        var $button = $("#sc_"+id+"_view");
        $button.text($button.text().replace("닫기", "확인"));
    });
});
<?php } ?>

function fcategoryformcheck(f)
{
    if (f.w.value == "") {
        var error = "";
        $.ajax({
            url: "./ajax.sc_id.php",
            type: "POST",
            data: {
                "sc_id": f.sc_id.value
            },
            dataType: "json",
            async: false,
            cache: false,
            success: function(data, textStatus) {
                error = data.error;
            }
        });

        if (error) {
            alert(error);
            return false;
        }
    }

    <?php echo get_editor_js('sc_head_html'); ?>
    <?php echo get_editor_js('sc_tail_html'); ?>
    <?php echo get_editor_js('sc_mobile_head_html'); ?>
    <?php echo get_editor_js('sc_mobile_tail_html'); ?>

    return true;
}

var captcha_chk = false;

function use_captcha_check(){
    $.ajax({
        type: "POST",
        url: g5_admin_url+"/ajax.use_captcha.php",
        data: { admin_use_captcha: "1" },
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
        }
    });
}

function frm_check_file(){
    var sc_include_head = "<?php echo $ca['sc_include_head']; ?>";
    var sc_include_tail = "<?php echo $ca['sc_include_tail']; ?>";
    var head = jQuery.trim(jQuery("#sc_include_head").val());
    var tail = jQuery.trim(jQuery("#sc_include_tail").val());

    if(sc_include_head !== head || sc_include_tail !== tail){
        // 캡챠를 사용합니다.
        jQuery("#admin_captcha_box").show();
        captcha_chk = true;

        use_captcha_check();

        return false;
    } else {
        jQuery("#admin_captcha_box").hide();
    }

    return true;
}

jQuery(function($){
    if( window.self !== window.top ){   // frame 또는 iframe을 사용할 경우 체크
        $("#sc_include_head, #sc_include_tail").on("change paste keyup", function(e) {
            frm_check_file();
        });

        use_captcha_check();
    }

    $(".subscription_category").on("click", function() {
        if(!confirm("현재 테마의 스킨, 이미지 사이즈 등의 설정을 적용하시겠습니까?"))
            return false;

        $.ajax({
            type: "POST",
            url: "../theme_config_load.php",
            cache: false,
            async: false,
            data: { type: 'subscription_category' },
            dataType: "json",
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                    return false;
                }

                $.each(data, function(key, val) {
                    if(key == "error")
                        return true;

                    $("#"+key).val(val);
                });
            }
        });
    });
});

/*document.fcategoryform.sc_name.focus(); 포커스 해제*/
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');