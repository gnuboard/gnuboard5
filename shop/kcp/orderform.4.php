<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 에스크로 안내 시작 { -->
<section id="sod_frm_escrow">
    <h2>에스크로 안내</h2>
    <table>
    <tr>
        <td><a href="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp?site_cd=<?php echo $default['de_kcp_mid']; ?>" class="nhnkcp_escrow_popup" data-sitecd="<?php echo $default['de_kcp_mid']; ?>" target="_blank"><img src="<?php echo G5_SHOP_URL; ?>/img/marks_escrow/escrow_foot.gif" width="290" height="92" border="0" usemap="#Map"></a></td>
    </tr>
    <tr>
        <td>
            <strong>에스크로(escrow) 제도란?</strong>
            <p>
                상거래 시에, 판매자와 구매자의 사이에 신뢰할 수 있는 중립적인 제삼자(여기서는 <a href='http://kcp.co.kr' target='_blank'>KCP</a>)가 중개하여
                금전 또는 물품을 거래를 하도록 하는 것, 또는 그러한 서비스를 말한다. 거래의 안전성을 확보하기 위해 이용된다.
                (2006.4.1 전자상거래 소비자보호법에 따른 의무 시행)
            </p>
            <p>
                현금 거래에만 해당(에스크로 결제를 선택했을 경우에만 해당)되며,
                신용카드로 구매하는 거래, 배송이 필요하지 않은 재화 등을 구매하는 거래(컨텐츠 등)에는 해당되지 않는다.
            </p>
        </td>
    </tr>
    </table>
    <map name="Map" id="Map">
    <area shape="rect" coords="5,62,74,83" href="javascript:escrow_foot_check()" alt="가입사실확인">
    </map>
</section>

<script>
jQuery("#sod_frm_escrow .nhnkcp_escrow_popup").on("click", function(e){
    e.preventDefault();
    escrow_foot_check();
});

function escrow_foot_check()
{
    var status  = "width=500 height=450 menubar=no,scrollbars=no,resizable=no,status=no";
    var obj     = window.open("", "escrow_foot_pop", status);

    var newForm = jQuery("<form>", {
        "id": "nhnkcp_escrow_form_popup",
        "action": "http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp?site_cd="+jQuery("#sod_frm_escrow .nhnkcp_escrow_popup").attr("data-sitecd"),
        "target": "escrow_foot_pop",
        "method": "post"
    }).append(jQuery("<input>", {
        "name": "site_cd",
        "value": jQuery("#sod_frm_escrow .nhnkcp_escrow_popup").attr("data-sitecd"),
        "type": "hidden"
    }));

    if( ! jQuery("#nhnkcp_escrow_form_popup").length ){
        newForm.hide().appendTo("body").submit();
    } else {
        jQuery("#nhnkcp_escrow_form_popup").submit();
    }

}
</script>
<!-- } 에스크로 안내 끝 -->

<!-- <?php if ($default['de_card_use'] || $default['de_iche_use']) { echo "결제대행사 : KCP"; } ?> -->