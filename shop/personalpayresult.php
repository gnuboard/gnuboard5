<?php
include_once('./_common.php');

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/personalpayresult.php');
    return;
}

$sql = "select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' ";
$pp = sql_fetch($sql);
if (!$pp['pp_id'] || (md5($pp['pp_id'].$pp['pp_time'].$_SERVER['REMOTE_ADDR']) != get_session('ss_personalpay_uid'))) {
    alert("조회하실 개인결제 내역이 없습니다.", G5_SHOP_URL);
}

// 결제방법
$settle_case = $pp['pp_settle_case'];

$g5['title'] = '개인결제상세내역';
include_once('./_head.php');
?>

<!-- 주문상세내역 시작 { -->
<script>
var openwin = window.open( './kcp/proc_win.html', 'proc_win', '' );
if(openwin != null) {
    openwin.close();
}
</script>

<div id="sod_fin">

    <p>개인결제번호 <strong><?php echo $pp_id; ?></strong></p>

    <section id="sod_fin_view">
        <h2>결제 정보</h2>
        <?php
        $misu = true;

        if ($pp['pp_price'] == $pp['pp_receipt_price']) {
            $wanbul = " (완불)";
            $misu = false; // 미수금 없음
        }
        else
        {
            $wanbul = display_price($pp['pp_receipt_price']);
        }

        $misu_price = $pp['pp_price'] - $pp['pp_receipt_price'];

        // 결제정보처리
        if($pp['pp_receipt_price'] > 0)
            $pp_receipt_price = display_price($pp['pp_receipt_price']);
        else
            $pp_receipt_price = '아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.';

        $app_no_subj = '';
        $disp_bank = true;
        $disp_receipt = false;
        if($pp['pp_settle_case'] == '신용카드') {
            $app_no_subj = '승인번호';
            $app_no = $pp['pp_app_no'];
            $disp_bank = false;
            $disp_receipt = true;
        } else if($pp['pp_settle_case'] == '휴대폰') {
            $app_no_subj = '휴대폰번호';
            $app_no = $pp['pp_bank_account'];
            $disp_bank = false;
            $disp_receipt = true;
        } else if($pp['pp_settle_case'] == '가상계좌' || $pp['pp_settle_case'] == '계좌이체') {
            $app_no_subj = 'KCP 거래번호';
            $app_no = $pp['pp_tno'];
        }
        ?>

        <section id="sod_fin_pay">
            <h3>결제정보</h3>

            <div class="tbl_head01 tbl_wrap">
                <table>
                <colgroup>
                    <col class="grid_3">
                    <col>
                </colgroup>
                <tbody>
                <?php if($pp['od_id']) { ?>
                <tr>
                    <th scope="row">주문번호</th>
                    <td><?php echo $pp['od_id']; ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row">결제방식</th>
                    <td><?php echo $pp['pp_settle_case']; ?></td>
                </tr>
                <?php if($pp_receipt_price) { ?>
                <tr>
                    <th scope="row">결제금액</th>
                    <td><?php echo $pp_receipt_price; ?></td>
                </tr>
                <tr>
                    <th scope="row">결제일시</th>
                    <td><?php echo is_null_time($pp['pp_receipt_time']) ? '' : $pp['pp_receipt_time']; ?></td>
                </tr>
                <?php
                }

                // 승인번호, 휴대폰번호, KCP 거래번호
                if($app_no_subj)
                {
                ?>
                <tr>
                    <th scope="row"><?php echo $app_no_subj; ?></th>
                    <td><?php echo $app_no; ?></td>
                </tr>
                <?php
                }

                // 계좌정보
                if($disp_bank)
                {
                ?>
                <tr>
                    <th scope="row">입금자명</th>
                    <td><?php echo $pp['pp_deposit_name']; ?></td>
                </tr>
                <tr>
                    <th scope="row">입금계좌</th>
                    <td><?php echo $pp['pp_bank_account']; ?></td>
                </tr>
                <?php
                }

                if($disp_receipt) {
                ?>
                <tr>
                    <th scope="row">영수증</th>
                    <td>
                        <?php
                        if($pp['pp_settle_case'] == '휴대폰')
                        {
                        ?>
                        <a href="javascript:;" onclick="window.open('https://admin.kcp.co.kr/Modules/Bill/ADSA_MCASH_N_Receipt.jsp?a_trade_no=<?php echo $pp['pp_tno']; ?>', 'winreceipt', 'width=500,height=690')">영수증 출력</a>
                        <?php
                        }

                        if($pp['pp_settle_case'] == '신용카드')
                        {
                        ?>
                        <a href="javascript:;" onclick="window.open('http://admin.kcp.co.kr/Modules/Sale/Card/ADSA_CARD_BILL_Receipt.jsp?c_trade_no=<?php echo $pp['pp_tno']; ?>', 'winreceipt', 'width=620,height=800')">영수증 출력</a>
                        <?php
                        }
                        ?>
                    <td>
                    </td>
                </tr>
                <?php
                }
                ?>
                </tbody>
                </table>
            </div>
        </section>
    </section>

    <section id="sod_fin_tot">
        <h2>결제합계</h2>

        <ul>
            <li>
                총 주문액
                <strong><?php echo display_price($pp['pp_price']); ?></strong>
            </li>
            <?php
            if ($misu_price > 0) {
            echo '<li>';
            echo '미결제액'.PHP_EOL;
            echo '<strong>'.display_price($misu_price).'</strong>';
            echo '</li>';
            }
            ?>
            <li id="alrdy">
                결제액
                <strong><?php echo $wanbul; ?></strong>
            </li>
        </ul>
    </section>

    <?php if ($pp['pp_settle_case'] == '가상계좌' && $default['de_card_test'] && $is_admin) {
    preg_match("/(\s[^\s]+\s)/", $pp['pp_bank_account'], $matchs);
    $deposit_no = trim($matchs[1]);
    ?>
    <fieldset>
    <legend>모의입금처리</legend>
    <p>관리자가 가상계좌 테스트를 한 경우에만 보입니다.</p>
    <form method="post" action="http://devadmin.kcp.co.kr/Modules/Noti/TEST_Vcnt_Noti_Proc.jsp" target="_blank">
    <input type="text" name="e_trade_no" value="<?php echo $pp['pp_tno']; ?>" size="80"><br />
    <input type="text" name="deposit_no" value="<?php echo $deposit_no; ?>" size="80"><br />
    <input type="text" name="req_name" value="<?php echo $pp['pp_deposit_name']; ?>" size="80"><br />
    <input type="text" name="noti_url" value="<?php echo G5_SHOP_URL; ?>/settle_kcp_common.php" size="80"><br /><br />
    <input type="submit" value="입금통보 테스트">
    </form>
    </fieldset>
    <?php } ?>

</div>
<!-- } 개인결제상세내역 끝 -->

<?php
include_once('./_tail.php');
?>