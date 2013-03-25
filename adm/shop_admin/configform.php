<?
$sub_menu = '400100';
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], "r");

//------------------------------------------------------------------------------
// 설정테이블에 필드 추가
//------------------------------------------------------------------------------

sql_query(" ALTER TABLE `{$g4['yc4_default_table']}`    ADD `de_hp_use` TINYINT NOT NULL DEFAULT '0' ", false);
sql_query(" ALTER TABLE `{$g4['yc4_default_table']}`    ADD `de_escrow_use` TINYINT NOT NULL DEFAULT '0' ", false);

// 쏜다넷 smskey 필드 추가 : 101201
@mysql_query(" ALTER TABLE `{$g4['yc4_default_table']}` ADD `de_xonda_smskey` VARCHAR( 255 ) NOT NULL ");

// 비회원에 대한 개인정보 수집에 대한 내용
@mysql_query(" ALTER TABLE `{$g4['yc4_default_table']}` ADD `de_guest_privacy` TEXT NOT NULL ");

// 현금영수증 발급
@mysql_query(" ALTER TABLE `{$g4['yc4_default_table']}` ADD `de_taxsave_use` TINYINT NOT NULL ");

@mysql_query(" ALTER TABLE `{$g4['yc4_default_table']}` ADD `de_kcp_site_key` VARCHAR( 255 ) NOT NULL ");
@mysql_query(" ALTER TABLE `{$g4['yc4_default_table']}` ADD `de_dacom_mertkey` VARCHAR( 255 ) NOT NULL ");
@mysql_query(" ALTER TABLE `{$g4['yc4_default_table']}` ADD `de_vbank_use` VARCHAR( 255 ) NOT NULL ");

@mysql_query(" ALTER TABLE `{$g4['yc4_order_table']}` ADD `od_settle_case` VARCHAR( 255 ) NOT NULL ");
@mysql_query(" ALTER TABLE `{$g4['yc4_order_table']}` ADD `od_escrow1`     VARCHAR( 255 ) NOT NULL ");
@mysql_query(" ALTER TABLE `{$g4['yc4_order_table']}` ADD `od_escrow2`     VARCHAR( 255 ) NOT NULL ");
@mysql_query(" ALTER TABLE `{$g4['yc4_order_table']}` ADD `od_escrow3`     VARCHAR( 255 ) NOT NULL ");

// SMS 아이코드 추가 (icodekorea.com)
$sql = " ALTER TABLE `{$g4['yc4_default_table']}`   ADD `de_sms_use` VARCHAR( 255 ) NOT NULL ,
                                                    ADD `de_icode_id` VARCHAR( 255 ) NOT NULL ,
                                                    ADD `de_icode_pw` VARCHAR( 255 ) NOT NULL ,
                                                    ADD `de_icode_server_ip` VARCHAR( 255 ) NOT NULL ,
                                                    ADD `de_icode_server_port` VARCHAR( 255 ) NOT NULL ";
sql_query($sql, false);

//------------------------------------------------------------------------------

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
    $res = get_sock("http://www.icodekorea.com/res/userinfo.php?userid=$default[de_icode_id]&userpw={$default['de_icode_pw']}");
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

$pg_anchor ="<ul class=\"anchor\">
<li><a href=\"#frm_info\">사업자정보</a></li>
<li><a href=\"#frm_index\">초기화면</a></li>
<li><a href=\"#frm_payment\">결제정보</a></li>
<li><a href=\"#frm_delivery\">배송정보</a></li>
<li><a href=\"#frm_etc\">기타정보</a></li>
<li><a href=\"#frm_sms\">SMS정보</a></li>
</ul>
";
?>


<form name="fconfig" action="./configformupdate.php" onsubmit="return fconfig_check(this)" method="post" enctype="MULTIPART/FORM-DATA">
<section id="frm_info" class="cbox">
    <h2>사업자정보</h2>
    <?=$pg_anchor?>
    <p><?=help("사업자정보는 tail.php 와 content.php 에서 표시합니다.")?></p>
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_5">
        <col class="grid_3">
        <col class="grid_5">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="de_admin_company_name">회사명</label></th>
        <td>
            <input type="text" name="de_admin_company_name" value="<?=$default['de_admin_company_name']?>" id="de_admin_company_name" class="frm_input" size="30">
        </td>
        <th scope="row"><label for="de_admin_company_saupja_no">사업자등록번호</label></th>
        <td>
            <input type="text" name="de_admin_company_saupja_no"  value="<?=$default['de_admin_company_saupja_no']?>" id="de_admin_company_saupja_no" class="frm_input" size="30">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_company_owner">대표자명</label></th>
        <td colspan="3"><input type="text" name="de_admin_company_owner" value="<?=$default['de_admin_company_owner']?>" id="de_admin_company_owner" class="frm_input" size="30"></td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_company_tel">대표전화번호</label></th>
        <td><input type="text" name="de_admin_company_tel" value="<?=$default['de_admin_company_tel']?>" id="de_admin_company_tel" class="frm_input" size="30"></td>
        <th scope="row"><label for="de_admin_company_fax">팩스번호</label></th>
        <td><input type="text" name="de_admin_company_fax" value="<?=$default['de_admin_company_fax']?>" id="de_admin_company_fax" class="frm_input" size="30"></td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_tongsin_no">통신판매업 신고번호</label></th>
        <td><input type="text" name="de_admin_tongsin_no" value="<?=$default['de_admin_tongsin_no']?>" id="de_admin_tongsin_no" class="frm_input" size="30"></td>
        <th scope="row"><label for="de_admin_buga_no">부가통신 사업자번호</label></th>
        <td><input type="text" name="de_admin_buga_no" value="<?=$default['de_admin_buga_no']?>" id="de_admin_buga_no" class="frm_input" size="30"></td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_company_zip">사업장우편번호</label></th>
        <td><input type="text" name="de_admin_company_zip" value="<?=$default['de_admin_company_zip']?>" id="de_admin_company_zip" class="frm_input" size="10"></td>
        <th scope="row"><label for="de_admin_company_addr">사업장주소</label></th>
        <td><input type="text" name="de_admin_company_addr" value="<?=$default['de_admin_company_addr']?>" id="de_admin_company_addr" class="frm_input" size="30"></td>
    </tr>
    <tr>
        <th scope="row"><label for="de_admin_info_name">정보관리책임자명</label></th>
        <td><input type="text" name="de_admin_info_name" value="<?=$default['de_admin_info_name']?>" id="de_admin_info_name" class="frm_input" size="30"></td>
        <th scope="row"><label for="de_admin_info_email">정보책임자 e-mail</label></th>
        <td><input type="text" name="de_admin_info_email" value="<?=$default['de_admin_info_email']?>" id="de_admin_info_email" class="frm_input" size="30"></td>
    </tr>

    </tbody>
    </table>
</section>

<section id="frm_index" class="cbox">
    <h2>쇼핑몰 첫 화면</h2>
    <?=$pg_anchor?>
    <p><?=help("상품관리에서 히트상품으로 선택한 상품들을 설정값대로 초기화면에 출력합니다.\n히트상품으로 체크한 상품이 없다면 초기화면에 출력하지 않습니다.\n추천상품과 신상품도 같은 방법으로 사용합니다.", -150)?></p>
    <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col class="grid_13">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">히트상품출력</th>
            <td>
                <label for="de_type1_list_use">출력 :</label> <input type="checkbox" name="de_type1_list_use" value="1" id="de_type1_list_use" <?=$default['de_type1_list_use']?"checked":"";?>>
                ,<label for="de_type1_list_skin">스킨 : </label>
                <select name="de_type1_list_skin" id="de_type1_list_skin"><?=get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH);?></select><script>document.getElementById('de_type1_list_skin').value='<?=$default['de_type1_list_skin']?>';</script>
                ,<label for="de_type1_list_mod">1라인이미지수 : </label>
                <input type="text" name="de_type1_list_mod" value="<?=$default['de_type1_list_mod']?>" id="de_type1_list_mod" class="frm_input" size="3">
                ,<label for="de_type1_list_row"> 라인 : </label>
                <input type="text" name="de_type1_list_row" value="<?=$default['de_type1_list_row']?>" id="de_type1_list_row" class="frm_input" size="3">
                ,<label for="de_type1_img_width"> 폭 : </label>
                <input type="text" name="de_type1_img_width" value="<?=$default['de_type1_img_width']?>" id="de_type1_img_width" class="frm_input" size="3">
                ,<label for="de_type1_img_height"> 높이 : </label>
                <input type="text" name="de_type1_img_height" value="<?=$default['de_type1_img_height']?>" id="de_type1_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">추천상품출력</th>
            <td>
                 <label for="de_type2_list_use">출력 :</label> <input type="checkbox" name="de_type2_list_use" value="1" id="de_type2_list_use" <?=$default['de_type2_list_use']?"checked":"";?>>
                ,<label for="de_type2_list_skin">스킨 : </label>
                <select id="de_type2_list_skin" name="de_type2_list_skin"><?=get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH);?></select><script>document.getElementById('de_type2_list_skin').value='<?=$default['de_type2_list_skin']?>';</script>
                ,<label for="de_type2_list_mod">1라인이미지수 : </label>
                <input type="text" name="de_type2_list_mod" value="<?=$default['de_type2_list_mod']?>" id="de_type2_list_mod" class="frm_input" size="3">
                ,<label for="de_type2_list_row"> 라인 : </label>
                <input type="text" name="de_type2_list_row" value="<?=$default['de_type2_list_row']?>" id="de_type2_list_row" class="frm_input" size="3">
                ,<label for="de_type2_img_width"> 폭 : </label>
                <input type="text" name="de_type2_img_width" value="<?=$default['de_type2_img_width']?>" id="de_type2_img_width" class="frm_input" size="3">
                ,<label for="de_type2_img_height"> 높이 : </label>
                <input type="text" name="de_type2_img_height" value="<?=$default['de_type2_img_height']?>" id="de_type2_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">최신상품출력</th>
            <td>
                 <label for="de_type3_list_use">출력 :</label> <input type="checkbox" name="de_type3_list_use" value="1" id="de_type3_list_use" <?=$default['de_type3_list_use']?"checked":"";?>>
                ,<label for="de_type3_list_skin">스킨 : </label>
                <select id="de_type3_list_skin" name="de_type3_list_skin"><?=get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH);?></select><script>document.getElementById('de_type3_list_skin').value='<?=$default['de_type3_list_skin']?>';</script>
                ,<label for="de_type3_list_mod">1라인이미지수 : </label>
                <input type="text" name="de_type3_list_mod" value="<?=$default['de_type3_list_mod']?>" id="de_type3_list_mod" class="frm_input" size="3">
                ,<label for="de_type3_list_row"> 라인 : </label>
                <input type="text" name="de_type3_list_row" value="<?=$default['de_type3_list_row']?>" id="de_type3_list_row" class="frm_input" size="3">
                ,<label for="de_type3_img_width"> 폭 : </label>
                <input type="text" name="de_type3_img_width" value="<?=$default['de_type3_img_width']?>" id="de_type3_img_width" class="frm_input" size="3">
                ,<label for="de_type3_img_height"> 높이 : </label>
                <input type="text" name="de_type3_img_height" value="<?=$default['de_type3_img_height']?>" id="de_type3_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">인기상품출력</th>
            <td>
                 <label for="de_type4_list_use">출력 :</label> 
                 <input type="checkbox" name="de_type4_list_use" value="1" id="de_type4_list_use" <?=$default['de_type4_list_use']?"checked":"";?>>
                ,<label for="de_type4_list_skin">스킨 : </label>
                <select id="de_type4_list_skin" name="de_type4_list_skin"><?=get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH);?></select><script>document.getElementById('de_type4_list_skin').value='<?=$default['de_type4_list_skin']?>';</script>
                ,<label for="de_type4_list_mod">1라인이미지수 : </label>
                <input type="text" name="de_type4_list_mod" value="<?=$default['de_type4_list_mod']?>" id="de_type4_list_mod" class="frm_input" size="3">
                ,<label for="de_type4_list_row"> 라인 : </label>
                <input type="text" name="de_type4_list_row" value="<?=$default['de_type4_list_row']?>" id="de_type4_list_row" class="frm_input" size="3">
                ,<label for="de_type4_img_width"> 폭 : </label>
                <input type="text" name="de_type4_img_width" value="<?=$default['de_type4_img_width']?>" id="de_type4_img_width" class="frm_input" size="3">
                ,<label for="de_type4_img_height"> 높이 : </label>
                <input type="text" name="de_type4_img_height" value="<?=$default['de_type4_img_height']?>" id="de_type4_img_height" class="frm_input" size="3">
            </td>
        </tr>
        <tr>
            <th scope="row">할인상품출력</th>
            <td>
                 <label for="de_type5_list_use">출력 :</label> 
                 <input type="checkbox" name="de_type5_list_use" value="1" id="de_type5_list_use" <?=$default['de_type5_list_use']?"checked":"";?>>
                ,<label for="de_type5_list_skin">스킨 : </label>
                <select id="de_type5_list_skin" name="de_type5_list_skin"><?=get_list_skin_options("^maintype(.*)\.php", G4_SHOP_PATH);?></select><script>document.getElementById('de_type5_list_skin').value='<?=$default['de_type5_list_skin']?>';</script>
                ,<label for="de_type5_list_mod">1라인이미지수 : </label>
                <input type="text" name="de_type5_list_mod" value="<?=$default['de_type5_list_mod']?>" id="de_type5_list_mod" class="frm_input" size="3">
                ,<label for="de_type5_list_row"> 라인 : </label>
                <input type="text" name="de_type5_list_row" value="<?=$default['de_type5_list_row']?>" id="de_type5_list_row" class="frm_input" size="3">
                ,<label for="de_type5_img_width"> 폭 : </label>
                <input type="text" name="de_type5_img_width" value="<?=$default['de_type5_img_width']?>" id="de_type5_img_width" class="frm_input" size="3">
                ,<label for="de_type5_img_height"> 높이 : </label>
                <input type="text" name="de_type5_img_height" value="<?=$default['de_type5_img_height']?>" id="de_type5_img_height" class="frm_input" size="3">
            </td>
        </tr>
    </tbody>
    </table>
</section>


<section id ="frm_payment" class="cbox">
    <h2>결제정보</h2>
    <?=$pg_anchor?>
    <table class="frm_tbl">
    <caption>결제정보</caption>
    <colgroup>
        <col class="grid_3">
        <col class="grid_5">
        <col class="grid_3">
        <col class="grid_5">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="de_bank_account">은행계좌번호</label></th>
        <td colspan="3">
            <textarea name="de_bank_account" id="de_bank_account" style="width:99%"><?=$default['de_bank_account']?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_bank_use">무통장입금사용</label></th>
        <td>
            <?=help("주문시 무통장으로 입금을 가능하게 할것인지를 설정합니다.\n사용할 경우 은행계좌번호를 반드시 입력하여 주십시오.", 50)?>
            <select id="de_bank_use" name="de_bank_use">
            <option value='0'>아니오
            <option value='1'>예
            </select>
            <script>document.getElementById('de_bank_use').value="<?=$default['de_bank_use']?>";</script>
        </td>
        <th scope="row"><label for="de_iche_use">계좌이체 결제사용</label></th>
        <td>
        <?=help("주문시 실시간 계좌이체를 가능하게 할것인지를 설정합니다.", 50)?>
            <select id="de_iche_use" name="de_iche_use">
                <option value="0">아니오
                <option value="1">예
            </select>
            <script>document.getElementById('de_iche_use').value="<?=$default['de_iche_use']?>";</script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_vbank_use">가상계좌 결제사용</label></th>
        <td colspan="3">
        <?=help("주문자가 현금거래를 원할 경우, 해당 거래건에 대해 주문자에게 고유로 발행되는 일회용 계좌번호입니다.", 50)?>
        <select name="de_vbank_use" id="de_vbank_use">
            <option value="0">아니오
            <option value="1">예
        </select>
        <script>document.fconfig.de_vbank_use.value="<?=$default['de_vbank_use']?>";</script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_hp_use">휴대폰결제사용</label></th>
        <td colspan="3">
            <?=help("주문시 신용카드 결제를 가능하게 할것인지를 설정합니다.", 50)?>
            <select id="de_hp_use" name="de_hp_use">
                <option value="0">아니오
                <option value="1">예
            </select>
            <script>document.getElementById('de_hp_use').value="<?=$default['de_hp_use']?>";</script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_card_use">신용카드결제사용</label></th>
        <td>
            <?=help("주문시 신용카드 결제를 가능하게 할것인지를 설정합니다.", 50)?>
            <select id="de_card_use" name="de_card_use">
                <option value="0">아니오
                <option value="1">예
            </select>
            <script>document.getElementById('de_card_use').value="<?=$default['de_card_use']?>";</script>
        </td>
        <th scope="row"><label for="de_card_max_amount">카드결제최소금액</label></th>
        <td>
        <?=help("신용카드의 경우 1000원 미만은 결제가 가능하지 않습니다.\n1000원 이상으로 설정하십시오.")?>
        <input type="text" name="de_card_max_amount" value="<?=$default['de_card_max_amount']?>"  id="de_card_max_amount" class="frm_input" size="10"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_taxsave_use">현금영수증발급사용</label></th>
        <td colspan="3">
            <?=help("현금 입금후 주문자가 주문상세내역에서 현금영수증 발급을 가능하게 할것인지를 설정합니다.\n\n관리자는 설정에 관계없이 주문관리 > 수정에서 발급이 가능합니다.\n\n현금영수증의 취소 기능은 없으므로 PG사에서 지원하는 현금영수증 취소 기능을 사용하시기 바랍니다.", 50)?>
            &nbsp; 현금영수증의 취소 기능은 없으므로 PG사에서 지원하는 현금영수증 취소 기능을 사용하시기 바랍니다.
            <select id="de_taxsave_use" name="de_taxsave_use">
                <option value='0'>아니오
                <option value='1'>예
            </select>
            <script>document.getElementById('de_taxsave_use').value="<?=$default['de_taxsave_use']?>";</script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_point">포인트 사용</label></th>
        <td colspan="3">
            <?=help("환경설정 > 기본환경설정과 동일한 설정입니다.")?>
            <input type="checkbox" name="cf_use_point" value="1" id="cf_use_point"<?=$config['cf_use_point']?' checked':'';?> class="frm_input"> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_point_settle">포인트 결제사용</label></th>
        <td>
            <?=help("회원의 포인트가 설정값 이상일 경우만 주문시 결제에 사용할 수 있습니다.\n\n포인트 사용을 하지 않는 경우에는 의미가 없습니다.")?>
            <input type="text" name="de_point_settle" value="<?=$default['de_point_settle']?>" id="de_point_settle" class="frm_input" size="10"> 점
        </td>
        <th scope="row"><label for="de_point_per">포인트결제 %</label></th>
        <td>
            <?=help("회원의 포인트가 포인트 결제사용 포인트 보다 클 경우 주문금액의 몇% 까지 사용 가능하게 할지를 설정합니다.")?>
            <select id="de_point_per" name="de_point_per">
            <? for ($i=100; $i>0; $i=$i-5) echo "<option value='$i'>{$i}\n"; ?>
            </select>%
            <script type="text/javascript">
              document.getElementById('de_point_per').value='<?=$default['de_point_per']?>';
            </script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_card_point">포인트부여</label></th>
        <td>
            <?=help("신용카드, 계좌이체 결제시 포인트를 부여할지를 설정합니다. (기본값은 '아니오')", 50)?>
            <select id="de_card_point" name="de_card_point">
                <option value='0'>아니오
                <option value='1'>예
            </select>
            <script>document.getElementById('de_card_point').value="<?=$default['de_card_point']?>";</script>
        </td>
        <th scope="row"><label for="de_point_days">주문완료 포인트</label></th>
        <td>
            <?=help("설정값 이후에 포인트를 부여합니다.(주문자가 회원일 경우에만 해당)\n\n주문취소, 반품 등을 고려하여 적당한 기간을 입력하십시오. (기본값은 7)\n\n0 으로 설정하는 경우 주문과 동시에 포인트를 부여합니다.", -150)?>
            주문 완료 <input type="text" name="de_point_days" value="<?=$default['de_point_days']?>" id="de_point_days" class="frm_input" size="5"> 일 이후에 포인트를 부여
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_kcp_mid">KCP SITE CODE</label></th>
        <td>
            <?=help("KCP 에서 부여받는 SITE CODE 를 입력하세요.<br>SR 로 시작하는 영대문자, 숫자 혼용 총 5자리 코드를 입력하시면 됩니다.<br>만약, 사이트코드가 SR로 시작하지 않는다면 KCP에 사이트코드를 변경 요청해 주십시오.<br>예) SRZ89");?>
            <input type="hidden" name="de_card_pg" value="kcp">
            <span style="font:bold 15px Verdana;">SR</span> <input type="text" name="de_kcp_mid" value="<?=$default['de_kcp_mid']?>" id="de_kcp_mid" class="frm_input" size="2" maxlength="3" style="font:bold 15px Verdana;"> 영대문자, 숫자 혼용 3자리
        </td>
        <th scope="row"><label for="de_kcp_site_key">KCP SITE KEY</label></th>
        <td>
            <?=help("25자리 영대문자와 숫자 - 그리고 _ 로 이루어 집니다.<br>SITE KEY 발급은 KCP로 문의하세요.<br>1544-8660<br>예) 1Q9YRV83gz6TukH8PjH0xFf__");?>
            <input type="text" name="de_kcp_site_key" value="<?=$default['de_kcp_site_key']?>" id="de_kcp_site_key" class="frm_input" size="32" maxlength="25">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_escrow_use">에스크로 사용</label></th>
        <td colspan="3">
            <?=help("일반결제와 에스크로 결제를 선택하실 수 있습니다.<br/>반드시 KCP 관리자 > 고객센터 > 서비스변경 및 추가 > 에스크로 신청 메뉴에서 에스크로를 사용 선택하고, 결제수단별로 적용 신청한 후 사용하셔야 합니다.<br/>에스크로 사용시 배송과의 연동은 되지 않으며 에스크로 결제만 지원됩니다.")?>
            <label><input type="radio" name="de_escrow_use" value="0" <?=$default['de_escrow_use']==0?"checked":"";?> id="de_escrow_use"> 일반결제 사용</label>
            <label><input type="radio" name="de_escrow_use" value="1"<?=$default['de_escrow_use']==1?"checked":"";?>> 에스크로결제 사용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_card_test">신용카드 결제테스트</label></th>
        <td colspan="3">
            <?=help("신용카드를 테스트 하실 경우에 체크하세요. 결제단위 최소 1,000원")?>
            <label><input type="radio" name="de_card_test" value="0" <?=$default['de_card_test']==0?"checked":"";?>id="de_card_test"> 실결제</label>
            <label><input type="radio" name="de_card_test" value="1" <?=$default['de_card_test']==1?"checked":"";?>> 테스트결제</label>
            &nbsp;
            [ <a href="https://admin8.kcp.co.kr/assist/login.LoginAction.do" target="_blank">실결제 관리자</a> &nbsp;|&nbsp;
            <a href="http://testadmin8.kcp.co.kr/assist/login.LoginAction.do" target="_blank">테스트 관리자</a> ]
            <span id="test_tip" style="margin:0 10px; color:#ff3300;">테스트결제 팁 더보기</span>
        </td>
    </tr>
    <tr>
    <td colspan="4" id="test_tip_help" style="display:none">
        <strong>일반결제 사용시 테스트 결제</strong><br />
        &middot; 신용카드 : 1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다. (BC, 현대, 롯데, 삼성카드)<br />
        &middot; 계좌이체 : 150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.<br />
        &middot; 가상계좌 : 1원 이상, 모든 은행이 테스트 되는 것은 아니며 "VB10 : 해당 은행 계좌 없음" 자주 발생함. (광주은행, 하나은행)<br />
        &middot; 휴대폰   : 1004원, 실결제가 되며 다음날 새벽에 일괄 취소됨.<br />
        <br />
        <strong>에스크로 사용시 테스트 결제</strong><br />
        &middot; 신용카드 : 1000원 이상, 모든 카드가 테스트 되는 것은 아니므로 여러가지 카드로 결제해 보셔야 합니다. (BC, 현대, 롯데, 삼성카드)<br />
        &middot; 계좌이체 : 150원 이상, 계좌번호, 비밀번호는 가짜로 입력해도 되며, 주민등록번호는 공인인증서의 것과 일치해야 합니다.<br />
        &middot; 가상계좌 : 1원 이상, 입금통보는 제대로 되지 않음.<br />
        &middot; 휴대폰   : 테스트 지원되지 않음.<br />
        <br />
        <div style="float:left; color:#ff3300;">
        * 테스트결제의 경우 상점관리자(<a href='http://testadmin8.kcp.co.kr/assist/login.LoginAction.do' target='_blank'>http://testadmin8.kcp.co.kr/assist/login.LoginAction.do</a>)의 로그인 정보가 사용하시는 것과 다르므로 아이디/패스워드를 KCP로 문의하시기 바랍니다. (기술지원 1544-8661)<br>
        * 참고로 일반결제의 테스트 사이트코드는 T0000 이며, 에스크로 결제의 테스트 사이트코드는 T0007 입니다.
        </div>
    </td>
    </tr>
    <tr>
        <th scope="row">공통 URL</th>
        <td colspan="3">
        <?=help("가상계좌 사용시 이 주소를 \"KCP 관리자 > 상점정보관리 > 정보변경 > 공통URL 정보 > 공통URL 변경후\"에 넣으셔야 상점에 자동으로 입금 통보됩니다.")?>
        <?=G4_SHOP_URL?>/settle_kcp_common.php
        </td>
    </tr>
    <tr class="ht" style='display:none;'>
        <td>LG텔레콤 상점아이디</td>
        <td>
        <input type="text" name="de_dacom_mid" value="<?=$default['de_dacom_mid']?>" size="40">
        <?=help("tsi_ 로 시작되는 상점아이디로만 테스트 결제가 가능합니다.");?>
        </td>
        <td>LG텔레콤 mertkey</td>
        <td>
        <input type="text" name="de_dacom_mertkey" value="<?=$default['de_dacom_mertkey']?>" size="40">
    </td>
    <!-- <td>LG텔레콤 테스트 모드</td>
    <td><input type=checkbox name=de_dacom_test value='1' <?=$default[de_dacom_test]?"checked":"";?>> 테스트로 결제하실 경우에 체크하세요.</td> -->
    </tr>
    <tr class="ht" style="display:none;">
        <td>이니시스 아이디</td>
        <td>
        <input type="text" name="de_inicis_mid" value="<?=$default['de_inicis_mid']?>" size="40">
    </td>
        <td>이니시스 패스워드</td>
        <td>
            <input type="text" name="de_inicis_passwd" value="<?=$default['de_inicis_passwd']?>">
        </td>
    </tr>
    <tr class="ht" style="display:none">
        <td>뱅크타운 상점ID</td>
        <td>
            <input type="text" name="de_banktown_mid" value="<?=$default['de_banktown_mid']?>" size="40">
        </td>
        <td>뱅크타운 라이센스 키<!-- AuthKey --></td>
        <td>
            <input type="text" name="de_banktown_auth_key" value="<?=$default['de_banktown_auth_key']?>" size="40" maxlength="32">
        </td>
    </tr>
    <tr class="ht" style="display:none">
        <td>올더게이트 몰ID</td>
        <td colspan="3">
           <input type="text" name="de_allthegate_mid" value="<?=$default['de_allthegate_mid']?>" size="40">
        </td>
    </tr>
    <tr class="ht" style="display:none">
        <td>올앳 파트너 ID</td>
        <td>
        <input type="text" name="de_allat_partner_id" value="<?=$default['de_allat_partner_id']?>" size="40">
        </td>
        <td>주문번호 Prefix</td>
        <td>
        <input type="text" name="de_allat_prefix" value="<?=$default['de_allat_prefix']?>"> 3자리
        </td>
    </tr>
    <tr class="ht" style="display:none">
        <td>올앳 FormKey 값</td>
        <td>
        <input type="text" name="de_allat_formkey" value="<?=$default['de_allat_formkey']?>" size="40">
        </td>
        <td>올앳 CrossKey 값</td>
        <td>
        <input type="text" name="de_allat_crosskey" value="<?=$default['de_allat_crosskey']?>" size="40">
        </td>
    </tr>
    <tr class="ht" style="display:none">
        <td>티지코프 ID</td>
        <td>
        <input type="text" name="de_tgcorp_mxid" value="<?=$default['de_tgcorp_mxid']?>" size="40">
        </td>
        <td>티지코프 접근키</td>
        <td>
        <input type="text" name="de_tgcorp_mxotp" value="<?=$default['de_tgcorp_mxotp']?>" size="40">
        </td>
    </tr>
    <tr class="ht" style="display:none">
        <td>KSPAY 상점아이디</td>
        <td colspan="3">
        <input type="text" name="de_kspay_id" value="<?=$default['de_kspay_id']?>" size="40">
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_delivery" class="cbox">
    <h2 >배송정보</h2>
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
        <th scope="row"><label for="de_send_cost_case">배송비유형</label></th>
        <td colspan="3">
            <?=help("'상한'으로 설정한 경우는 주문총액이 배송비상한가 미만일 경우 배송비를 받습니다.\n\n'없음'으로 설정한 경우에는 배송비상한가, 배송비를 무시하며 착불의 경우도 없음으로 설정하여 사용합니다.", 50);?>
            <select id="de_send_cost_case" name="de_send_cost_case">
                <option value="상한">상한
                <option value="없음">없음
                </select>
            <script>document.getElementById('de_send_cost_case').value="<?=$default['de_send_cost_case']?>";</script>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_send_cost_limit">배송비상한가</label></th>
        <td colspan="3">
            <?=help("배송비유형이 '상한'일 경우에만 해당되며 배송비상한가를 여러개 두고자 하는 경우는 <b>;</b> 로 구분합니다.\n\n예를 들어 20000원 미만일 경우 4000원, 30000원 미만일 경우 3000원 으로 사용할 경우에는 배송비상한가를 20000;30000 으로 입력하고 배송비를 4000;3000 으로 입력합니다.", 50);?>
            <input type="text" name="de_send_cost_limit" value="<?=$default['de_send_cost_limit']?>" size="40" class="frm_input" id="de_send_cost_limit"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_send_cost_list">배송비</label></th>
        <td colspan="3">
            <input type="text" name="de_send_cost_list" value="<?=$default['de_send_cost_list']?>" size="40" class="frm_input" id="de_send_cost_list"> 원
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_hope_date_use">희망배송일사용</label></th>
        <td>
            <?=help("'예'로 설정한 경우 주문서에서 희망배송일을 입력 받습니다.", 50);?>
            <select id="de_hope_date_use" name="de_hope_date_use">
            <option value="0">아니오
            <option value="1">예
            </select>
            <script>document.getElementById('de_hope_date_use').value="<?=$default['de_hope_date_use']?>";</script>
        </td>
         <th scope="row"><label for="de_hope_date_after">희망배송일날짜</label></th>
        <td>
            <?=help("설정한날 이후의 날짜부터 일주일까지 선택박스 형식으로 출력합니다.", 50);?>
            <input type="text" name="de_hope_date_after" value="<?=$default['de_hope_date_after']?>" id="de_hope_date_after" class="frm_input" size="5"> 일
        </td>
    </tr>
    <tr>
        <th scope="row">배송정보</th>
        <td colspan="3">
            <br /><?=editor_html('de_baesong_content', $default['de_baesong_content']);?>
        </td>
    </tr>
    <tr>
        <th scope="row">교환/반품</th>
        <td colspan="3">
            <br /><?=editor_html('de_change_content', $default['de_change_content']);?>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_etc" class="cbox">
    <h2>기타정보</h2>
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
        <th scope="row">관련상품출력</th>
        <td colspan="3">
        <?=help("$cart_dir/item.sub.adding.php 에서 $cart_dir/maintype1.inc.php 를 include 하여 출력합니다.");?>
        <label for="de_rel_list_mod">1라인이미지수 : </label><input type="text" name="de_rel_list_mod" value="<?=$default['de_rel_list_mod']?>" id="de_rel_list_mod" class="frm_input" size="3">
        <label for="de_rel_img_width">, 이미지폭 : </label><input type="text" name="de_rel_img_width" value="<?=$default['de_rel_img_width']?>" id="de_rel_img_width" class="frm_input" size="3">
        <label for="de_rel_img_height">, 이미지높이 : </label><input type="text" name="de_rel_img_height" value="<?=$default['de_rel_img_height']?>" id="de_rel_img_height" class="frm_input" size="3">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_simg_width">이미지(소) 폭</label></th>
        <td>
        <?=help("상품관리의 상품입력에서 이미지(대) 입력으로 자동생성해 줄때 이미지(중)의 폭과 높이를 설정한 값으로 생성하여 줍니다.");?>
        <input type="text" name="de_simg_width" value="<?=$default['de_simg_width']?>" id="de_simg_width" class="frm_input" size="5"> 픽셀
        </td>
        <th scope="row"><label for="de_simg_height">이미지(소) 높이</label></th>
        <td>
        <input type="text" name="de_simg_height" value="<?=$default['de_simg_height']?>" id="de_simg_height" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_mimg_width">이미지(중) 폭</label></th>
        <td>
        <?=help("상품관리의 상품입력에서 이미지(대) 입력으로 자동생성해 줄때 이미지(중)의 폭과 높이를 설정한 값으로 생성하여 줍니다.");?>
        <input type="text" name="de_mimg_width" value="<?=$default['de_mimg_width']?>" id="de_mimg_width" class="frm_input" size="5"> 픽셀
        </td>
        <th scope="row"><label for="de_mimg_height">이미지(중) 높이</label></th>
        <td>
            <input type="text" name="de_mimg_height" value="<?=$default['de_mimg_height']?>" id="de_mimg_height" class="frm_input" size="5"> 픽셀
        </td>
    </tr>
    <tr>
        <th scope="row">로고이미지</th>
        <td colspan="3">
        <?=help("쇼핑몰에 사용하는 로고이미지 입니다.\n이미지 파일만 업로드 가능합니다.");?>
        <input type="file" name="logo_img" id="logo_img">
        <?
        $logo_img = G4_DATA_PATH."/common/logo_img";
        if (file_exists($logo_img))
        {
            $size = getimagesize($logo_img);
            echo "<img src='".G4_ADMIN_URL."/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('id_logo_img', $size[0], $size[1]);\"><input type=checkbox name=logo_img_del value='1'>삭제";
            echo "<div id='id_logo_img' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='".G4_DATA_URL."/common/logo_img' border=1></div>";
        }
        ?>
        </td>
    </tr>
    <tr>
        <th scope="row">메인이미지</th>
        <td colspan="3">
            <?=help("쇼핑몰에 사용하는 메인이미지 입니다.\n이미지 파일만 업로드 가능합니다.");?>
            <input type="file" name="main_img">
            <?
            $main_img = G4_DATA_PATH."/common/main_img";
            if (file_exists($main_img))
            {
            $size = getimagesize($main_img);
            echo "<img src='".G4_ADMIN_URL."/img/icon_viewer.gif' border=0 align=absmiddle onclick=\"imageview('id_main_img', $size[0], $size[1]);\"><input type=checkbox name=main_img_del value='1'>삭제";
            echo "<div id='id_main_img' style='left:0; top:0; z-index:+1; display:none; position:absolute;'><img src='".G4_DATA_URL."/common/main_img' border=1></div>";
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_item_ps_use">사용후기</label></th>
        <td colspan="3">
             <?=help("고객이 특정 상품에 사용후기를 작성하였을 경우 바로 출력할것인지 관리자 승인 후 출력할것인지를 설정합니다.", 50);?>
            <select id="de_item_ps_use" name="de_item_ps_use">
                <option value="0">관리자 승인없이 출력
                <option value="1">관리자 승인 후 출력
            </select>
            <script>document.getElementById('de_item_ps_use').value="<?=$default['de_item_ps_use']?>";</script>
        </td>
    </tr>
    <?/*?>
    <tr>
        <th scope="row"><label for="de_scroll_banner_use">스크롤배너 사용</label></th>
        <td colspan="3">
            <?=help("'예'로 설정한 경우 쇼핑몰 우측에 스크롤배너가 출력됩니다.", 50);?>
            <select id="de_scroll_banner_use" name="de_scroll_banner_use">
                <option value="0">아니오
                <option value="1">예
            </select>
            <script>document.getElementById('de_scroll_banner_use').value="<?=$default['de_scroll_banner_use']?>";</script>
        </td>
    </tr>
    <?*/?>
    <tr>
        <th scope="row"><label for="de_level_sell">상품구입 권한</label></th>
        <td colspan="3">
            <?=help("설정을 1로 하게되면 모든 방문자에게 판매를 할 수 있지만 설정을 변경하여 특정회원을 대상으로 판매를 할 수 있습니다.");?>
            <?=get_member_level_select('de_level_sell', 1, 10, $default['de_level_sell']) ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_code_dup_use">코드 중복검사</label></th>
        <td colspan="3">
             <?=help("분류, 상품을 입력(추가) 할 때 코드 중복검사를 사용할 경우 체크하시면 됩니다.");?>
            <input type="checkbox" name="de_code_dup_use" value="1" id="de_code_dup_use"<?=$default['de_code_dup_use']?' checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="de_different_msg">장바구니 메세지</label></th>
        <td colspan="3">
            <?=help("상품을 장바구니에 담은 후에는 가격 수정이 불가하므로 비회원가격과 회원가격이 다른 경우에는 장바구니에 담기 전에 미리 메세지를 출력하여 로그인 한 후 구입을 하도록 유도합니다.", -150);?>
            <input type="checkbox" name="de_different_msg" value="1" id="de_different_msg"<?=$default['de_different_msg']?'checked':'';?>>
            <label for="de_different_msg">비회원가격과 회원가격이 다른 상품을 장바구니에 담는 경우 "가격이 다릅니다"라는 메세지를 출력합니다.</label>
        </td>
    </tr>
    <tr>
        <th scope="row">비회원에 대한<br/>개인정보수집 내용</th>
        <td colspan="3">
         <br /><?=editor_html('de_guest_privacy', $default['de_guest_privacy']);?>
        </td>
    </tr>
    <tr>
        <th scope="row">MYSQL USER</th>
        <td><br/><?=$mysql_user?></td>
        <th scope="row">MYSQL DB</th>
        <td><br/><?=$mysql_db?></td>
    </tr>
    <tr>
        <th scope="row">서버 IP</th>
        <td><?=($_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:$_SERVER['LOCAL_ADDR']);?></td>
        <th scope="row"><label for="de_register">프로그램 등록번호</label></th>
        <td>
            <?=help("정식구입자께만 발급해 드리고 있습니다.\n등록번호가 틀린 경우 주문서를 확인 하실 수 없습니다.\n등록번호는 서버 IP, MYSQL USER, DB 를 알려주셔야 발급이 가능합니다.", -180, -160);?>
            <input type="text" name="de_register" value="<?=$default['de_register']?>" id="de_register" class="frm_input" required size="30">
        </td>
    </tr>
    </tbody>
    </table>
</section>

<script type="text/javascript">
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
<section id="frm_sms" class="cbox" >
    <h2>SMS정보</h2>
    <?=$pg_anchor?>
    <?
    $sms_title   = array (1=>"회원가입시", "주문서작성시", "입금확인시", "상품배송시");
    $sms_daesang = array (1=>"고객님께 발송", "관리자께 발송", "고객님께 발송", "고객님께 발송");
    ?>

    <? for ($i=1; $i<=4; $i++) { ?>
<div style="width:225px;float:left">
<h3 style="display:inline-block;text-align:center;width:225px" ><?=$sms_title[$i]?></h3>
    <ul style="list-style:none;margin:0 auto;width:163px;padding:0 !important">
        <li style="text-align:center">(<?=$sms_daesang[$i]?>)</li>
        <li style="background:url(./img/sms_back.gif) no-repeat 0 0;width:163px;height:191px">
            <textarea id="de_sms_cont<?=$i?>" name="de_sms_cont<?=$i?>" ONKEYUP="byte_check('de_sms_cont<?=$i?>', 'byte<?=$i?>');" style="margin-left:22px;margin-top:54px;width:114px;overflow:hidden;height:85px;background-color:#C4FFFF; FONT-SIZE: 8pt; font-family:굴림체"><?=$default["de_sms_cont".$i]?></textarea>
        </li>
        <li style="text-align:center">
            <span id="byte<?=$i?>" style="text-align:center">0 / 80 바이트</span>
            <br><input type="checkbox" name="de_sms_use<?=$i?>" value="1" id="de_sms_use<?=$i?>"<?=($default["de_sms_use".$i] ? " checked" : "")?>>
            <label for="de_sms_use<?=$i?>">사용</label>
        </li>
    </ul>
</div>
  
    <script type="text/javascript"> 
    byte_check('de_sms_cont<?=$i?>', 'byte<?=$i?>');
    </script>
    <? } ?>                

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_5">
        <col class="grid_3">
        <col class="grid_5">
    </colgroup>
    <tbody>
    <tr>
        <th><label for="de_sms_use">SMS 사용</label></th>
        <td colspan="3">
            <?=help("서비스 회사를 선택하신 경우에만 SMS 를 사용합니다.\n위의 개별적인 기능별 사용(회원가입시, 주문서작성시 ...)보다 우선합니다.\n아이코드의 경우 무료테스트 환경을 지원합니다.");?>
            <select id="de_sms_use" name="de_sms_use">
                <option value="">사용안함
                <option value="icode">아이코드
            </select>
            <script>document.getElementById('de_sms_use').value="<?=$default['de_sms_use']?>";</script>
        </td>
    </tr>
    <tr>
        <th><label for="de_sms_hp">관리자 핸드폰번호</label></th>
        <td colspan="3">
            <?=help("쇼핑몰관리자 또는 보내시는분의 핸드폰번호를 입력하세요.\n\n주문서작성시 쇼핑몰관리자가 문자메세지를 받으시려면 반드시 입력하셔야 합니다.\n\n숫자만 입력하세요.\n예) 0101234567");?>
            <input type="text" name="de_sms_hp" value="<?=$default['de_sms_hp']?>" id="de_sms_hp" class="frm_input" size="20">
        </td>
    </tr>
    <tr>
        <th><label for="de_icode_id">아이코드 회원아이디</label></th>
        <td colspan="3">
            <?=help("아이코드에서 사용하시는 회원아이디를 입력합니다.");?>
            <input type="text" name="de_icode_id" value="<?=$default['de_icode_id']?>" id="de_icode_id" class="frm_input" size="20">
        </td>
    </tr>
    <tr>
        <th><label for="de_icode_pw">아이코드 패스워드</label></th>
        <td colspan="3">
            <?=help("아이코드에서 사용하시는 패스워드를 입력합니다.");?>
            <input type="password" name="de_icode_pw" value="<?=$default['de_icode_pw']?>" class="frm_input" id="de_icode_pw">
        </td>
    </tr>
    <tr>
        <th>요금제</th>
        <td>
            <input type="hidden" name="de_icode_server_ip" value="<?=$default['de_icode_server_ip']?>">
            <?
                if ($userinfo['payment'] == "A") {
                echo "충전제";
                echo "<input type=hidden name=de_icode_server_port value='7295'>";
                }
                else if ($userinfo['payment'] == "C") {
                echo "정액제";
                echo "<input type=hidden name=de_icode_server_port value='7296'>";
                }
                else {
                echo "가입해주세요.";
                echo "<input type=hidden name=de_icode_server_port value='7295'>";
                }
            ?>
        </td>
        <th>아이코드 서비스 신청(회원가입)</th>
        <td>
            <?=help("이 페이지에서 회원가입 하시면 문자 건당 16원에 제공 받을 수 있습니다.");?>
            <a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank">http://www.icodekorea.com</a>
        </td>

    </tr>
     <? if ($userinfo['payment'] == 'A') { ?>
    <tr class="ht">
        <td>충전 잔액</td>
        <td colspan="3">
            <?=number_format($userinfo['coin'])?> 원.
            <input type=button class=btn1 value='충전하기' onclick="window.open('http://www.icodekorea.com/smsbiz/credit_card_amt.php?icode_id=<?=$sms4['cf_id']?>&icode_passwd=<?=$sms4['cf_pw']?>','icode_payment', 'scrollbars=1,resizable=1')">
        </td>
    </tr>
    <tr class="ht">
        <td>건수별 금액</td>
        <td colspan="3">
            <?=number_format($userinfo['gpay'])?> 원.
        </td>
    </tr>
    <? } ?>
    <!-- <tr class=ht>
        <td>아이코드 서버 IP</td>
        <td colspan=3>
            <input type=text name=de_icode_server_ip value='<?=$default[de_icode_server_ip]?$default[de_icode_server_ip]:"211.172.232.124";?>' size=20>
            <?=help("아이코드에서 문자메세지를 발송하는 서버의 IP 를 입력하십시오.\n\n기본값은 211.172.232.124 입니다.");?>
        </td>
    </tr>
    <tr class=ht>
        <td>아이코드 서버 Port</td>
        <td colspan=3>
            <select id=de_icode_server_port name=de_icode_server_port>
            <option value=''>사용안함
            <option value='7295'>충전식
            <option value='7296'>정액제
            </select>
            <script>document.getElementById('de_icode_server_port').value="<?=$default[de_icode_server_port]?>";</script>
        </td>
    </tr>
     -->
     <tr>
         <td colspan="4">
            <br>회원가입시  : {이름} {회원아이디} {회사명}
            <br>주문서작성 : {이름} {보낸분} {받는분} {주문번호} {주문금액} {회사명}
            <br>입금확인시 : {이름} {입금액} {주문번호} {회사명}
            <br>상품배송시 : {이름} {택배회사} {운송장번호} {주문번호} {회사명}
            <p>주의) 80 bytes 까지만 전송됩니다. (영문 한글자 : 1byte , 한글 한글자 : 2bytes , 특수문자의 경우 1 또는 2 bytes 임)
            <br>
            <br>
         </td>
     </tr>
     </tbody>
    </table>
    <p style="text-align:center">
    <input type="submit" value="  확  인  " class="btn1" accesskey="s">
</section>
    </form>

<script type="text/javascript">
function fconfig_check(f)
{
    <?=get_editor_js('de_baesong_content');?>
    <?=get_editor_js('de_change_content');?>
    <?=get_editor_js('de_guest_privacy');?>

    return true;
}

// document.fconfig.de_admin_company_name.focus();

$(function() {
    $("#test_tip").bind("click", function() {
        $("#test_tip_help").toggle();
    })
    .css("cursor", "pointer")
    .css("text-decoration", "underline");
});
</script>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
