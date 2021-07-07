<?php
include_once('./_common.php');

$pp_id = isset($_REQUEST['pp_id']) ? preg_replace('/[^0-9]/', '', $_REQUEST['pp_id']) : 0;

if (G5_IS_MOBILE) {
    include_once(G5_MSHOP_PATH.'/personalpayresult.php');
    return;
}

$sql = "select * from {$g5['g5_shop_personalpay_table']} where pp_id = '$pp_id' ";
$pp = sql_fetch($sql);
if (! (isset($pp['pp_id']) && $pp['pp_id']) || (md5($pp['pp_id'].$pp['pp_time'].$_SERVER['REMOTE_ADDR']) != get_session('ss_personalpay_uid'))) {
    alert("조회하실 개인결제 내역이 없습니다.", G5_SHOP_URL);
}

// 결제방법
$settle_case = $pp['pp_settle_case'];

$g5['title'] = '개인결제상세내역';
include_once('./_head.php');

// LG 현금영수증 JS
if($pp['pp_pg'] == 'lg') {
    if($default['de_card_test']) {
    echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_TEST_JS.'"></script>'.PHP_EOL;
    } else {
        echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_REAL_JS.'"></script>'.PHP_EOL;
    }
}
?>

<!-- 주문상세내역 시작 { -->
<div id="sod_fin">

    <p id="sod_fin_no">개인결제번호 <strong><?php echo $pp_id; ?></strong></p>

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
            $app_no_subj = '거래번호';
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

                // 승인번호, 휴대폰번호, 거래번호
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
                    <td><?php echo get_text($pp['pp_deposit_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row">입금계좌</th>
                    <td><?php echo get_text($pp['pp_bank_account']); ?></td>
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
                            if($pp['pp_pg'] == 'lg') {
                                require_once G5_SHOP_PATH.'/settle_lg.inc.php';
                                $LGD_TID      = $pp['pp_tno'];
                                $LGD_MERTKEY  = $config['cf_lg_mert_key'];
                                $LGD_HASHDATA = md5($LGD_MID.$LGD_TID.$LGD_MERTKEY);

                                $hp_receipt_script = 'showReceiptByTID(\''.$LGD_MID.'\', \''.$LGD_TID.'\', \''.$LGD_HASHDATA.'\');';
                            } else if($pp['pp_pg'] == 'inicis') {
                                $hp_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid='.$pp['pp_tno'].'&noMethod=1\',\'receipt\',\'width=430,height=700\');';
                            } else {
                                $hp_receipt_script = 'window.open(\''.G5_BILL_RECEIPT_URL.'mcash_bill&tno='.$pp['pp_tno'].'&order_no='.$pp['pp_id'].'&trade_mony='.$pp['pp_receipt_price'].'\', \'winreceipt\', \'width=500,height=690,scrollbars=yes,resizable=yes\');';
                            }
                        ?>
                        <a href="javascript:;" onclick="<?php echo $hp_receipt_script; ?>">영수증 출력</a>
                        <?php
                        }

                        if($pp['pp_settle_case'] == '신용카드')
                        {
                            if($pp['pp_pg'] == 'lg') {
                                require_once G5_SHOP_PATH.'/settle_lg.inc.php';
                                $LGD_TID      = $pp['pp_tno'];
                                $LGD_MERTKEY  = $config['cf_lg_mert_key'];
                                $LGD_HASHDATA = md5($LGD_MID.$LGD_TID.$LGD_MERTKEY);

                                $card_receipt_script = 'showReceiptByTID(\''.$LGD_MID.'\', \''.$LGD_TID.'\', \''.$LGD_HASHDATA.'\');';
                            } else if($pp['pp_pg'] == 'inicis') {
                                $card_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/mCmReceipt_head.jsp?noTid='.$pp['pp_tno'].'&noMethod=1\',\'receipt\',\'width=430,height=700\');';
                            } else {
                                $card_receipt_script = 'window.open(\''.G5_BILL_RECEIPT_URL.'card_bill&tno='.$pp['pp_tno'].'&order_no='.$pp['pp_id'].'&trade_mony='.$pp['pp_receipt_price'].'\', \'winreceipt\', \'width=470,height=815,scrollbars=yes,resizable=yes\');';
                            }
                        ?>
                        <a href="javascript:;" onclick="<?php echo $card_receipt_script; ?>">영수증 출력</a>
                        <?php
                        }
                        ?>
                    <td>
                    </td>
                </tr>
                <?php
                }

                // 현금영수증 발급을 사용하는 경우에만
                if ($default['de_taxsave_use']) {
                    $is_cash_receipt = true;

                    // 주문내역이 있으면 현금영수증 발급하지 않음
                    if($pp['od_id']) {
                        $sql = " select count(od_id) as cnt from {$g5['g5_shop_order_table']} where od_id = '{$pp['od_id']}' ";
                        $row = sql_fetch($sql);

                        if($row['cnt'] > 0)
                            $is_cash_receipt = false;
                    }

                    // 미수금이 없고 현금일 경우에만 현금영수증을 발급 할 수 있습니다.
                    if ($is_cash_receipt && $misu_price == 0 && $pp['pp_receipt_price'] && ($pp['pp_settle_case'] == '계좌이체' || $pp['pp_settle_case'] == '가상계좌')) {
                ?>
                <tr>
                    <th scope="row">현금영수증</th>
                    <td>
                    <?php
                    if ($pp['pp_cash'])
                    {
                        if($pp['pp_pg'] == 'lg') {
                            require_once G5_SHOP_PATH.'/settle_lg.inc.php';

                            switch($pp['pp_settle_case']) {
                                case '계좌이체':
                                    $trade_type = 'BANK';
                                    break;
                                case '가상계좌':
                                    $trade_type = 'CAS';
                                    break;
                                default:
                                    $trade_type = 'CR';
                                    break;
                            }
                            $cash_receipt_script = 'javascript:showCashReceipts(\''.$LGD_MID.'\',\''.$pp['pp_id'].'\',\''.$pp['pp_casseqno'].'\',\''.$trade_type.'\',\''.$CST_PLATFORM.'\');';
                        } else if($pp['pp_pg'] == 'inicis') {
                            $cash = unserialize($pp['pp_cash_info']);
                            $cash_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/Cash_mCmReceipt.jsp?noTid='.$cash['TID'].'&clpaymethod=22\',\'showreceipt\',\'width=380,height=540,scrollbars=no,resizable=no\');';
                        } else {
                            require_once G5_SHOP_PATH.'/settle_kcp.inc.php';

                            $cash = unserialize($pp['pp_cash_info']);
                            $cash_receipt_script = 'window.open(\''.G5_CASH_RECEIPT_URL.$default['de_kcp_mid'].'&orderid='.$pp_id.'&bill_yn=Y&authno='.$cash['receipt_no'].'\', \'taxsave_receipt\', \'width=360,height=647,scrollbars=0,menus=0\');';
                        }
                    ?>
                        <a href="javascript:;" onclick="<?php echo $cash_receipt_script; ?>" class="btn_frmline">현금영수증 확인하기</a>
                    <?php
                    }
                    else
                    {
                    ?>
                        <a href="javascript:;" onclick="window.open('<?php echo G5_SHOP_URL; ?>/taxsave.php?tx=personalpay&od_id=<?php echo $pp_id; ?>', 'taxsave', 'width=550,height=400,scrollbars=1,menus=0');" class="btn_frmline">현금영수증을 발급하시려면 클릭하십시오.</a>
                    <?php } ?>
                    </td>
                </tr>
                <?php
                    }
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

    <?php if ($pp['pp_settle_case'] == '가상계좌'  && $pp['pp_receipt_price'] == 0 && $default['de_card_test'] && $is_admin && $pp['pp_pg'] == 'kcp') {
    preg_match("/\s{1}([^\s]+)\s?/", $pp['pp_bank_account'], $matchs);
    $deposit_no = trim($matchs[1]);
    ?>
    <div class="tbl_frm01 tbl_wrap">
        <form method="post" action="http://devadmin.kcp.co.kr/Modules/Noti/TEST_Vcnt_Noti_Proc.jsp" target="_blank">
        <p>관리자가 가상계좌 테스트를 한 경우에만 보입니다.</p>
        <table>
        <caption>모의입금처리</caption>
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="col"><label for="e_trade_no">KCP 거래번호</label></th>
            <td><input type="text" name="e_trade_no" value="<?php echo $pp['pp_tno']; ?>" size="80"></td>
        </tr>
        <tr>
            <th scope="col"><label for="deposit_no">입금계좌</label></th>
            <td><input type="text" name="deposit_no" value="<?php echo $deposit_no; ?>" size="80"></td>
        </tr>
        <tr>
            <th scope="col"><label for="req_name">입금자명</label></th>
            <td><input type="text" name="req_name" value="<?php echo $pp['pp_deposit_name']; ?>" size="80"></td>
        </tr>
        <tr>
            <th scope="col"><label for="noti_url">입금통보 URL</label></th>
            <td><input type="text" name="noti_url" value="<?php echo G5_SHOP_URL; ?>/settle_kcp_common.php" size="80"></td>
        </tr>
        </tbody>
        </table>
        <div id="sod_fin_test" class="btn_confirm">
            <input type="submit" value="입금통보 테스트" class="btn_submit">
        </div>
        </form>
    </div>
    <?php } ?>

</div>
<!-- } 개인결제상세내역 끝 -->

<?php
include_once('./_tail.php');