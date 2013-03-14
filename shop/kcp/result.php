<?
    /* ============================================================================== */
    /* =   PAGE : 지불 요청 및 결과 처리 PAGE                                        = */
    /* = -------------------------------------------------------------------------- = */
    /* =   Copyright (c)  2005   KCP Inc.   All Rights Reserverd.                   = */
    /* ============================================================================== */
?>
<?
    /* ============================================================================== */
    /* =   지불 결과                                                                = */
    /* = -------------------------------------------------------------------------- = */
    $req_tx           = $_POST[ "req_tx"         ];      // 요청 구분(승인/취소)
    $use_pay_method   = $_POST[ "use_pay_method" ];      // 사용 결제 수단
    $bSucc            = $_POST[ "bSucc"          ];      // 업체 DB 정상처리 완료 여부
    /* = -------------------------------------------------------------------------- = */
    $res_cd           = $_POST[ "res_cd"         ];      // 결과 코드
    $res_msg          = $_POST[ "res_msg"        ];      // 결과 메시지
    /* = -------------------------------------------------------------------------- = */
    $ordr_idxx        = $_POST[ "ordr_idxx"      ];      // 주문번호
    $tno              = $_POST[ "tno"            ];      // KCP 거래번호
    $good_mny         = $_POST[ "good_mny"       ];      // 결제 금액
    $good_name        = $_POST[ "good_name"      ];      // 상품명
    $buyr_name        = $_POST[ "buyr_name"      ];      // 구매자명
    $buyr_tel1        = $_POST[ "buyr_tel1"      ];      // 구매자 전화번호
    $buyr_tel2        = $_POST[ "buyr_tel2"      ];      // 구매자 휴대폰번호
    $buyr_mail        = $_POST[ "buyr_mail"      ];      // 구매자 E-Mail
    /* = -------------------------------------------------------------------------- = */
    // 에스크로
    $buyr_zipx        = $_POST[ "buyr_zipx"      ];      // 주문자 우편번호
    $buyr_add1        = $_POST[ "buyr_add1"      ];      // 주문자 주소
    $buyr_add2        = $_POST[ "buyr_add2"      ];      // 주문자 상세주소
    $rcvr_name        = $_POST[ "rcvr_name"      ];      // 수취인명
    $rcvr_tel1        = $_POST[ "rcvr_tel1"      ];      // 수취인 전화번호
    $rcvr_tel2        = $_POST[ "rcvr_tel2"      ];      // 수취인 휴대폰번호
    $rcvr_mail        = $_POST[ "rcvr_mail"      ];      // 수취인 E-Mail
    $rcvr_zipx        = $_POST[ "rcvr_zipx"      ];      // 수취인 우편번호
    $rcvr_add1        = $_POST[ "rcvr_add1"      ];      // 수취인 주소
    $rcvr_add2        = $_POST[ "rcvr_add2"      ];      // 수취인 상세주소
    $rcvr_date        = $_POST[ "rcvr_date"      ];      // 배송 희망일
    $rqst_msgx        = $_POST[ "rqst_msgx"      ];      // 배송 요청 사항
    /* = -------------------------------------------------------------------------- = */
    // 신용카드
    $card_cd          = $_POST[ "card_cd"        ];      // 카드 코드
    $card_name        = $_POST[ "card_name"      ];      // 카드명
    $app_time         = $_POST[ "app_time"       ];      // 승인시간 (공통)
    $app_no           = $_POST[ "app_no"         ];      // 승인번호
    $quota            = $_POST[ "quota"          ];      // 할부개월
    /* = -------------------------------------------------------------------------- = */
    // 계좌이체
    $bank_name        = $_POST[ "bank_name"      ];      // 은행명
    /* = -------------------------------------------------------------------------- = */
    // 가상계좌
    $bankname         = $_POST[ "bankname"       ];      // 입금 은행
    $depositor        = $_POST[ "depositor"      ];      // 입금계좌 예금주
    $account          = $_POST[ "account"        ];      // 입금계좌 번호
    /* = -------------------------------------------------------------------------- = */
    $req_tx_name = "";

    if( $req_tx == "pay" )
    {
        $req_tx_name = "지불";
    }
    else if( $req_tx == "mod" )
    {
        $req_tx_name = "매입/취소";
    }
?>
    <html>
    <head>
    <link href="css/sample.css" rel="stylesheet" type="text/css">
    <script language="javascript">
        <!-- 신용카드 영수증 연동 스크립트 -->
        function receiptView(tno)
        {
            receiptWin = "http://admin.kcp.co.kr/Modules/Sale/Card/ADSA_CARD_BILL_Receipt.jsp?c_trade_no=" + tno
            window.open(receiptWin , "" , "width=420, height=670")
        } 
    </script>
    </head>
    <body>
    <center>
    <table border='0' cellpadding='0' cellspacing='1' width='500' align='center'>
        <tr>
            <td align="left" height="25"><img src="./img/KcpLogo.jpg" border="0" width="65" height="50"></td>
            <td align='right' class="txt_main">KCP Online Payment System [AX_HUB PHP Version]</td>
        </tr>
        <tr>
            <td bgcolor="CFCFCF" height='3' colspan='2'></td>
        </tr>
        <tr>
            <td colspan="2">
                <br>
                <table width="90%" align="center">
                    <tr>
                        <td bgcolor="CFCFCF" height='2'></td>
                    </tr>
                    <tr>
                        <td align="center">결과 페이지(<?=$req_tx_name?>)</td>
                    </tr>
                    <tr>
                        <td bgcolor="CFCFCF" height='2'></td>
                    </tr>
                </table>
<?
    if ($req_tx == "pay")                           // 거래 구분 : 승인
    {
        if ($bSucc != "false")                      // 업체 DB 처리 정상
        {
            if ($res_cd == "0000")                  // 정상 승인
            {
?>
                <table width="85%" align="center" border='0' cellpadding='0' cellspacing='1'>
                    <tr>
                        <td>결과코드</td>
                        <td><?=$res_cd?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결과 메세지</td>
                        <td><?=$res_msg?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문번호</td>
                        <td><?=$ordr_idxx?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>KCP 거래번호</td>
                        <td><?=$tno?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제금액</td>
                        <td><?=$good_mny?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>상품명</td>
                        <td><?=$good_name?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문자명</td>
                        <td><?=$buyr_name?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문자 전화번호</td>
                        <td><?=$buyr_tel1?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문자 휴대폰번호</td>
                        <td><?=$buyr_tel2?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>E-mail</td>
                        <td><?=$buyr_mail?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문자 우편번호</td>
                        <td><?=$buyr_zipx?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문자 주소</td>
                        <td><?=$buyr_add1?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>주문자 상세주소</td>
                        <td><?=$buyr_add2?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인명</td>
                        <td><?=$rcvr_name?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인 전화번호</td>
                        <td><?=$rcvr_tel1?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인 휴대폰번호</td>
                        <td><?=$rcvr_tel2?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인 E-Mail</td>
                        <td><?=$rcvr_mail?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인 우편번호</td>
                        <td><?=$rcvr_zipx?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인 주소</td>
                        <td><?=$rcvr_add1?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>수취인 상세주소</td>
                        <td><?=$rcvr_add2?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>배송 희망일</td>
                        <td><?=$rcvr_date?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>배송 요청 사항</td>
                        <td><?=$rqst_msgx?></td>
                    </tr>                    
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>                    
<?

                if ($use_pay_method == "100000000000")       // 신용카드
                {

?>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제수단 </td>
                        <td>신용카드</td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제카드</td>
                        <td><?=$card_cd?> / <?=$card_name?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>승인시간</td>
                        <td><?=$app_time?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>승인번호</td>
                        <td><?=$app_no?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>할부개월</td>
                        <td><?=$quota?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>신용카드 영수증</td>
                        <td><input type="button" name="receiptView" value="영수증 확인" class="box" onClick="javascript:receiptView('<%=tno%>')"></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td colspan="2">※ 영수증 확인은 실제결제의 경우에만 가능합니다.</td>
                    </tr>
                </table>
<?

                }
                else if ($use_pay_method == "010000000000")       // 계좌이체
                {

?>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제수단 </td>
                        <td>계좌이체</td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>이체은행</td>
                        <td><?=$bank_name?></td>
                    </tr>
                </table>
<?

                }
                else if ($use_pay_method == "001000000000")       // 가상계좌
                {

?>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제수단 </td>
                        <td>가상계좌</td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>입금 은행</td>
                        <td><?=$bankname?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>입금계좌 예금주</td>
                        <td><?=$depositor?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>입금계좌 번호</td>
                        <td><?=$account?></td>
                    </tr>
                </table>
<?

                }
                else if ($use_pay_method == "000010000000")       // 휴대폰
                {

?>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제수단 </td>
                        <td>휴대폰</td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>승인시간</td>
                        <td><?=$app_time?></td>
                    </tr>
                </table>
<?

                }
                else if ($use_pay_method == "000100000000")       // 카드사 포인트
                {

?>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제수단 </td>
                        <td>카드사 포인트</td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>승인시간</td>
                        <td><?=$app_time?></td>
                    </tr>
                </table>
<?

                }
                else if ($use_pay_method == "000000000010")       // ARS
                {

?>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결제수단 </td>
                        <td>ARS</td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>승인시간</td>
                        <td><?=$app_time?></td>
                    </tr>
                </table>
<?

                }

            }
            else                                       // 승인 실패
            {

?>
                <table width="85%" align="center" border='0' cellpadding='0' cellspacing='1'>
                    <tr>
                        <td>결과코드</td>
                        <td><?=$res_cd?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결과 메세지</td>
                        <td><?=$res_msg?></td>
                    </tr>
                </table>
<?

            }

        }
        else                                           // 업체 DB 처리 실패
        {

?>
                <table width="85%" align="center" border='0' cellpadding='0' cellspacing='1'>
                    <tr>
                        <td nowrap>취소 결과코드</td>
                        <td><?=$res_cd?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td nowrap>취소 결과 메세지</td>
                        <td><?=$res_msg?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td nowrap>상세메세지</td>
                        <td>
<?

            if ($res_cd == "0000")
            {
                echo("결제는 정상적으로 이루어졌지만 쇼핑몰에서 결제 결과를 처리하는 중 오류가 발생하여 시스템에서 자동으로 취소 요청을 하였습니다. <br> 쇼핑몰로 전화하여 확인하시기 바랍니다.");
            }
            else
            {
                echo("결제는 정상적으로 이루어졌지만 쇼핑몰에서 결제 결과를 처리하는 중 오류가 발생하여 시스템에서 자동으로 취소 요청을 하였으나, <br> <b>취소가 실패 되었습니다.</b><br> 쇼핑몰로 전화하여 확인하시기 바랍니다.");
            }

?>
                        </td>
                    </tr>
                </table>
<?

        }

    }
    else if ($req_tx == "mod")                     // 거래 구분 : 취소/매입
    {

?>

                <table width="85%" align="center" border='0' cellpadding='0' cellspacing='1'>
                    <tr>
                        <td>결과코드</td>
                        <td><?=$res_cd?></td>
                    </tr>
                    <tr><td colspan="2"><IMG SRC="./img/dot_line.gif" width="100%"></td></tr>
                    <tr>
                        <td>결과 메세지</td>
                        <td><?=$res_msg?></td>
                    </tr>
                </table>
<?

    }

?>
                <table width="90%" align="center">
                    <tr>
                        <td bgcolor="CFCFCF" height='2'></td>
                    </tr>
                    <tr>
                        <td height='2'>&nbsp;</td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td bgcolor="CFCFCF" height='3' colspan='2'></td>
        </tr>
    </table>
    </center>
    </body>
    </html>
