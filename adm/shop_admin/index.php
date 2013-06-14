<?php
$sub_menu = '400000';
include_once('./_common.php');

$max_limit = 7; // 몇행 출력할 것인지?

$g4['title'] = ' 쇼핑몰관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sidx_rdy">입금완료미배송내역</a></li>
<li><a href="#anc_sidx_wait">미입금주문내역</a></li>
<li><a href="#anc_sidx_ps">사용후기</a></li>
<li><a href="#anc_sidx_qna">상품문의</a></li>
</ul>';
?>

<section id="anc_sidx_rdy" class="cbox">
    <h2>입금완료 미배송내역</h2>
    <?php echo $pg_anchor; ?>

    <table>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">주문자</th>
        <th scope="col">입금액</th>
        <th scope="col">결제방법</th>
        <th scope="col">수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // 미수금이 없고 운송장번호가 없는 자료를 구함
    $sql = " select a.od_id,
                    a.*, "._MISU_QUERY_."
               from {$g4['shop_order_table']} a
               left join {$g4['shop_cart_table']} b on (b.uq_id=a.uq_id)
              group by a.od_id
              /*having misu <= 0 and a.od_invoice = '' and ordercancel = 0*/
              /*having orderamount - receiptamount = 0 and a.od_invoice = ''*/
              having misu <= 0 and a.od_invoice = ''
              order by a.od_id desc
              limit $max_limit ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $sql1 = " select * from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row1 = sql_fetch($sql1);

        $name = get_sideview($row['mb_id'], get_text($row['od_name']), $row1['mb_email'], $row1['mb_homepage']);

        $settle_method = "";
        if ($row['od_settle_case'])
        {
            $settle_method = $row['od_settle_case'];
        }
        else
        {
            $settle_method .= '미입력';
            if ($row['od_receipt_point']) $settle_method .= '포인트';
        }
    ?>
    <tr>
        <td class="td_odrnum2"><?php echo $row['od_id']; ?></td>
        <td class="td_name"><?php echo $name; ?></td>
        <td class="td_bignum"><?php echo display_price($row['receiptamount']); ?></td>
        <td class="td_payby"><?php echo $settle_method; ?></td>
        <td class="td_smallmng"><a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>">수정</a></td>
    </tr>
    <?php
    }
    if ($i == 0) echo '<tr><td colspan="5" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_ft">
        <a href="./deliverylist.php?sort1=od_invoice&amp;sort2=asc&amp;chk_misu=1">입금완료 미배송내역 더보기</a>
    </div>
</section>

<section id="anc_sidx_wait" class="cbox">
    <h2>미입금 주문내역</h2>
    <?php echo $pg_anchor; ?>

    <table>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">주문자</th>
        <th scope="col">주문액</th>
        <th scope="col">결제방법</th>
        <th scope="col">수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    // 미수금이 있고 송장번호가 없는 자료를 구함
    $sql = " select a.od_id,
                    a.*, "._MISU_QUERY_."
               from {$g4['shop_order_table']} a
               left join {$g4['shop_cart_table']} b on (b.uq_id=a.uq_id)
              group by a.od_id
              /* having receiptamount <= 0 */
              having misu > 0
              order by a.od_id desc
              limit $max_limit ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $sql1 = " select * from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row1 = sql_fetch($sql1);

        $name = get_sideview($row['mb_id'], get_text($row['od_name']), $row1['mb_email'], $row1['mb_homepage']);

        $settle_method = "";
        if ($row['od_settle_case'])
        {
            $settle_method = $row['od_settle_case'];
        }
        else
        {
            $settle_method .= '미입력';
            if ($row['od_temp_point']) $settle_method .= '포인트';
        }
    ?>
    <tr>
        <td class="td_odrnum2"><a href="./orderstatuslist.php?sort1=od_id&amp;sel_field=od_id&amp;search=<?php echo $row['od_id']; ?>"><?php echo $row['od_id']; ?></a></td>
        <td class="td_name"><?php echo $name; ?></td>
        <td class="td_bignum"><?php echo display_price($row['orderamount']); ?></td>
        <td class="td_payby"><?php echo $settle_method; ?></td>
        <td class="td_smallmng"><a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>">수정</a></td>
    </tr>
    <?php
    }
    if ($i == 0) echo '<tr><td colspan="5">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_ft">
        <a href="./orderlist.php?sort1=receiptamount&amp;sort2=asc">미입금 주문내역 더보기</a>
    </div>
</section>

<section id="anc_sidx_ps" class="cbox">
    <h2>사용후기</h2>
    <?php echo $pg_anchor; ?>

    <table>
    <thead>
    <tr>
        <th scope="col">회원명</th>
        <th scope="col">제목</th>
        <th scope="col">수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
<<<<<<< HEAD
    $sql = " select * from {$g4[shop_item_use_table]}
=======
    $sql = " select * from {$g4['shop_item_ps_table']}
>>>>>>> master
              where is_confirm = 0
              order by is_id desc
              limit $max_limit ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $sql1 = " select * from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row1 = sql_fetch($sql1);

        $name = get_sideview($row['mb_id'], get_text($row['is_name']), $row1['mb_email'], $row1['mb_homepage']);
    ?>
    <tr>
        <td class="td_name"><?php echo $name; ?></td>
        <td><?php echo cut_str($row['is_subject'],40); ?></td>
<<<<<<< HEAD
        <td class="td_smallmng"><a href="./itemuseform.php?w=u&amp;is_id=<?php echo $row['is_id']; ?>"><img src="./img/icon_mod.jpg" alt="<?php cut_str($row['is_subject'],40); ?> 수정"></a></td>
=======
        <td class="td_smallmng"><a href="./itempsform.php?w=u&amp;is_id=<?php echo $row['is_id']; ?>">수정</a></td>
>>>>>>> master
    </tr>
    <?php
    }
    if ($i == 0) echo '<tr><td colspan="3" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_ft">
        <a href="./itemuselist.php?sort1=is_confirm&amp;sort2=asc">사용후기 더보기</a>
    </div>
</section>

<section id="anc_sidx_qna" class="cbox">
    <h2>상품문의</h2>
    <?php echo $pg_anchor; ?>

    <table>
    <thead>
    <tr>
        <th scope="col">회원명</th>
        <th scope="col">제목</th>
        <th scope="col">수정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select * from {$g4['shop_item_qa_table']}
              where iq_answer = ''
              order by iq_id desc
              limit $max_limit ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $sql1 = " select * from {$g4['member_table']} where mb_id = '{$row['mb_id']}' ";
        $row1 = sql_fetch($sql1);

        $name = get_sideview($row['mb_id'], get_text($row['iq_name']), $row1['mb_email'], $row1['mb_homepage']);
    ?>
    <tr>
        <td class="td_name"><?php echo $name; ?></td>
        <td><?php echo cut_str($row['iq_subject'],40); ?></td>
        <td class="td_mng"><a href="./itemqaform.php?w=u&amp;iq_id=<?php echo $row['iq_id']; ?>">수정</a></td>
    </tr>
    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="3" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_ft">
        <a href="./itemqalist.php?sort1=iq_answer&amp;sort2=asc">상품문의 더보기</a>
    </div>
</section>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
