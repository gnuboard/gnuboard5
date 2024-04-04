<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<script>
    // 현금영수증 MAIN FUNC
    function  jsf__pay_cash( form )
    {
        jsf__show_progress(true);

        if ( jsf__chk_cash( form ) == false )
        {
            jsf__show_progress(false);
            return;
        }

        form.submit();
    }

    // 진행 바
    function  jsf__show_progress( show )
    {
        if ( show == true )
        {
            window.show_pay_btn.style.display  = "none";
            window.show_progress.style.display = "inline";
        }
        else
        {
            window.show_pay_btn.style.display  = "inline";
            window.show_progress.style.display = "none";
        }
    }

    // 포맷 체크
    function  jsf__chk_cash( form )
    {
        if (  form.tr_code[0].checked )
        {
            if ( form.id_info.value.length != 10 &&
                 form.id_info.value.length != 11 &&
                 form.id_info.value.length != 13 )
            {
                alert("주민번호 또는 휴대폰번호를 정확히 입력해 주시기 바랍니다.");
                form.id_info.select();
                form.id_info.focus();
                return false;
            }
        }
        else if (  form.tr_code[1].checked )
        {
            if ( form.id_info.value.length != 10 )
            {
                alert("사업자번호를 정확히 입력해 주시기 바랍니다.");
                form.id_info.select();
                form.id_info.focus();
                return false;
            }
        }
        return true;
    }

    function  jsf__chk_tr_code( form )
    {
        var span_tr_code_0 = document.getElementById( "span_tr_code_0" );
        var span_tr_code_1 = document.getElementById( "span_tr_code_1" );

        if ( form.tr_code[0].checked )
        {
            span_tr_code_0.style.display = "inline";
            span_tr_code_1.style.display = "none";
        }
        else if (form.tr_code[1].checked )
        {
            span_tr_code_0.style.display = "none";
            span_tr_code_1.style.display = "inline";
        }
    }

</script>

<div id="scash" class="new_win">
    <h1 id="win_title"><?php echo $g5['title']; ?></h1>

    <section>
        <h2>주문정보</h2>

        <div class="tbl_head01 tbl_wrap">
            <table>
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">주문 번호</th>
                <td><?php echo $od_id; ?></td>
            </tr>
            <tr>
                <th scope="row">상품 정보</th>
                <td><?php echo $goods_name; ?></td>
            </tr>
            <tr>
                <th scope="row">주문자 이름</th>
                <td><?php echo $od_name; ?></td>
            </tr>
            <tr>
                <th scope="row">주문자 E-Mail</th>
                <td><?php echo $od_email; ?></td>
            </tr>
            <tr>
                <th scope="row">주문자 전화번호</th>
                <td><?php echo $od_tel; ?></td>
            </tr>
            </tbody>
            </table>
        </div>
    </section>

    <section>
        <h2>현금영수증 발급 정보</h2>

        <form method="post" action="<?php echo G5_SHOP_URL; ?>/nicepay/taxsave_result.php">
        <input type="hidden" name="tx"       value="<?php echo $tx; ?>">
        <input type="hidden" name="od_id"    value="<?php echo $od_id; ?>">
        <div class="tbl_head01 tbl_wrap">
            <table>
            <colgroup>
                <col class="grid_3">
                <col>
            </colgroup>
            <tbody>
            <tr>
                <th scope="row">원 거래 시각</th>
                <td><?php echo $trad_time; ?></td>
            </tr>
            <tr>
                <th scope="row">발행 용도</th>
                <td>
                    <input type="radio" name="tr_code" value="1" id="tr_code1" onClick="jsf__chk_tr_code( this.form )" checked>
                    <label for="tr_code1">개인 소득공제용</label>
                    <input type="radio" name="tr_code" value="2" id="tr_code2" onClick="jsf__chk_tr_code( this.form )">
                    <label for="tr_code2">사업자 지출증빙용</label>
                </td>
            </tr>
            <tr>
                <th scope="row">
                    <label for="id_info">
                        <span id="span_tr_code_0" style="display:inline">주민(휴대폰)번호</span>
                        <span id="span_tr_code_1" style="display:none">사업자번호</span>
                    </label>
                </th>
                <td>
                    <input type="text" name="id_info" id="id_info" class="frm_input" size="16" maxlength="13"> ("-" 생략)
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="buyeremail">이메일</label></th>
                <td><input type="text" name="buyeremail" id="buyeremail" value="<?php echo $od_email; ?>" required class="required frm_input" size="30"></td>
            </tr>
            <tr>
                <th scope="row"><label for="buyertel">휴대폰</label></th>
                <td><input type="text" name="buyertel" id="buyertel" value="" required class="required frm_input" size="20"></td>
            </tr>
            <tr>
                <th scope="row">거래금액 총합</th>
                <td><?php echo number_format($amt_tot); ?>원</td>
            </tr>
            <tr>
                <th scope="row">공급가액</th>
                <td><?php echo number_format($amt_sup); ?>원<!-- ((거래금액 총합 * 10) / 11) --></td>
            </tr>
            <tr>
                <th scope="row">부가가치세</th>
                <td><?php echo number_format($amt_tax); ?>원<!-- 거래금액 총합 - 공급가액 - 봉사료 --></td>
            </tr>
            <?php if ($amt_free) { ?>
            <tr>
                <th scope="row">비과세공급가액</th>
                <td><?php echo number_format($amt_free); ?>원</td>
            </tr>
            <?php } ?>
            <tr>
                <th scope="row">봉사료</th>
                <td><?php echo number_format($amt_svc); ?>원</td>
            </tr>
            </tbody>
            </table>
        </div>

        <div id="scash_apply">
            <span id="show_pay_btn">
                <button type="button" onclick="jsf__pay_cash( this.form )">등록요청</button>
            </span>
            <span id="show_progress" style="display:none">
                <b>등록 진행중입니다. 잠시만 기다려주십시오</b>
            </span>
        </div>

        </form>
    </section>

</div>