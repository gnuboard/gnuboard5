<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/orderinquiryview.php');
    return;
}

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

if (!$is_member) {
    if (get_session("ss_temp_uq_id") != $_GET['uq_id'])
        alert("직접 링크로는 주문서 조회가 불가합니다.\\n\\n주문조회 화면을 통하여 조회하시기 바랍니다.");
}

$sql = "select * from {$g4['shop_order_table']} where od_id = '$od_id' and uq_id = '$uq_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    echo "$od_id $uq_id $MxIssueNO";
    alert("조회하실 주문서가 없습니다.", G4_SHOP_URL);
}

// 처리 중인 요청이 있는지..
$dsp_request = true;
$sql = " select count(*) as cnt from {$g4['shop_request_table']} where od_id = '$od_id' and rq_status = '0' ";
$rq = sql_fetch($sql);
if($rq['cnt'])
    $dsp_request = false;

// 결제방법
$settle_case = $od['od_settle_case'];

set_session('ss_temp_uq_id', $uq_id);

$g4['title'] = '주문상세내역';
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

    <p>주문번호 <strong><?php echo $od_id; ?></strong></p>

    <section id="sod_fin_list">
    <form name="forderrequest" method="post" action="./orderrequestupdate.php" onsubmit="return frequest_check(this);">
    <input type="hidden" name="od_id" value="<?php echo $od['od_id']; ?>">
    <input type="hidden" name="uq_id" value="<?php echo $od['uq_id']; ?>">
    <input type="hidden" name="rq_type" value="">
        <h2>주문하신 상품</h2>
        <span class="sound_only">상품 상태 설명</span>
        <dl id="sod_fin_legend">
            <dt>주문</dt>
            <dd>주문이 접수되었습니다.</dd>
            <dt>준비</dt>
            <dd>상품 준비 중입니다.</dd>
            <dt>배송</dt>
            <dd>상품 배송 중입니다.</dd>
            <dt>완료</dt>
            <dd>상품 배송이 완료되었습니다.</dd>
        </dl>
        <?php
        $od_count1 = $od_count2 = 0;
        $idx = 0;

        $sql = " select it_id, it_name, cp_amount
                    from {$g4['shop_cart_table']}
                    where uq_id = '$uq_id'
                      and ct_num = '0'
                    order by ct_id ";
        $result = sql_query($sql);
        ?>
        <ul id="sod_ul">
            <?php
            for($i=0; $row=sql_fetch_array($result); $i++) {
                $image = get_it_image($row['it_id'], 70, 70);
            ?>
            <li>
                <p>
                    <a href="./item.php?it_id=<?php echo $row['it_id']; ?>"><?php echo $image; ?><?php echo $row['it_name']; ?></a>
                </p>

                <table class="basic_tbl">
                <thead>
                <tr>
                    <th scope="col" colspan="2">옵션항목</th>
                    <th scope="col">수량</th>
                    <th scope="col">판매가</th>
                    <th scope="col">소계</th>
                    <th scope="col">포인트</th>
                    <th scope="col">상태</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $sql = " select ct_id, ct_option, ct_qty, ct_price, ct_point, ct_status, io_type, io_price
                            from {$g4['shop_cart_table']}
                            where uq_id = '$uq_id'
                              and it_id = '{$row['it_id']}'
                            order by ct_num ";
                $res = sql_query($sql);

                for($k=0; $opt=sql_fetch_array($res); $k++) {
                    if($opt['io_type'])
                        $opt_price = $opt['io_price'];
                    else
                        $opt_price = $opt['ct_price'] + $opt['io_price'];

                    $sell_amount = $opt_price * $opt['ct_qty'];
                    $point = $opt['ct_point'] * $opt['ct_qty'];
                ?>
                <tr>
                    <td>
                        <input type="hidden" name="ct_id[<?php echo $idx; ?>]" value="<?php echo $opt['ct_id']; ?>">
                        <label for="chk_ct_id_<?php echo $idx; ?>"><?php echo $opt['ct_option']; ?> 선택</label>
                        <input type="checkbox" name="chk_ct_id[<?php echo $idx; ?>]" id="chk_ct_id_<?php echo $idx; ?>" value="1">
                    </td>
                    <td><?php echo $opt['ct_option']; ?></td>
                    <td class="td_smallmng"><?php echo number_format($opt['ct_qty']); ?></td>
                    <td class="td_bignum"><?php echo number_format($opt_price); ?></td>
                    <td class="td_num"><?php echo number_format($sell_amount); ?></td>
                    <td class="td_num"><?php echo number_format($point); ?></td>
                    <td class="td_smallmng"><?php echo $opt['ct_status']; ?></td>
                </tr>
                <?php
                    if ($opt['ct_status'] == '취소' || $opt['ct_status'] == '반품' || $opt['ct_status'] == '품절') {
                        $tot_cancel_amount += $sell_amount;
                    }
                    else {
                        $tot_point       += $point;
                        $tot_sell_amount += $sell_amount;
                    }

                    // 전체 상품의 상태가 주문인지 비교할 때 사용
                    $od_count1++;
                    if($opt['ct_status'] == '주문')
                        $od_count2++;

                    $idx++;
                }
                ?>
                </tbody>
                </table>
            </li>
            <?php
                $tot_cp_amount += $row['cp_amount'];
            }

            $send_cost = $od['od_send_cost'];
            $send_cost2 = $od['od_send_cost2'];
            $send_coupon = $od['od_send_coupon'];
            $org_send_cost = $send_cost + $send_coupon;
            ?>
        </ul>

        <?php
        // 총계 = 주문상품금액합계 + 배송비 - 상품할인 - 결제할인
        $od_coupon = $od['od_coupon'];
        $tot_amount = $tot_sell_amount + $send_cost + $send_cost2 - $tot_cp_amount - $od_coupon;
        ?>

        <dl id="sod_bsk_tot">
            <dt class="sod_bsk_dvr">주문총액</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($tot_sell_amount); ?> 원</strong></dd>

            <?php if($tot_cp_amount > 0) { ?>
            <dt class="sod_bsk_dvr">상품할인</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($tot_cp_amount); ?> 원</strong></dd>
            <?php } ?>

            <?php if($od_coupon > 0) { ?>
            <dt class="sod_bsk_dvr">결제할인</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($od_coupon); ?> 원</strong></dd>
            <?php } ?>

            <?php if ($org_send_cost > 0) { ?>
            <dt class="sod_bsk_dvr">배송비</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($org_send_cost); ?> 원</strong></dd>
            <?php } ?>

            <?php if($send_coupon > 0) { ?>
            <dt class="sod_bsk_dvr">배송비할인</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($send_coupon); ?> 원</strong></dd>
            <?php } ?>

            <?php if ($send_cost2 > 0) { ?>
            <dt class="sod_bsk_dvr">추가배송비</dt>
            <dd class="sod_bsk_dvr"><strong><?php echo number_format($send_cost2); ?> 원</strong></dd>
            <?php } ?>

            <dt class="sod_bsk_cnt">총계</dt>
            <dd class="sod_bsk_cnt"><strong><?php echo number_format($tot_amount); ?> 원</strong></dd>

            <dt class="sod_bsk_point">포인트</dt>
            <dd class="sod_bsk_point"><strong><?php echo number_format($tot_point); ?> 점</strong></dd>
        </dl>

        <div id="request_form">
            <div>
                <label for="rq_content">요청내용</label>
                <input type="text" name="rq_content" id="rq_content" value="">
            </div>
            <div>
                <input type="submit" value="확인" required class="required">
                <button type="button" id="request_cancel">취소</button>
            </div>
        </div>

        <div>
            <button type="button" class="req_button">취소요청</button>
            <button type="button" class="req_button">교환요청</button>
            <button type="button" class="req_button">반품요청</button>
        </div>

    </form>
    </section>

    <div id="sod_fin_view">
        <h2>결제/배송 정보</h2>
        <?php
        $receipt_amount = $od['od_receipt_amount']
                        + $od['od_receipt_point']
                        - $od['od_cancel_card']
                        - $od['od_refund_amount'];

        $misu = true;

        if ($tot_amount - $tot_cancel_amount == $receipt_amount) {
            $wanbul = " (완불)";
            $misu = false; // 미수금 없음
        }
        else
        {
            $wanbul = display_price($receipt_amount);
        }

        $misu_amount = $tot_amount - $receipt_amount - $od['od_dc_amount'];

        // 결제정보처리
        if($od['od_receipt_amount'] > 0)
            $od_receipt_amount = display_price($od['od_receipt_amount']);
        else
            $od_receipt_amount = '아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.';

        $app_no_subj = '';
        $disp_bank = true;
        $disp_receipt = false;
        if($od['od_settle_case'] == '신용카드') {
            $sql = " select * from {$g4['shop_card_history_table']} where od_id = '{$od['od_id']}' ";
            $cd = sql_fetch($sql);

            $app_no_subj = '승인번호';
            $app_no = $cd['cd_app_no'];
            $disp_bank = false;
            $disp_receipt = true;
        } else if($od['od_settle_case'] == '휴대폰') {
            $app_no_subj = '휴대폰번호';
            $app_no = $od['od_bank_account'];
            $disp_bank = false;
            $disp_receipt = true;
        } else if($od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') {
            $app_no_subj = 'KCP 거래번호';
            $app_no = $od['od_tno'];
        }
        ?>

        <section id="sod_fin_pay">
            <h3>결제정보</h3>

            <table class="basic_tbl">
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">주문번호</th>
                <td><?php echo $od_id; ?></td>
            </tr>
            <tr>
                <th scope="row">주문일시</th>
                <td><?php echo $od['od_time']; ?></td>
            </tr>
            <tr>
                <th scope="row">결제방식</th>
                <td><?php echo $od['od_settle_case']; ?></td>
            </tr>
            <tr>
                <th scope="row">결제금액</th>
                <td><?php echo $od_receipt_amount; ?></td>
            </tr>
            <?php
            if($od['od_receipt_amount'] > 0)
            {
            ?>
            <tr>
                <th scope="row">결제일시</th>
                <td><?php echo $od['od_receipt_time']; ?></td>
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
                <td><?php echo $od['od_deposit_name']; ?></td>
            </tr>
            <tr>
                <th scope="row">입금계좌</th>
                <td><?php echo $od['od_bank_account']; ?></td>
            </tr>
            <?php
            }

            if($disp_receipt) {
            ?>
            <tr>
                <th scope="row">영수증</th>
                <td>
                    <?php
                    if($od['od_settle_case'] == '휴대폰')
                    {
                    ?>
                    <a href="javascript:;" onclick="window.open('https://admin.kcp.co.kr/Modules/Bill/ADSA_MCASH_N_Receipt.jsp?a_trade_no=<?php echo $od['od_tno']; ?>', 'winreceipt', 'width=500,height=690')">영수증 출력</a>
                    <?php
                    }

                    if($od['od_settle_case'] == '신용카드')
                    {
                    ?>
                    <a href="javascript:;" onclick="window.open('http://admin.kcp.co.kr/Modules/Sale/Card/ADSA_CARD_BILL_Receipt.jsp?c_trade_no=<?php echo $od['od_tno']; ?>', 'winreceipt', 'width=620,height=800')">영수증 출력</a>
                    <?php
                    }
                    ?>
                <td>
                </td>
            </tr>
            <?php
            }

            if ($od['od_receipt_point'] > 0)
            {
            ?>
            <tr>
                <th scope="row">포인트사용</th>
                <td><?php echo display_point($od['od_receipt_point']); ?></td>
            </tr>

            <?php
            }

            if ($od['od_cancel_card'] > 0)
            {
            ?>
            <tr>
                <th scope="row">결제취소 금액</th>
                <td><?php echo display_price($od['od_cancel_card']); ?></td>
            </tr>
            <?php
            }

            if ($od['od_refund_amount'] > 0)
            {
            ?>
            <tr>
                <th scope="row">환불 금액</th>
                <td><?php echo display_price($od['od_refund_amount']); ?></td>
            </tr>
            <?php
            }

            // 현금영수증 발급을 사용하는 경우에만
            if ($default['de_taxsave_use'] && $default['de_card_pg'] == 'kcp') {
                // 미수금이 없고 현금일 경우에만 현금영수증을 발급 할 수 있습니다.
                if ($misu_amount == 0 && $od['od_receipt_amount'] && ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '계좌이체' || $od['od_settle_case'] == '가상계좌')) {
            ?>
            <tr>
                <th scope="row">현금영수증</th>
                <td>
                <?php
                if ($od['od_cash'])
                {
                ?>
                    <a href="javascript:;" onclick="window.open('https://admin.kcp.co.kr/Modules/Service/Cash/Cash_Bill_Common_View.jsp?cash_no=<?php echo $od['od_cash_no']; ?>', 'taxsave_receipt', 'width=360,height=647,scrollbars=0,menus=0');" class="btn_frmline">현금영수증 확인하기</a>
                <?php
                }
                else
                {
                ?>
                    <a href="javascript:;" onclick="window.open('<?php echo G4_SHOP_URL; ?>/taxsave_kcp.php?od_id=<?php echo $od_id; ?>&amp;uq_id=<?php echo $od['uq_id']; ?>', 'taxsave', 'width=550,height=400,scrollbars=1,menus=0');" class="btn_frmline">현금영수증을 발급하시려면 클릭하십시오.</a>
                <?php } ?>
                </td>
            </tr>
            <?php
                }
            }
            ?>
            </tbody>
            </table>
        </section>

        <section id="sod_fin_orderer">
            <h3>주문하신 분</h3>
            <table class="basic_tbl">
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">이 름</th>
                <td><?php echo $od['od_name']; ?></td>
            </tr>
            <tr>
                <th scope="row">전화번호</th>
                <td><?php echo $od['od_tel']; ?></td>
            </tr>
            <tr>
                <th scope="row">핸드폰</th>
                <td><?php echo $od['od_hp']; ?></td>
            </tr>
            <tr>
                <th scope="row">주 소</th>
                <td><?php echo sprintf("(%s-%s)&nbsp;%s %s", $od['od_zip1'], $od['od_zip2'], $od['od_addr1'], $od['od_addr2']); ?></td>
            </tr>
            <tr>
                <th scope="row">E-mail</th>
                <td><?php echo $od['od_email']; ?></td>
            </tr>
            </tbody>
            </table>
        </section>

        <section id="sod_fin_receiver">
            <h3>받으시는 분</h3>
            <table class="basic_tbl">
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">이 름</th>
                <td><?php echo $od['od_b_name']; ?></td>
            </tr>
            <tr>
                <th scope="row">전화번호</th>
                <td><?php echo $od['od_b_tel']; ?></td>
            </tr>
            <tr>
                <th scope="row">핸드폰</th>
                <td><?php echo $od['od_b_hp']; ?></td>
            </tr>
            <tr>
                <th scope="row">주 소</th>
                <td><?php echo sprintf("(%s-%s)&nbsp;%s %s", $od['od_b_zip1'], $od['od_b_zip2'], $od['od_b_addr1'], $od['od_b_addr2']); ?></td>
            </tr>
            <?php
            // 희망배송일을 사용한다면
            if ($default['de_hope_date_use'])
            {
            ?>
            <tr>
                <th scope="row">희망배송일</td>
                <td><?php echo substr($od['od_hope_date'],0,10).' ('.get_yoil($od['od_hope_date']).')' ;?></td>
            </tr>
            <?php }
            if ($od['od_memo'])
            {
            ?>
            <tr>
                <th scope="row">전하실 말씀</td>
                <td><?php echo conv_content($od['od_memo'], 0); ?></td>
            </tr>
            <?php } ?>
            </tbody>
            </table>
        </section>

        <section id="sod_fin_dvr">
            <h3>배송정보</h3>
            <table class="basic_tbl">
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <?php
            // 배송회사 정보
            $dl = sql_fetch(" select * from {$g4['shop_delivery_table']} where dl_id = '{$od['dl_id']}' ");

            if ($od['od_invoice'] || !$od['misu'])
            {
                if (is_array($dl))
                {
                    // get 으로 날리는 경우 운송장번호를 넘김
                    if (strpos($dl['dl_url'], "=")) $invoice = $od['od_invoice'];
            ?>
            <tr>
                <th scope="row">배송회사</th>
                <td><?php echo $dl['dl_company']; ?> [<a href="<?php echo $dl['dl_url'].$invoice; ?>" target="_blank">배송조회하기</a>]</td>
            </tr>
            <tr>
                <th scope="row">운송장번호</th>
                <td><?php echo $od['od_invoice']; ?></td>
            </tr>
            <tr>
                <th scope="row">배송일시</th>
                <td><?php echo $od['od_invoice_time']; ?></td>
            </tr>
            <tr>
                <th>고객센터 전화</th>
                <td><?php echo $dl['dl_tel']; ?></td>
            </tr>
            <?php
                }
                else
                {
            ?>
            <tr>
                <td class="empty_table">아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</td>
            </tr>
            <?php
                }
            }
            ?>
            </tbody>
            </table>
        </section>
    </div>

    <section id="sod_fin_tot">
        <h2>결제합계</h2>

        <ul>
            <li>
                총 구매액
                <strong><?php echo display_price($tot_amount); ?></strong>
            </li>
            <?php
            if ($od['od_dc_amount'] > 0) {
            echo '<li>';
            echo '할인액'.PHP_EOL;
            echo '<strong>'.display_price($od['od_dc_amount']).'</strong>';
            echo '</li>';
            }
            if ($misu_amount > 0) {
            echo '<li>';
            echo '미결제액'.PHP_EOL;
            echo '<strong>'.display_price($misu_amount).'</strong>';
            echo '</li>';
            }
            ?>
            <li id="alrdy">
                결제액
                <strong><?php echo $wanbul; ?></strong>
            </li>
        </ul>
    </section>

    <section id="sod_fin_cancel">
        <h2>주문취소</h2>
        <?php
        // 취소한 내역이 없다면
        if ($tot_cancel_amount == 0) {
            if ($od_count1 == $od_count2 && ($od['od_settle_case'] != '가상계좌' || $od['od_receipt_amount'] == 0)) {
        ?>
        <button type="button" onclick="document.getElementById('sod_fin_cancelfrm').style.display='block';">주문 취소하기</button>

        <div id="sod_fin_cancelfrm">
            <form method="post" action="./orderinquirycancel.php" onsubmit="return fcancel_check(this);">
            <input type="hidden" name="od_id"  value="<?php echo $od['od_id']; ?>">
            <input type="hidden" name="uq_id" value="<?php echo $od['uq_id']; ?>">
            <input type="hidden" name="token"  value="<?php echo $token; ?>">

            <label for="cancel_memo">취소사유</label>
            <input type="text" name="cancel_memo" id="cancel_memo" required class="frm_input required" size="40" maxlength="100">
            <input type="submit" value="확인" class="btn_frmline">

            </form>
        </div>
        <?php
            }
        } else {
            $misu_amount = $misu_amount - $send_cost;
        ?>
        <p>주문 취소, 반품, 품절된 내역이 있습니다.</p>
        <?php } ?>
    </section>

    <?php if ($od['od_settle_case'] == '가상계좌' && $default['de_card_test'] && $is_admin) {
    preg_match("/(\s[^\s]+\s)/", $od['od_bank_account'], $matchs);
    $deposit_no = trim($matchs[1]);
    ?>
    <fieldset>
    <legend>모의입금처리</legend>
    <p>관리자가 가상계좌 테스트를 한 경우에만 보입니다.</p>
    <form method="post" action="http://devadmin.kcp.co.kr/Modules/Noti/TEST_Vcnt_Noti_Proc.jsp" target="_blank">
    <input type="text" name="e_trade_no" value="<?php echo $od['od_tno']; ?>" size="80"><br />
    <input type="text" name="deposit_no" value="<?php echo $deposit_no; ?>" size="80"><br />
    <input type="text" name="req_name" value="<?php echo $od['od_name']; ?>" size="80"><br />
    <input type="text" name="noti_url" value="<?php echo G4_SHOP_URL; ?>/settle_kcp_common.php" size="80"><br /><br />
    <input type="submit" value="입금통보 테스트">
    </form>
    </fieldset>
    <?php } ?>

</div>
<!-- } 주문상세내역 끝 -->

<script>
var req_act = "";

$(function() {
    $(".req_button").click(function() {
        var $chk_item = $("input[name^=chk_ct_id]:checked");
        req_act = $(this).text();
        <?php if(!$dsp_request) { ?>
        alert("관리자가 처리 중인 요청내용이 있어 추가로 요청하실 수 없습니다.");
        return false;
        <?php } ?>

        if($chk_item.size() < 1) {
            alert(req_act+"할 상품을 하나 이상 선택해 주십시오");
            return false;
        }

        $("input[name=rq_content]").val("");
        $("#request_form").show();

    });

    $("#request_cancel").click(function() {
        $("#request_form").hide();
    });
});

function frequest_check(f)
{
    var rq_type;
    var $chk_item = $("input[name^=chk_ct_id]:checked");
    if($chk_item.size() < 1) {
        alert(req_act+"할 상품을 하나 이상 선택해 주십시오");
        return false;
    }

    if(!confirm("선택하신 상품을 "+req_act+"하시겠습니까?"))
        return false;

    switch(req_act) {
        case "교환요청":
            rq_type = 1;
            break;
        case "반품요청":
            rq_type = 2;
            break;
        default:
            rq_type = 0;
            break;
    }

    f.rq_type.value = rq_type;

    return true;
}

function fcancel_check(f)
{
    if(!confirm("주문을 정말 취소하시겠습니까?"))
        return false;

    var memo = f.cancel_memo.value;
    if(memo == "") {
        alert("취소사유를 입력해 주십시오.");
        return false;
    }

    return true;
}
</script>

<?php
include_once('./_tail.php');
?>