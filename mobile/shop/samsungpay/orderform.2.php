<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//삼성페이 또는 L.pay 사용시에만 해당함
if( ! is_inicis_simple_pay() || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}
?>
<input type="hidden" name="good_mny"          value="<?php echo $tot_price ?>" >
<input type="hidden" name="res_cd"            value="">                                     <!-- 결과 코드          -->

<input type="hidden" name="P_HASH"            value="">
<input type="hidden" name="P_TYPE"            value="">
<input type="hidden" name="P_UNAME"           value="">
<input type="hidden" name="P_AUTH_DT"         value="">
<input type="hidden" name="P_AUTH_NO"         value="">
<input type="hidden" name="P_HPP_CORP"        value="">
<input type="hidden" name="P_APPL_NUM"        value="">
<input type="hidden" name="P_VACT_NUM"        value="">
<input type="hidden" name="P_VACT_NAME"       value="">
<input type="hidden" name="P_VACT_BANK"       value="">
<input type="hidden" name="P_CARD_ISSUER"     value="">