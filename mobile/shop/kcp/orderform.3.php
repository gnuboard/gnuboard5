<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<section id="sod_frm_escrow">
    <h2>에스크로 안내</h2>
    <form name="escrow_foot" method="post" action="http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp">
    <input type="hidden" name="site_cd" value="<?php echo $default['de_kcp_mid']; ?>">
    <table border="0" cellspacing="0" cellpadding="0">
    <tr>
        <td align='center'><img src="<?php echo G5_SHOP_URL; ?>/img/marks_escrow/escrow_foot.gif" width="290" height="92" border="0" usemap="#Map"></td>
    </tr>
    <tr>
        <td style='line-height:150%;'>
            <br>
            <strong>에스크로(escrow) 제도란?</strong>
            <br>상거래 시에, 판매자와 구매자의 사이에 신뢰할 수 있는 중립적인 제삼자(여기서는 <a href='http://kcp.co.kr' target='_blank'>KCP</a>)가 중개하여
            금전 또는 물품을 거래를 하도록 하는 것, 또는 그러한 서비스를 말한다. 거래의 안전성을 확보하기 위해 이용된다.
            (2006.4.1 전자상거래 소비자보호법에 따른 의무 시행)
            <br><br>
            현금 거래에만 해당(에스크로 결제를 선택했을 경우에만 해당)되며,
            신용카드로 구매하는 거래, 배송이 필요하지 않은 재화 등을 구매하는 거래(컨텐츠 등)에는 해당되지 않는다.
            <br>
            <br>
        </td>
    </tr>
    </table>
    <map name="Map" id="Map">
    <area shape="rect" coords="5,62,74,83" href="javascript:escrow_foot_check()" alt="가입사실확인">
    </map>
    </form>
</section>

<script>
function escrow_foot_check()
{
    var status  = "width=500 height=450 menubar=no,scrollbars=no,resizable=no,status=no";
    var obj     = window.open('', 'escrow_foot_pop', status);

    document.escrow_foot.method = "post";
    document.escrow_foot.target = "escrow_foot_pop";
    document.escrow_foot.action = "http://admin.kcp.co.kr/Modules/escrow/kcp_pop.jsp";

    document.escrow_foot.submit();
}
</script>

<!-- <?php if ($default['de_card_use'] || $default['de_iche_use']) { echo "결제대행사 : KCP"; } ?> -->