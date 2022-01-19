<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<!-- 필수 입력값 -->
<input type="hidden" name="TrKey"       value=""/>                                          <!-- 필드만 필요 -->
<input type="hidden" name="PayMethod"   value="">

<input type="hidden" name="GoodsCnt"    value="<?php echo $goods_count+1; ?>">              <!-- goods count는 초기1개의 값을 count하지 않음으로 +1 -->
<input type="hidden" name="GoodsName"   value="<?php echo $goods; ?>">

<input type="hidden" name="Amt"         value="<?php echo $tot_price; ?>">
<input type="hidden" name="MID"         value="<?php echo $mid; ?>">

<input type="hidden" name="BuyerName"   value="">
<input type="hidden" name="BuyerTel"    value="">

<input type="hidden" name="EncryptData" value="">                                          <!-- 해쉬값	-->

<!-- 선택 입력값 -->

<input type="hidden" name="BuyerEmail"  value="">
<input type="hidden" name="BuyerAddr"   value="">
<input type="hidden" name="BuyerPostNo" value="">
<input type="hidden" name="MallUserId"  value="<?php echo isset($member) ? $member['mb_id'] : ""?>">


<input type="hidden" name="EdiDate"     value="<?php echo $ediDate; ?>"/>             <!-- 전문 생성일시 -->
<input type="hidden" name="Moid"        value="<?php echo $od_id; ?>">


<input type="hidden" name="RcvrName"    value="">
<input type="hidden" name="RcvrTel"     value="">
<input type="hidden" name="RcvrZipx"    value="">
<input type="hidden" name="RcvrAddr"    value="">

<input type="hidden" name="timestamp"   value="">

<!-- 실물에 따라 0 or 1 -->
<input type="hidden" name="GoodsCl"     value="<?php echo $goodsCl; ?>"/>                                                   <!--실물(1) 컨텐츠(0) -->

<input type="hidden" name="good_mny"    value="<?php echo $tot_price; ?>">

<input type="hidden" name="TransType"   value="<?php echo $useescrow; ?>">

<input type="hidden" name="OptionList"  value="<?php echo $optionList; ?>">
<input type="hidden" name="ReturnURL"   value="<?php echo $return_url; ?>">

<?php if($default['de_tax_flag_use']) { ?>
<input type="hidden" name="comm_tax_mny"	  value="<?php echo $comm_tax_mny; ?>">         <!-- 과세금액    -->
<input type="hidden" name="comm_vat_mny"      value="<?php echo $comm_vat_mny; ?>">         <!-- 부가세	    -->
<input type="hidden" name="comm_free_mny"     value="<?php echo $comm_free_mny; ?>">        <!-- 비과세 금액 -->
<?php } ?>

<div id="display_pay_button" class="btn_confirm">
    <span id="show_req_btn"><input type="button" name="submitChecked" onClick="pay_approval();" value="결제등록" class="btn_submit"></span>
    <span id="show_pay_btn" style="display:none;"><input type="button" onClick="forderform_check();" value="주문하기" class="btn_submit"></span>
    <a href="<?php echo G5_SHOP_URL; ?>" class="btn_cancel">취소</a>
</div>


<script type="text/javascript">
function make_signature(frm)
{
    // 데이터 암호화 처리
    var result = true;
    $.ajax({
        url: g5_url+"/shop/nicepay/makesignature.php",
        type: "POST",
        data: {
            ediDate : frm.EdiDate.value,
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data.error == "") {
                frm.EncryptData.value = data.EncryptData;
            } else {
                alert(data.error);
                result = false;
            }
        }
    });

    return result;
}
//결제창 최초 요청시 실행됩니다.
function paybtn(f){
    goPay(f);
}

//결제 최종 요청시 실행됩니다. <<'nicepaySubmit()' 이름 수정 불가능>>
// function nicepaySubmit(){
//     $("#forderform").submit();
// }
</script>