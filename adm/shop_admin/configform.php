<?php
$sub_menu = '400100';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "r");

if (!function_exists("get_sock")) {
    function get_sock($url)
    {
        // host 와 uri 를 분리
        //if (ereg("http://([a-zA-Z0-9_\-\.]+)([^<]*)", $url, $res))
        if (preg_match("/http:\/\/([a-zA-Z0-9_\-\.]+)([^<]*)/", $url, $res))
        {
            $host = $res[1];
            $get  = $res[2];
        }

        // 80번 포트로 소캣접속 시도
        $fp = fsockopen ($host, 80, $errno, $errstr, 30);
        if (!$fp)
        {
            die("$errstr ($errno)\n");
        }
        else
        {
            fputs($fp, "GET $get HTTP/1.0\r\n");
            fputs($fp, "Host: $host\r\n");
            fputs($fp, "\r\n");

            // header 와 content 를 분리한다.
            while (trim($buffer = fgets($fp,1024)) != "")
            {
                $header .= $buffer;
            }
            while (!feof($fp))
            {
                $buffer .= fgets($fp,1024);
            }
        }
        fclose($fp);

        // content 만 return 한다.
        return $buffer;
    }
}

if (!$default['de_icode_server_ip'])   $default['de_icode_server_ip'] = '211.172.232.124';
if (!$default['de_icode_server_port']) $default['de_icode_server_port'] = '7295';

if ($default['de_icode_id'] && $default['de_icode_pw']) {
    $res = get_sock('http://www.icodekorea.com/res/userinfo.php?userid='.$default['de_icode_id'].'&userpw='.$default['de_icode_pw']);
    $res = explode(';', $res);
    $userinfo = array(
        'code'      => $res[0], // 결과코드
        'coin'      => $res[1], // 고객 잔액 (충전제만 해당)
        'gpay'      => $res[2], // 고객의 건수 별 차감액 표시 (충전제만 해당)
        'payment'   => $res[3]  // 요금제 표시, A:충전제, C:정액제
    );
}


$g4['title'] = '쇼핑몰설정';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_scf_info">사업자정보</a></li>
<li><a href="#anc_scf_index">쇼핑몰 초기화면</a></li>
<li><a href="#anc_mscf_index">모바일 초기화면</a></li>
<li><a href="#anc_scf_payment">결제설정</a></li>
<li><a href="#anc_scf_delivery">배송설정</a></li>
<li><a href="#anc_scf_etc">기타설정</a></li>
<li><a href="#anc_scf_sms">SMS설정</a></li>
</ul>';
?>

<form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
<section id="anc_scf_info" class="cbox">
    <h2>사업자정보</h2>
    <?php echo $pg_anchor; ?>
    <p>사업자정보는 tail.php 와 content.php 에서 표시합니다.</p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_6">
        <col class="grid_3">
        <col class="grid_6">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="de_admin_company_name">회사명</label></th>
        <td>
            <input type="text" name="de_admin_company_name" value="<?php echo $default['de_admin_company_name']; ?>" id="de_admin_company_name" class="frm_input" size="30">
        </td>
        <th scope="row"><label for="de_admin_company_saupja_no">사업자등록번호</label></th>
        <td>
            <input type="text" name="de_admin_company_saupja_no"  value="<?php echo $default['de_admin_company_saupja_no']; ?>" id="de_admin_company_saupja_no" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_company_owner">대표자명</label></th>
        <td colspan="3">
            <input type="text" name="de_admin_company_owner" value="<?php echo $default['de_admin_company_owner']; ?>" id="de_admin_company_owner" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_company_tel">대표전화번호</label></th>
        <td>
            <input type="text" name="de_admin_company_tel" value="<?php echo $default['de_admin_company_tel']; ?>" id="de_admin_company_tel" class="frm_input" size="30">
        </td>
        <th scope="row"><label for="de_admin_company_fax">팩스번호</label></th>
        <td>
            <input type="text" name="de_admin_company_fax" value="<?php echo $default['de_admin_company_fax']; ?>" id="de_admin_company_fax" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_tongsin_no">통신판매업 신고번호</label></th>
        <td>
            <input type="text" name="de_admin_tongsin_no" value="<?php echo $default['de_admin_tongsin_no']; ?>" id="de_admin_tongsin_no" class="frm_input" size="30">
        </td>
        <th scope="row"><label for="de_admin_buga_no">부가통신 사업자번호</label></th>
        <td>
            <input type="text" name="de_admin_buga_no" value="<?php echo $default['de_admin_buga_no']; ?>" id="de_admin_buga_no" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_company_zip">사업장우편번호</label></th>
        <td>
            <input type="text" name="de_admin_company_zip" value="<?php echo $default['de_admin_company_zip']; ?>" id="de_admin_company_zip" class="frm_input" size="10">
        </td>
        <th scope="row"><label for="de_admin_company_addr">사업장주소</label></th>
        <td>
            <input type="text" name="de_admin_company_addr" value="<?php echo $default['de_admin_company_addr']; ?>" id="de_admin_company_addr" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_info_name">정보관리책임자명</label></th>
        <td>
            <input type="text" name="de_admin_info_name" value="<?php echo $default['de_admin_info_name']; ?>" id="de_admin_info_name" class="frm_input" size="30">
        </td>
        <th scope="row"><label for="de_admin_info_email">정보책임자 e-mail</label></th>
        <td>
            <input type="text" name="de_admin_info_email" value="<?php echo $default['de_admin_info_email']; ?>" id="de_admin_info_email" class="frm_input" size="30">
        </td>
    </tr>

    </tbody>
    </table>
</section>

<section id="anc_scf_index" class="cbox">
    <h2>쇼핑몰 초기화면</h2>
    <?php echo $pg_anchor; ?>
    <p>
        상품관리에서 선택한 상품의 타입대로 쇼핑몰 초기화면에 출력합니다. (상품 타입 히트/추천/최신/인기/할인)<br>
        각 타입별로 선택된 상품이 없으면 쇼핑몰 초기화면에 출력하지 않습니다.
    </p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">최신상품출력</th>
        <td>
            <label for="de_type3_list_use">출력</label>
            <input type="checkbox" name="de_type3_list_use" value="1" id="de_type3_list_use" <?php echo $default['de_type3_list_use']?"checked":""; ?>>
            <label for="de_type3_list_skin">스킨</label>
            <select name="de_type3_list_skin" id="de_type3_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH, $default['de_type3_list_skin']); ?>
            </select>
            <label for="de_type3_list_row">출력할 줄 수</label>
            <input type="text" name="de_type3_list_row" value="<?php echo $default['de_type3_list_row']; ?>" id="de_type3_list_row" class="frm_input" size="3">
            <label for="de_type3_list_mod">1줄당 이미지 수</label>
            <input type="text" name="de_type3_list_mod" value="<?php echo $default['de_type3_list_mod']; ?>" id="de_type3_list_mod" class="frm_input" size="3">
            <label for="de_type3_img_width">이미지 폭</label>
            <input type="text" name="de_type3_img_width" value="<?php echo $default['de_type3_img_width']; ?>" id="de_type3_img_width" class="frm_input" size="3">
            <label for="de_type3_img_height">이미지 높이</label>
            <input type="text" name="de_type3_img_height" value="<?php echo $default['de_type3_img_height']; ?>" id="de_type3_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">히트상품출력</th>
        <td>
            <label for="de_type1_list_use">출력</label> <input type="checkbox" name="de_type1_list_use" value="1" id="de_type1_list_use" <?php echo $default['de_type1_list_use']?"checked":""; ?>>
            <label for="de_type1_list_skin">스킨 </label>

            <select name="de_type1_list_skin" id="de_type1_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH, $default['de_type1_list_skin']); ?>
            </select>
            <label for="de_type1_list_row">출력할 줄 수</label>
            <input type="text" name="de_type1_list_row" value="<?php echo $default['de_type1_list_row']; ?>" id="de_type1_list_row" class="frm_input" size="3">
            <label for="de_type1_list_mod">1줄당 이미지 수</label>
            <input type="text" name="de_type1_list_mod" value="<?php echo $default['de_type1_list_mod']; ?>" id="de_type1_list_mod" class="frm_input" size="3">
            <label for="de_type1_img_width">이미지 폭</label>
            <input type="text" name="de_type1_img_width" value="<?php echo $default['de_type1_img_width']; ?>" id="de_type1_img_width" class="frm_input" size="3">
            <label for="de_type1_img_height">이미지 높이</label>
            <input type="text" name="de_type1_img_height" value="<?php echo $default['de_type1_img_height']; ?>" id="de_type1_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">추천상품출력</th>
        <td>
            <label for="de_type2_list_use">출력</label>
            <input type="checkbox" name="de_type2_list_use" value="1" id="de_type2_list_use" <?php echo $default['de_type2_list_use']?"checked":""; ?>>
            <label for="de_type2_list_skin">스킨</label>
            <select name="de_type2_list_skin" id="de_type2_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH, $default['de_type2_list_skin']); ?>
            </select>
            <label for="de_type2_list_row">출력할 줄 수</label>
            <input type="text" name="de_type2_list_row" value="<?php echo $default['de_type2_list_row']; ?>" id="de_type2_list_row" class="frm_input" size="3">
            <label for="de_type2_list_mod">1줄당 이미지 수</label>
            <input type="text" name="de_type2_list_mod" value="<?php echo $default['de_type2_list_mod']; ?>" id="de_type2_list_mod" class="frm_input" size="3">
            <label for="de_type2_img_width">이미지 폭</label>
            <input type="text" name="de_type2_img_width" value="<?php echo $default['de_type2_img_width']; ?>" id="de_type2_img_width" class="frm_input" size="3">
            <label for="de_type2_img_height">이미지 높이</label>
            <input type="text" name="de_type2_img_height" value="<?php echo $default['de_type2_img_height']; ?>" id="de_type2_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">인기상품출력</th>
        <td>
            <label for="de_type4_list_use">출력</label>
            <input type="checkbox" name="de_type4_list_use" value="1" id="de_type4_list_use" <?php echo $default['de_type4_list_use']?"checked":""; ?>>
            <label for="de_type4_list_skin">스킨</label>
            <select name="de_type4_list_skin" id="de_type4_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH, $default['de_type4_list_skin']); ?>
            </select>
            <label for="de_type4_list_row">출력할 줄 수</label>
            <input type="text" name="de_type4_list_row" value="<?php echo $default['de_type4_list_row']; ?>" id="de_type4_list_row" class="frm_input" size="3">
            <label for="de_type4_list_mod">1줄당 이미지 수</label>
            <input type="text" name="de_type4_list_mod" value="<?php echo $default['de_type4_list_mod']; ?>" id="de_type4_list_mod" class="frm_input" size="3">
            <label for="de_type4_img_width">이미지 폭</label>
            <input type="text" name="de_type4_img_width" value="<?php echo $default['de_type4_img_width']; ?>" id="de_type4_img_width" class="frm_input" size="3">
            <label for="de_type4_img_height">이미지 높이</label>
            <input type="text" name="de_type4_img_height" value="<?php echo $default['de_type4_img_height']; ?>" id="de_type4_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">할인상품출력</th>
        <td>
            <label for="de_type5_list_use">출력</label>
            <input type="checkbox" name="de_type5_list_use" value="1" id="de_type5_list_use" <?php echo $default['de_type5_list_use']?"checked":""; ?>>
            <label for="de_type5_list_skin">스킨</label>
            <select id="de_type5_list_skin" name="de_type5_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH, $default['de_type5_list_skin']); ?>
            </select>
            <label for="de_type5_list_row">출력할 줄 수</label>
            <input type="text" name="de_type5_list_row" value="<?php echo $default['de_type5_list_row']; ?>" id="de_type5_list_row" class="frm_input" size="3">
            <label for="de_type5_list_mod">1줄당 이미지 수</label>
            <input type="text" name="de_type5_list_mod" value="<?php echo $default['de_type5_list_mod']; ?>" id="de_type5_list_mod" class="frm_input" size="3">
            <label for="de_type5_img_width">이미지 폭</label>
            <input type="text" name="de_type5_img_width" value="<?php echo $default['de_type5_img_width']; ?>" id="de_type5_img_width" class="frm_input" size="3">
            <label for="de_type5_img_height">이미지 높이</label>
            <input type="text" name="de_type5_img_height" value="<?php echo $default['de_type5_img_height']; ?>" id="de_type5_img_height" class="frm_input" size="3">
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="anc_mscf_index" class="cbox">
    <h2>모바일 쇼핑몰 초기화면</h2>
    <?php echo $pg_anchor; ?>
    <p>
        상품관리에서 선택한 상품의 타입대로 쇼핑몰 초기화면에 출력합니다. (상품 타입 히트/추천/최신/인기/할인)<br>
        각 타입별로 선택된 상품이 없으면 쇼핑몰 초기화면에 출력하지 않습니다.
    </p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">최신상품출력</th>
        <td>
            <label for="de_mobile_type3_list_use">출력</label>
            <input type="checkbox" name="de_mobile_type3_list_use" value="1" id="de_mobile_type3_list_use" <?php echo $default['de_mobile_type3_list_use']?"checked":""; ?>>
            <label for="de_mobile_type3_list_skin">스킨</label>
            <select name="de_mobile_type3_list_skin" id="de_mobile_type3_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_MSHOP_PATH, $default['de_mobile_type3_list_skin']); ?>
            </select>
            <label for="de_mobile_type3_list_row">출력할 이미지 수</label>
            <input type="text" name="de_mobile_type3_list_row" value="<?php echo $default['de_mobile_type3_list_row']; ?>" id="de_mobile_type3_list_row" class="frm_input" size="3">
            <label for="de_mobile_type3_img_width">이미지 폭</label>
            <input type="text" name="de_mobile_type3_img_width" value="<?php echo $default['de_mobile_type3_img_width']; ?>" id="de_mobile_type3_img_width" class="frm_input" size="3">
            <label for="de_mobile_type3_img_height">이미지 높이</label>
            <input type="text" name="de_mobile_type3_img_height" value="<?php echo $default['de_mobile_type3_img_height']; ?>" id="de_mobile_type3_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">히트상품출력</th>
        <td>
            <label for="de_mobile_type1_list_use">출력</label> <input type="checkbox" name="de_mobile_type1_list_use" value="1" id="de_mobile_type1_list_use" <?php echo $default['de_mobile_type1_list_use']?"checked":""; ?>>
            <label for="de_mobile_type1_list_skin">스킨 </label>
            <select name="de_mobile_type1_list_skin" id="de_mobile_type1_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_MSHOP_PATH, $default['de_mobile_type1_list_skin']); ?>
            </select>
            <label for="de_mobile_type1_list_row">출력할 이미지 수</label>
            <input type="text" name="de_mobile_type1_list_row" value="<?php echo $default['de_mobile_type1_list_row']; ?>" id="de_mobile_type1_list_row" class="frm_input" size="3">
            <label for="de_mobile_type1_img_width">이미지 폭</label>
            <input type="text" name="de_mobile_type1_img_width" value="<?php echo $default['de_mobile_type1_img_width']; ?>" id="de_mobile_type1_img_width" class="frm_input" size="3">
            <label for="de_mobile_type1_img_height">이미지 높이</label>
            <input type="text" name="de_mobile_type1_img_height" value="<?php echo $default['de_mobile_type1_img_height']; ?>" id="de_mobile_type1_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">추천상품출력</th>
        <td>
            <label for="de_mobile_type2_list_use">출력</label>
            <input type="checkbox" name="de_mobile_type2_list_use" value="1" id="de_mobile_type2_list_use" <?php echo $default['de_mobile_type2_list_use']?"checked":""; ?>>
            <label for="de_mobile_type2_list_skin">스킨</label>
            <select name="de_mobile_type2_list_skin" id="de_mobile_type2_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_MSHOP_PATH, $default['de_mobile_type2_list_skin']); ?>
            </select>
            <label for="de_mobile_type2_list_row">출력할 이미지 수</label>
            <input type="text" name="de_mobile_type2_list_row" value="<?php echo $default['de_mobile_type2_list_row']; ?>" id="de_mobile_type2_list_row" class="frm_input" size="3">
            <label for="de_mobile_type2_img_width">이미지 폭</label>
            <input type="text" name="de_mobile_type2_img_width" value="<?php echo $default['de_mobile_type2_img_width']; ?>" id="de_mobile_type2_img_width" class="frm_input" size="3">
            <label for="de_mobile_type2_img_height">이미지 높이</label>
            <input type="text" name="de_mobile_type2_img_height" value="<?php echo $default['de_mobile_type2_img_height']; ?>" id="de_mobile_type2_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">인기상품출력</th>
        <td>
            <label for="de_mobile_type4_list_use">출력</label>
            <input type="checkbox" name="de_mobile_type4_list_use" value="1" id="de_mobile_type4_list_use" <?php echo $default['de_mobile_type4_list_use']?"checked":""; ?>>
            <label for="de_mobile_type4_list_skin">스킨</label>
            <select name="de_mobile_type4_list_skin" id="de_mobile_type4_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_MSHOP_PATH, $default['de_mobile_type4_list_skin']); ?>
            </select>
            <label for="de_mobile_type4_list_row">출력할 이미지 수</label>
            <input type="text" name="de_mobile_type4_list_row" value="<?php echo $default['de_mobile_type4_list_row']; ?>" id="de_mobile_type4_list_row" class="frm_input" size="3">
            <label for="de_mobile_type4_img_width">이미지 폭</label>
            <input type="text" name="de_mobile_type4_img_width" value="<?php echo $default['de_mobile_type4_img_width']; ?>" id="de_mobile_type4_img_width" class="frm_input" size="3">
            <label for="de_mobile_type4_img_height">이미지 높이</label>
            <input type="text" name="de_mobile_type4_img_height" value="<?php echo $default['de_mobile_type4_img_height']; ?>" id="de_mobile_type4_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">할인상품출력</th>
        <td>
            <label for="de_mobile_type5_list_use">출력</label>
            <input type="checkbox" name="de_mobile_type5_list_use" value="1" id="de_mobile_type5_list_use" <?php echo $default['de_mobile_type5_list_use']?"checked":""; ?>>
            <label for="de_mobile_type5_list_skin">스킨</label>
            <select id="de_mobile_type5_list_skin" name="de_mobile_type5_list_skin">
                <?php echo get_list_skin_options("^maintype(.*)\.php", G4_MSHOP_PATH, $default['de_mobile_type5_list_skin']); ?>
            </select>
            <label for="de_mobile_type5_list_row">출력할 이미지 수</label>
            <input type="text" name="de_mobile_type5_list_row" value="<?php echo $default['de_mobile_type5_list_row']; ?>" id="de_mobile_type5_list_row" class="frm_input" size="3">
            <label for="de_mobile_type5_img_width">이미지 폭</label>
            <input type="text" name="de_mobile_type5_img_width" value="<?php echo $default['de_mobile_type5_img_width']; ?>" id="de_mobile_type5_img_width" class="frm_input" size="3">
            <label for="de_mobile_type5_img_height">이미지 높이</label>
            <input type="text" name="de_mobile_type5_img_height" value="<?php echo $default['de_mobile_type5_img_height']; ?>" id="de_mobile_type5_img_height" class="frm_input" size="3">
        </td>
    </tr>
    </tbody>
    </table>
</section>


<section id ="anc_scf_payment" class="cbox">
    <h2>결제정보</h2>
    <?php echo $pg_anchor; ?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="de_bank_use">무통장입금사용</label></th>
        <td>
            <?php echo help("주문시 무통장으로 입금을 가능하게 할것인지를 설정합니다.\n사용할 경우 은행계좌번호를 반드시 입력하여 주십시오.", 50); ?>
            <select id="de_bank_use" name="de_bank_use">
                <option value="0" <?php echo get_selected($default['de_bank_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($default['de_bank_use'], 1); ?>>사용</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_bank_account">은행계좌번호</label></th>
        <td>
            <textarea name="de_bank_account" id="de_bank_account"><?php echo $default['de_bank_account']; ?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_iche_use">계좌이체 결제사용</label></th>
        <td>
        <?php echo help("주문시 실시간 계좌이체를 가능하게 할것인지를 설정합니다.", 50); ?>
            <select id="de_iche_use" name="de_iche_use">
                <option value="0" <?php echo get_selected($default['de_iche_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($default['de_iche_use'], 1); ?>>사용</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_vbank_use">가상계좌 결제사용</label></th>
        <td>
        <?php echo help("주문자가 현금거래를 원할 경우, 해당 거래건에 대해 주문자에게 고유로 발행되는 일회용 계좌번호입니다.", 50); ?>
        <select name="de_vbank_use" id="de_vbank_use">
            <option value="0" <?php echo get_selected($default['de_vbank_use'], 0); ?>>사용안함</option>
            <option value="1" <?php echo get_selected($default['de_vbank_use'], 1); ?>>사용</option>
        </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_hp_use">휴대폰결제사용</label></th>
        <td>
            <?php echo help("주문시 휴대폰 결제를 가능하게 할것인지를 설정합니다.", 50); ?>
            <select id="de_hp_use" name="de_hp_use">
                <option value="0" <?php echo get_selected($default['de_hp_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($default['de_hp_use'], 1); ?>>사용</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_card_use">신용카드결제사용</label></th>
        <td>
            <?php echo help("주문시 신용카드 결제를 가능하게 할것인지를 설정합니다.", 50); ?>
            <select id="de_card_use" name="de_card_use">
                <option value="0" <?php echo get_selected($default['de_card_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($default['de_card_use'], 1); ?>>사용<option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_card_max_amount">카드결제최소금액</label></th>
        <td>
            <?php echo help("신용카드는 경우 1000원 미만은 결제가 불가능합니다.\n카드결제최소금액을 1000원 이상으로 설정하십시오."); ?>
            <input type="text" name="de_card_max_amount" value="<?php echo $default['de_card_max_amount']; ?>"  id="de_card_max_amount" class="frm_input" size="10"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_taxsave_use">현금영수증<br>발급사용</label></th>
        <td>
            <?php echo help("관리자는 설정에 관계없이 <a href=\"".G4_ADMIN_URL."/shop_admin/orderlist.php\">주문내역</a> &gt; 수정에서 발급이 가능합니다.\n현금영수증 발급 취소는 PG사에서 지원하는 현금영수증 취소 기능을 사용하시기 바랍니다.", 50); ?>
            <select id="de_taxsave_use" name="de_taxsave_use">
                <option value="0" <?php echo get_selected($default['de_taxsave_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($default['de_taxsave_use'], 1); ?>>사용</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_mileage_use">마일리지 사용</label></th>
        <td>
            <?php echo help("마일리지는 주문완료에 의해 적립되는 포인트입니다.\n마일리지 사용으로 설정하시면 기존 포인트 대신 마일리지가 주문 결제에 사용됩니다."); ?>
            <input type="checkbox" name="de_mileage_use" value="1" id="de_mileage_use" <?php echo $default['de_mileage_use']?' checked':''; ?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_point">포인트 사용</label></th>
        <td>
            <?php echo help("<a href=\"".G4_ADMIN_URL."/config_form.php#frm_board\" target=\"_blank\">환경설정 &gt; 기본환경설정</a>과 동일한 설정입니다."); ?>
            <input type="checkbox" name="cf_use_point" value="1" id="cf_use_point"<?php echo $config['cf_use_point']?' checked':''; ?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_point_settle">포인트결제 비율</label></th>
        <td>
            <?php echo help("회원의 포인트가 설정값 이상일 경우만 주문시 결제에 사용할 수 있습니다.\n포인트 사용을 하지 않는 경우에는 의미가 없습니다."); ?>
            <input type="text" name="de_point_settle" value="<?php echo $default['de_point_settle']; ?>" id="de_point_settle" class="frm_input" size="10"> 점
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_point_per">포인트결제 %</label></th>
        <td>
            <?php echo help("회원 보유 포인트가 결제액보다 많을 경우, 결제액에서 포인트로 결제 가능한 비율을 설정합니다."); ?>
            <select id="de_point_per" name="de_point_per">
            <?php for ($i=100; $i>0; $i=$i-5) echo '<option value="'.$i.'" '.get_selected($default['de_point_per'], $i).'>'.$i.'</option>'.PHP_EOL; ?>
            </select>%
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_card_point">포인트부여</label></th>
        <td>
            <?php echo help("신용카드, 계좌이체 결제시 포인트를 부여할지를 설정합니다. (기본값은 '아니오')", 50); ?>
            <select id="de_card_point" name="de_card_point">
                <option value="0" <?php echo get_selected($default['de_card_point'], 0); ?>>아니오</option>
                <option value="1" <?php echo get_selected($default['de_card_point'], 1); ?>>예</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_point_days">주문완료 포인트</label></th>
        <td>
            <?php echo help("주문자가 회원일 경우에만 주문완료 포인트를 지급합니다. 주문취소, 반품 등을 고려하여 적당한 기간을 입력하십시오. (기본값은 7)\n0 으로 설정하는 경우 주문완료와 동시에 포인트를 부여합니다.", -150); ?>
            주문 완료 <input type="text" name="de_point_days" value="<?php echo $default['de_point_days']; ?>" id="de_point_days" class="frm_input" size="2"> 일 이후에 포인트를 부여
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_kcp_mid">KCP SITE CODE</label></th>
        <td>
            <?php echo help("KCP 에서 받은 SR 로 시작하는 영대문자, 숫자 혼용 총 5자리 SITE CODE 를 입력하세요.\n만약, 사이트코드가 SR로 시작하지 않는다면 KCP에 사이트코드 변경 요청을 하십시오. 예) SRZ89"); ?>
            <input type="hidden" name="de_card_pg" value="kcp">
            <span class="sitecode">SR</span> <input type="text" name="de_kcp_mid" value="<?php echo $default['de_kcp_mid']; ?>" id="de_kcp_mid" class="frm_input" size="2" maxlength="3" style="font:bold 15px Verdana;"> 영대문자, 숫자 혼용 3자리
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_kcp_site_key">KCP SITE KEY</label></th>
        <td>
            <?php echo help("25자리 영대문자와 숫자 - 그리고 _ 로 이루어 집니다. SITE KEY 발급 KCP 전화: 1544-8660\n예) 1Q9YRV83gz6TukH8PjH0xFf__"); ?>
            <input type="text" name="de_kcp_site_key" value="<?php echo $default['de_kcp_site_key']; ?>" id="de_kcp_site_key" class="frm_input" size="32" maxlength="25">
        </td>
    </tr>
    <tr>
        <th scope="row">에스크로 사용</th>
        <td>
            <?php echo help("에스크로 결제를 사용하시려면, 반드시 <strong>KCP 관리자 > 고객센터 > 서비스변경 및 추가 > 에스크로 신청 메뉴에서 에스크로를 사용 선택하고, 결제수단별로 적용 신청한 후 사용</strong>하셔야 합니다.\n에스크로 사용시 배송과의 연동은 되지 않으며 에스크로 결제만 지원됩니다."); ?>
                <input type="radio" name="de_escrow_use" value="0" <?php echo $default['de_escrow_use']==0?"checked":""; ?> id="de_escrow_use1">
                <label for="de_escrow_use1">일반결제 사용</label>
                <input type="radio" name="de_escrow_use" value="1"<?php echo $default['de_escrow_use']==1?"checked":""; ?> id="de_escrow_use2">
                <label for="de_escrow_use2"> 에스크로결제 사용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_tax_flag_use">복합과세 결제</label></th>
        <td>
             <?php echo help("복합과세(과세, 비과세) 결제를 사용하려면 체크하십시오.\n복합과세 결제를 사용하기 전 KCP에 결제 신청을 해주셔야 합니다."); ?>
            <input type="checkbox" name="de_tax_flag_use" value="1" id="de_tax_flag_use"<?php echo $default['de_tax_flag_use']?' checked':''; ?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row">신용카드 결제테스트</th>
        <td>
            <?php echo help("신용카드를 테스트 하실 경우에 체크하세요. 결제단위 최소 1,000원"); ?>
            <label for="de_card_test1">실결제 </label>
            <input type="radio" name="de_card_test" value="0" <?php echo $default['de_card_test']==0?"checked":""; ?> id="de_card_test1">
            <label for="de_card_test2">테스트결제</label>
            <input type="radio" name="de_card_test" value="1" <?php echo $default['de_card_test']==1?"checked":""; ?> id="de_card_test2">
            <div id="scf_cardtest">
                <a href="https://admin8.kcp.co.kr/assist/login.LoginAction.do" target="_blank">실결제 관리자</a>
                <a href="http://testadmin8.kcp.co.kr/assist/login.LoginAction.do" target="_blank">테스트 관리자</a>
            </div>
            <div id="scf_cardtest_tip">
                <strong>일반결제 사용시 테스트 결제</strong>
                <dl>
                    <dt>신용카드</dt><dd>1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다.<br>(BC, 현대, 롯데, 삼성카드)</dd>
                    <dt>계좌이체</dt><dd>150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.</dd>
                    <dt>가상계좌</dt><dd>1원 이상, 모든 은행이 테스트 되는 것은 아니며 "VB10 : 해당 은행 계좌 없음" 자주 발생함.<br>(광주은행, 하나은행)</dd>
                    <dt>휴대폰</dt><dd>1004원, 실결제가 되며 다음날 새벽에 일괄 취소됨</dd>
                </dl>
                <strong>에스크로 사용시 테스트 결제</strong><br>
                <dl>
                    <dt>신용카드</dt><dd>1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다.<br>(BC, 현대, 롯데, 삼성카드)</dd>
                    <dt>계좌이체</dt><dd>150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.</dd>
                    <dt>가상계좌</dt><dd>1원 이상, 입금통보는 제대로 되지 않음.</dd>
                    <dt>휴대폰</dt><dd>테스트 지원되지 않음.</dd>
                </dl>
                <ul>
                    <li>테스트결제는 <a href="http://testadmin8.kcp.co.kr/assist/login.LoginAction.do" target="_blank">상점관리자</a>의 로그인 정보를 KCP로 문의하시기 바랍니다. (기술지원 1544-8661)</li>
                    <li><b>일반결제</b>의 테스트 사이트코드는 <b>T0000</b> 이며, <b>에스크로 결제</b>의 테스트 사이트코드는 <b>T0007</b> 입니다.</li>
                </ul>
            </div>
            <script>
            $('#scf_cardtest_tip').attr('class','scf_cardtest_tip');
            $('<button type="button" id="scf_cardtest_btn">테스트결제 팁 더보기</button>').appendTo('#scf_cardtest');
            </script>
        </td>
    </tr>
    <tr>
        <th scope="row">공통 URL</th>
        <td>
            <?php echo help("가상계좌 사용시 다음 주소를 <strong>KCP 관리자 > 상점정보관리 > 정보변경 > 공통URL 정보 > 공통URL 변경후</strong>에 넣으셔야 상점에 자동으로 입금 통보됩니다."); ?>
            <?php echo G4_SHOP_URL; ?>/settle_kcp_common.php
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="anc_scf_delivery" class="cbox">
    <h2 >배송정보</h2>
     <?php echo $pg_anchor; ?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="de_send_cost_case">배송비유형</label></th>
        <td>
            <?php echo help("<strong>상한</strong>으로 설정한 경우, 주문총액이 배송비상한가 미만일 경우 배송비를 받습니다.\n<strong>없음</strong>으로 설정한 경우, 배송비상한가 및 배송비를 무시하며 착불의 경우도 없음으로 설정됩니다.\n<strong>개별배송비</strong>로 설정한 경우, 개별 상품의 배송비 설정이 적용됩니다.", 50); ?>
            <select name="de_send_cost_case" id="de_send_cost_case">
                <option value="상한" <?php echo get_selected($default['de_send_cost_case'], "상한"); ?>>상한</option>
                <option value="없음" <?php echo get_selected($default['de_send_cost_case'], "없음"); ?>>없음</option>
                <option value="개별" <?php echo get_selected($default['de_send_cost_case'], "개별"); ?>>개별배송비</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_send_cost_limit">배송비상한가</label></th>
        <td>
            <?php echo help("배송비유형이 '상한'일 경우에만 해당되며 배송비상한가를 여러개 두고자 하는 경우는 <b>;</b> 로 구분합니다.\n\n예를 들어 20000원 미만일 경우 4000원, 30000원 미만일 경우 3000원 으로 사용할 경우에는 배송비상한가를 20000;30000 으로 입력하고 배송비를 4000;3000 으로 입력합니다.", 50); ?>
            <input type="text" name="de_send_cost_limit" value="<?php echo $default['de_send_cost_limit']; ?>" size="40" class="frm_input" id="de_send_cost_limit"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_send_cost_list">배송비</label></th>
        <td>
            <input type="text" name="de_send_cost_list" value="<?php echo $default['de_send_cost_list']; ?>" size="40" class="frm_input" id="de_send_cost_list"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_hope_date_use">희망배송일사용</label></th>
        <td>
            <?php echo help("'예'로 설정한 경우 주문서에서 희망배송일을 입력 받습니다."); ?>
            <select name="de_hope_date_use" id="de_hope_date_use">
                <option value="0" <?php echo get_selected($default['de_hope_date_use'], 0); ?>>사용안함</option>
                <option value="1" <?php echo get_selected($default['de_hope_date_use'], 1); ?>>사용</option>
            </select>
        </td>
    </tr>
    <tr>
         <th scope="row"><label for="de_hope_date_after">희망배송일지정</label></th>
        <td>
            <?php echo help("설정한 날로부터 일주일까지 선택박스 형식으로 출력합니다."); ?>
            <input type="text" name="de_hope_date_after" value="<?php echo $default['de_hope_date_after']; ?>" id="de_hope_date_after" class="frm_input" size="5"> 일
        </td>
    </tr>
    <tr>
        <th scope="row">배송정보</th>
        <td><?php echo editor_html('de_baesong_content', $default['de_baesong_content']); ?></td>
    </tr>
    <tr>
        <th scope="row">교환/반품</th>
        <td><?php echo editor_html('de_change_content', $default['de_change_content']); ?></td>
    </tr>
    </tbody>
    </table>
</section>

<section id="anc_scf_etc" class="cbox">
    <h2>기타설정</h2>
    <?php echo $pg_anchor; ?>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row">관련상품출력</th>
        <td>
            <?php echo help(G4_SHOP_DIR.'/item.php 에서 '.G4_SHOP_DIR.'/maintype10.inc.php 를 include 하여 출력합니다.'); ?>
            <label for="de_rel_list_mod">1줄당 이미지 수</label>
            <input type="text" name="de_rel_list_mod" value="<?php echo $default['de_rel_list_mod']; ?>" id="de_rel_list_mod" class="frm_input" size="3">
            <label for="de_rel_img_width">이미지폭</label>
            <input type="text" name="de_rel_img_width" value="<?php echo $default['de_rel_img_width']; ?>" id="de_rel_img_width" class="frm_input" size="3">
            <label for="de_rel_img_height">이미지높이</label>
            <input type="text" name="de_rel_img_height" value="<?php echo $default['de_rel_img_height']; ?>" id="de_rel_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row">이미지(소)</th>
        <td>
            <?php echo help("상품관리의 상품입력에서 이미지(대) 를 기준 자동생성해 줄때 이미지(소)의 폭과 높이를 설정한 값(단위:픽셀)로 생성합니다."); ?>
            <label for="de_simg_width"><span class="sound_only">이미지(소) </span>폭</label>
            <input type="text" name="de_simg_width" value="<?php echo $default['de_simg_width']; ?>" id="de_simg_width" class="frm_input" size="5"> 픽셀
            /
            <label for="de_simg_height"><span class="sound_only">이미지(소) </span>높이</label>
            <input type="text" name="de_simg_height" value="<?php echo $default['de_simg_height']; ?>" id="de_simg_height" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row">이미지(중)</th>
        <td>
            <?php echo help("상품관리의 상품입력에서 이미지(대) 를 기준 자동생성해 줄때 이미지(중)의 폭과 높이를 설정한 값(단위:픽셀)로 생성합니다."); ?>
            <label for="de_mimg_width"><span class="sound_only">이미지(중) </span>폭</label>
            <input type="text" name="de_mimg_width" value="<?php echo $default['de_mimg_width']; ?>" id="de_mimg_width" class="frm_input" size="5"> 픽셀
            /
            <label for="de_mimg_height"><span class="sound_only">이미지(중) </span>높이</label>
            <input type="text" name="de_mimg_height" value="<?php echo $default['de_mimg_height']; ?>" id="de_mimg_height" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row">로고이미지</th>
        <td>
            <?php echo help("쇼핑몰 로고를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
            <input type="file" name="logo_img" id="logo_img">
            <?php
            $logo_img = G4_DATA_PATH."/common/logo_img";
            if (file_exists($logo_img))
            {
                $size = getimagesize($logo_img);
            ?>
            <label for="logo_img_del"><span class="sound_only">로고이미지</span> 삭제</label>
            <input type="checkbox" name="logo_img_del" value="1" id="logo_img_del">
            <span class="scf_img_logoimg"></span>
            <div id="logoimg" class="banner_or_img">
                <img src="<?php echo G4_DATA_URL; ?>/common/logo_img" alt="">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="cf_logoimg_view" class="btn_frmline scf_img_view">로고이미지 확인</button>').appendTo('.scf_img_logoimg');
            </script>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">메인이미지</th>
        <td>
            <?php echo help("쇼핑몰 메인이미지를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
            <input type="file" name="main_img">
            <?php
            $main_img = G4_DATA_PATH."/common/main_img";
            if (file_exists($main_img))
            {
                $size = getimagesize($main_img);
            ?>
            <label for="main_img_del"><span class="sound_only">메인이미지</span> 삭제</label>
            <input type="checkbox" name="main_img_del" value="1" id="main_img_del">
            <span class="scf_img_mainimg"></span>
            <div id="mainimg" class="banner_or_img">
                <img src="<?php echo G4_DATA_URL; ?>/common/main_img" alt="">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="cf_mainimg_view" class="btn_frmline scf_img_view">메인이미지 확인</button>').appendTo('.scf_img_mainimg');
            </script>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">모바일 로고이미지</th>
        <td>
            <?php echo help("모바일 쇼핑몰 로고를 직접 올릴 수 있습니다. 이미지 파일만 가능합니다."); ?>
            <input type="file" name="mobile_logo_img" id="mobile_logo_img">
            <?php
            $mobile_logo_img = G4_DATA_PATH."/common/mobile_logo_img";
            if (file_exists($mobile_logo_img))
            {
                $size = getimagesize($mobile_logo_img);
            ?>
            <label for="mobile_logo_img_del"><span class="sound_only">모바일 로고이미지</span> 삭제</label>
            <input type="checkbox" name="mobile_logo_img_del" value="1" id="mobile_logo_img_del">
            <span class="scf_img_mobilelogoimg"></span>
            <div id="mobilelogoimg" class="banner_or_img">
                <img src="<?php echo G4_DATA_URL; ?>/common/mobile_logo_img" alt="">
                <button type="button" class="sit_wimg_close">닫기</button>
            </div>
            <script>
            $('<button type="button" id="cf_mobilelogoimg_view" class="btn_frmline scf_img_view">로고이미지 확인</button>').appendTo('.scf_img_mobilelogoimg');
            </script>
            <?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_item_use_use">사용후기</label></th>
        <td>
             <?php echo help("사용후기가 올라오면, 즉시 출력 혹은 관리자 승인 후 출력 여부를 설정합니다.", 50); ?>
            <select name="de_item_use_use" id="de_item_use_use">
                <option value="0" <?php echo get_selected($default['de_item_use_use'], 0); ?>>즉시 출력</option>
                <option value="1" <?php echo get_selected($default['de_item_use_use'], 1); ?>>관리자 승인 후 출력</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_level_sell">상품구입 권한</label></th>
        <td>
            <?php echo help("권한을 1로 설정하면 누구나 구입할 수 있습니다. 특정회원만 구입할 수 있도록 하려면 해당 권한으로 설정하십시오."); ?>
            <?php echo get_member_level_select('de_level_sell', 1, 10, $default['de_level_sell']); ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_code_dup_use">코드 중복검사</label></th>
        <td>
             <?php echo help("분류, 상품 등을 추가할 때 자동으로 코드 중복검사를 하려면 체크하십시오."); ?>
            <input type="checkbox" name="de_code_dup_use" value="1" id="de_code_dup_use"<?php echo $default['de_code_dup_use']?' checked':''; ?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_cart_keep_term">장바구니 보관기간</label></th>
        <td>
             <?php echo help("장바구니 상품의 보관 기간을 설정하십시오."); ?>
            <input type="text" name="de_cart_keep_term" value="<?php echo $default['de_cart_keep_term']; ?>" id="de_cart_keep_term" class="frm_input" size="5"> 일
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_guest_cart_use">비회원 장바구니</label></th>
        <td>
             <?php echo help("비회원 장바구니 기능을 사용하려면 체크하십시오."); ?>
            <input type="checkbox" name="de_guest_cart_use" value="1" id="de_guest_cart_use"<?php echo $default['de_guest_cart_use']?' checked':''; ?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row">비회원에 대한<br/>개인정보수집 내용</th>
        <td><?php echo editor_html('de_guest_privacy', $default['de_guest_privacy']); ?></td>
    </tr>
    <tr>
        <th scope="row">MYSQL USER</th>
        <td><?php echo G4_MYSQL_USER; ?></td>
    </tr>
    <tr>
        <th scope="row">MYSQL DB</th>
        <td><?php echo G4_MYSQL_DB; ?></td>
    </tr>
    <tr>
        <th scope="row">서버 IP</th>
        <td><?php echo ($_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:$_SERVER['LOCAL_ADDR']); ?></td>
    </tr>
    </tbody>
    </table>
</section>

<?php if (file_exists($logo_img) || file_exists($main_img)) { ?>
<script>
$(".banner_or_img").addClass("scf_img");
$(function() {
    $(".scf_img_view").bind("click", function() {
        var sit_wimg_id = $(this).attr("id").split("_");
        var $img_display = $("#"+sit_wimg_id[1]);

        if(sit_wimg_id[1].search("mainimg") > -1) {
            var $img = $("#"+sit_wimg_id[1]);
            var width = $img_display.width();
            var height = $img_display.height();
            if(width > 700) {
                var img_width = 700;
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
        var $button = $("#cf_"+id+"_view");
        $button.text($button.text().replace("닫기", "확인"));
    });
});
</script>
<?php } ?>

<script>
function byte_check(el_cont, el_byte)
{
    var cont = document.getElementById(el_cont);
    var bytes = document.getElementById(el_byte);
    var i = 0;
    var cnt = 0;
    var exceed = 0;
    var ch = '';

    for (i=0; i<cont.value.length; i++) {
        ch = cont.value.charAt(i);
        if (escape(ch).length > 4) {
            cnt += 2;
        } else {
            cnt += 1;
        }
    }

    //byte.value = cnt + ' / 80 bytes';
    bytes.innerHTML = cnt + ' / 80 bytes';

    if (cnt > 80) {
        exceed = cnt - 80;
        alert('메시지 내용은 80바이트를 넘을수 없습니다.\r\n작성하신 메세지 내용은 '+ exceed +'byte가 초과되었습니다.\r\n초과된 부분은 자동으로 삭제됩니다.');
        var tcnt = 0;
        var xcnt = 0;
        var tmp = cont.value;
        for (i=0; i<tmp.length; i++) {
            ch = tmp.charAt(i);
            if (escape(ch).length > 4) {
                tcnt += 2;
            } else {
                tcnt += 1;
            }

            if (tcnt > 80) {
                tmp = tmp.substring(0,i);
                break;
            } else {
                xcnt = tcnt;
            }
        }
        cont.value = tmp;
        //byte.value = xcnt + ' / 80 bytes';
        bytes.innerHTML = xcnt + ' / 80 bytes';
        return;
    }
}
</script>

<section id="anc_scf_sms" class="cbox" >
    <h2>SMS설정</h2>
    <?php echo $pg_anchor; ?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="de_sms_use">SMS 사용</label></th>
        <td>
            <?php echo help("SMS  서비스 회사를 선택하십시오. 서비스 회사를 선택하지 않으면, SMS 발송 기능이 동작하지 않습니다.\n아이코드는 무료 문자메세지 발송 테스트 환경을 지원합니다."); ?>
            <select id="de_sms_use" name="de_sms_use">
                <option value="" <?php echo get_selected($default['de_sms_use'], ''); ?>>사용안함</option>
                <option value="icode" <?php echo get_selected($default['de_sms_use'], 'icode'); ?>>아이코드</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_sms_hp">관리자 핸드폰번호</label></th>
        <td>
            <?php echo help("주문서작성시 쇼핑몰관리자가 문자메세지를 받아볼 번호를 숫자만으로 입력하세요. 예) 0101234567"); ?>
            <input type="text" name="de_sms_hp" value="<?php echo $default['de_sms_hp']; ?>" id="de_sms_hp" class="frm_input" size="20">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_icode_id">아이코드 회원아이디</label></th>
        <td>
            <?php echo help("아이코드에서 사용하시는 회원아이디를 입력합니다."); ?>
            <input type="text" name="de_icode_id" value="<?php echo $default['de_icode_id']; ?>" id="de_icode_id" class="frm_input" size="20">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_icode_pw">아이코드 패스워드</label></th>
        <td>
            <?php echo help("아이코드에서 사용하시는 패스워드를 입력합니다."); ?>
            <input type="password" name="de_icode_pw" value="<?php echo $default['de_icode_pw']; ?>" class="frm_input" id="de_icode_pw">
        </td>
    </tr>
    <tr>
        <th scope="row">요금제</th>
        <td>
            <input type="hidden" name="de_icode_server_ip" value="<?php echo $default['de_icode_server_ip']; ?>">
            <?php
                if ($userinfo['payment'] == 'A') {
                   echo '충전제';
                    echo '<input type="hidden" name="de_icode_server_port" value="7295">';
                } else if ($userinfo['payment'] == 'C') {
                    echo '정액제';
                    echo '<input type="hidden" name="de_icode_server_port" value="7296">';
                } else {
                    echo '가입해주세요.';
                    echo '<input type="hidden" name="de_icode_server_port" value="7295">';
                }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">아이코드 SMS 신청<br>회원가입</th>
        <td>
            <?php echo help("아래 링크에서 회원가입 하시면 문자 건당 16원에 제공 받을 수 있습니다."); ?>
            <a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank" class="btn_frmline">아이코드 회원가입</a>
        </td>
    </tr>
     <?php if ($userinfo['payment'] == 'A') { ?>
    <tr>
        <th scope="row">충전 잔액</th>
        <td colspan="3">
            <?php echo number_format($userinfo['coin']); ?> 원.
            <a href="http://www.icodekorea.com/smsbiz/credit_card_amt.php?icode_id=<?php echo $default['de_icode_id']; ?>&amp;icode_passwd=<?php echo $default['de_icode_pw']; ?>" target="_blank" class="btn_frmline" onclick="window.open(this.href,'icode_payment', 'scrollbars=1,resizable=1'); return false;">충전하기</a>
        </td>
    </tr>
    <tr>
        <th scope="row">건수별 금액</th>
        <td colspan="3">
            <?php echo number_format($userinfo['gpay']); ?> 원.
        </td>
    </tr>
    <?php } ?>
     </tbody>
    </table>

    <section id="scf_sms_pre">
        <h3>사전에 정의된 SMS프리셋</h3>
        <dl>
            <dt>회원가입시</dt>
            <dd>{이름} {회원아이디} {회사명}</dd>
            <dt>주문서작성</dt>
            <dd>{이름} {보낸분} {받는분} {주문번호} {주문금액} {회사명}</dd>
            <dt>입금확인시</dt>
            <dd>{이름} {입금액} {주문번호} {회사명}</dd>
            <dt>상품배송시</dt>
            <dd>{이름} {택배회사} {운송장번호} {주문번호} {회사명}</dd>
        </dl>

        <p><?php echo help('주의! 80 bytes 까지만 전송됩니다. (영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 임)'); ?></p>

        <div id="scf_sms">
            <?php
            $scf_sms_title = array (1=>"회원가입시 고객님께 발송", "주문시 고객님께 발송", "주문시 관리자에게 발송", "입금확인시 고객님께 발송", "상품배송시 고객님께 발송");
            for ($i=1; $i<=5; $i++) {
            ?>
            <section class="scf_sms_box">
                <h4><?php echo $scf_sms_title[$i]?></h4>
                <label for="de_sms_use<?php echo $i; ?>"><span class="sound_only"><?php echo $scf_sms_title; ?></span>사용</label>
                <input type="checkbox" name="de_sms_use<?php echo $i; ?>" value="1" id="de_sms_use<?php echo $i; ?>" <?php echo ($default["de_sms_use".$i] ? " checked" : ""); ?>>
                <div class="scf_sms_img">
                    <textarea id="de_sms_cont<?php echo $i; ?>" name="de_sms_cont<?php echo $i; ?>" ONKEYUP="byte_check('de_sms_cont<?php echo $i; ?>', 'byte<?php echo $i; ?>');"><?php echo $default['de_sms_cont'.$i]; ?></textarea>
                </div>
                <span id="byte<?php echo $i; ?>" class="scf_sms_cnt">0 / 80 바이트</span>
            </section>

            <script>
            byte_check('de_sms_cont<?php echo $i; ?>', 'byte<?php echo $i; ?>');
            </script>
            <?php } ?>
        </div>
    </section>

</section>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
</div>

</form>

<script>
function fconfig_check(f)
{
    <?php echo get_editor_js('de_baesong_content'); ?>
    <?php echo get_editor_js('de_change_content'); ?>
    <?php echo get_editor_js('de_guest_privacy'); ?>

    return true;
}

$(function() {
    $("#scf_cardtest_btn").bind("click", function() {
        var $cf_cardtest_tip = $("#scf_cardtest_tip");
        var $cf_cardtest_btn = $("#scf_cardtest_btn");

        $cf_cardtest_tip.toggle();

        if($cf_cardtest_tip.is(":visible")) {
            $cf_cardtest_btn.text("테스트결제 팁 닫기");
        } else {
            $cf_cardtest_btn.text("테스트결제 팁 더보기");
        }
    })
});
</script>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
