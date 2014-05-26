<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<form name="sm_form" method="POST" action="<?php echo G5_MSHOP_URL; ?>/lg/xpay_approval.php">
<input type="hidden" name="LGD_OID"                     id="LGD_OID"            value="<?php echo $od_id; ?>">                                   <!-- 주문번호 -->
<input type="hidden" name="LGD_BUYER"                   id="LGD_BUYER"          value="">                                  <!-- 구매자 -->
<input type="hidden" name="LGD_PRODUCTINFO"             id="LGD_PRODUCTINFO"    value="<?php echo $goods; ?>">             <!-- 상품정보 -->
<input type="hidden" name="LGD_AMOUNT"                  id="LGD_AMOUNT"         value="">                                  <!-- 결제금액 -->
<input type="hidden" name="LGD_CUSTOM_FIRSTPAY"         id="LGD_CUSTOM_FIRSTPAY" value="">                                 <!-- 결제수단 -->
<input type="hidden" name="LGD_BUYEREMAIL"              id="LGD_BUYEREMAIL"     value="">                                  <!-- 구매자 이메일 -->
<input type="hidden" name="LGD_TAXFREEAMOUNT"           id="LGD_TAXFREEAMOUNT"  value="<?php echo $comm_free_mny; ?>">     <!-- 결제금액 중 면세금액 -->
<input type="hidden" name="LGD_BUYERID"                 id="LGD_BUYERID"        value="<?php echo $LGD_BUYERID; ?>">       <!-- 구매자ID -->
<input type="hidden" name="LGD_CASHRECEIPTYN"           id="LGD_CASHRECEIPTYN"  value="N">                                 <!-- 현금영수증 사용 설정 -->
<input type="hidden" name="LGD_BUYERPHONE"              id="LGD_BUYERPHONE"     value="">                                  <!-- 구매자 휴대폰번호 -->

<input type="hidden" name="good_mny"          value="<?php echo $tot_price ?>" >
</form>