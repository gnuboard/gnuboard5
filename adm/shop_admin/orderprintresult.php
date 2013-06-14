<?php
$sub_menu = '500120';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

//print_r2($_GET); exit;

/*
function multibyte_digit($source)
{
    $search  = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $replace = array("０","１","２","３","４","５","６","７","８","９");
    return str_replace($search, $replace, (string)$source);
}
*/

function conv_telno($t)
{
    // 숫자만 있고 0으로 시작하는 전화번호
    if (!preg_match("/[^0-9]/", $t) && preg_match("/^0/", $t))  {
        if (preg_match("/^01/", $t)) {
            $t = preg_replace("/([0-9]{3})(.*)([0-9]{4})/", "\\1-\\2-\\3", $t);
        } else if (preg_match("/^02/", $t)) {
            $t = preg_replace("/([0-9]{2})(.*)([0-9]{4})/", "\\1-\\2-\\3", $t);
        } else {
            $t = preg_replace("/([0-9]{3})(.*)([0-9]{4})/", "\\1-\\2-\\3", $t);
        }
    }

    return $t;
}

// 1.04.01
// MS엑셀 CSV 데이터로 다운로드 받음
if ($csv == 'csv')
{
    $fr_date = date_conv($fr_date);
    $to_date = date_conv($to_date);


    $sql = " SELECT od_b_zip1, od_b_zip2, od_b_addr1, od_b_addr2, od_b_name, od_b_tel, od_b_hp, b.it_name, ct_qty, b.it_id, a.od_id, od_memo, od_invoice, b.ct_option, b.ct_send_cost
               FROM {$g4['shop_order_table']} a, {$g4['shop_cart_table']} b
              where a.uq_id = b.uq_id ";
    if ($case == 1) // 출력기간
        $sql .= " and a.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    else // 주문번호구간
        $sql .= " and a.od_id between '$fr_od_id' and '$to_od_id' ";
    if ($ct_status)
        $sql .= " and b.ct_status = '$ct_status' ";
    $sql .="  order by od_time asc ";
    $result = sql_query($sql);
    $cnt = @mysql_num_rows($result);
    if (!$cnt)
        alert("출력할 내역이 없습니다.");

    //header('Content-Type: text/x-csv');
    header("Content-charset=utf-8");
    header('Content-Type: doesn/matter');
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: attachment; filename="' . date("ymd", time()) . '.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    //echo "우편번호,주소,이름,전화1,전화2,상품명,수량,비고,전하실말씀\n";
    echo "우편번호,주소,이름,전화1,전화2,상품명,수량,선택사항,배송비,상품코드,주문번호,운송장번호,전하실말씀\n";
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $ct_send_cost = ($row['ct_send_cost'] ? '착불' : '선불');

        echo '"'.$row['od_b_zip1'].'-'.$row['od_b_zip2'].'"'.',';
        echo '"'.$row['od_b_addr1'].' '.$row['od_b_addr2'].'"'.',';
        echo '"'.$row['od_b_name'].'"'.',';
        //echo '"'.multibyte_digit((string)$row[od_b_tel]).'"'.',';
        //echo '"'.multibyte_digit((string)$row[od_b_hp]).'"'.',';
        echo '"'.conv_telno($row['od_b_tel']) . '"'.',';
        echo '"'.conv_telno($row['od_b_hp']) . '"'.',';
        echo '"'.preg_replace("/\"/", "&#034;", $row['it_name']) . '"'.',';
        echo '"'.$row['ct_qty'].'"'.',';
        echo '"'.$row['ct_option'].'"'.',';
        echo '"'.$ct_send_cost.'"'.',';
        echo '"\''.$row['it_id'].'\'"'.',';
        echo '"\''.$row['od_id'].'\'"'.',';
        echo '"'.$row['od_invoice'].'"'.',';
        //echo '"'.preg_replace("/\"/", "&#034;", preg_replace("/\n/", "", $row[od_memo])).'"';
        echo '"'.preg_replace("/\"/", "&#034;", $row['od_memo']).'"';
        echo "\n";
    }
    if ($i == 0)
        echo '자료가 없습니다.'.PHP_EOL;

    exit;
}

// MS엑셀 XLS 데이터로 다운로드 받음
if ($csv == 'xls')
{
    $fr_date = date_conv($fr_date);
    $to_date = date_conv($to_date);


    $sql = " SELECT od_b_zip1, od_b_zip2, od_b_addr1, od_b_addr2, od_b_name, od_b_tel, od_b_hp, b.it_name, ct_qty, b.it_id, a.od_id, od_memo, od_invoice, b.ct_option, b.ct_send_cost
               FROM {$g4['shop_order_table']} a, {$g4['shop_cart_table']} b
              where a.uq_id = b.uq_id ";
    if ($case == 1) // 출력기간
        $sql .= " and a.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    else // 주문번호구간
        $sql .= " and a.od_id between '$fr_od_id' and '$to_od_id' ";
    if ($ct_status)
        $sql .= " and b.ct_status = '$ct_status' ";
    $sql .="  order by od_time asc ";
    $result = sql_query($sql);
    $cnt = @mysql_num_rows($result);
    if (!$cnt)
        alert("출력할 내역이 없습니다.");

    header("Content-charset=utf-8");
    header('Content-Type: application/vnd.ms-excel');
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: attachment; filename="' . date("ymd", time()) . '.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo '<html>';
    echo '<head>';
    echo '<title>xls</title>';
    echo '<style>.mso_txt {mso-number-format:"\\@"}</style>';
    echo '</head>';
    echo '<body>';
    echo '<table class="frm_tbl">';
    echo '<tr>';
    echo '<td>우편번호</td>';
    echo '<td>주소</td>';
    echo '<td>이름</td>';
    echo '<td>전화1</td>';
    echo '<td>전화2</td>';
    echo '<td>상품명</td>';
    echo '<td>수량</td>';
    echo '<td>선택사항</td>';
    echo '<td>배송비</td>';
    echo '<td>상품코드</td>';
    echo '<td>주문번호</td>';
    echo '<td>운송장번호</td>';
    echo '<td>전하실말씀</td>';
    echo '</tr>';
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        $it_name = stripslashes($row['it_name']) . "<br />";
        $ct_send_cost = ($row['ct_send_cost'] ? '착불' : '선불');
        echo '<tr>';
        echo '<td>'.$row['od_b_zip1'].'-'.$row['od_b_zip2'].'</td>';
        echo '<td>'.$row['od_b_addr1'].' '.$row['od_b_addr2'].'</td>';
        echo '<td>'.$row['od_b_name'].'</td>';
        echo '<td class="mso_txt">'.$row['od_b_tel'].'</td>';
        echo '<td class="mso_txt">'.$row['od_b_hp'].'</td>';
        echo '<td>'.$it_name.'</td>';
        echo '<td>'.$row['ct_qty'].'</td>';
        echo '<td>'.$row['ct_option'].'</td>';
        echo '<td>'.$ct_send_cost.'</td>';
        echo '<td class="mso_txt">'.$row['it_id'].'</td>';
        echo '<td class="mso_txt">'. urlencode($row['od_id']).'</td>';
        echo '<td class="mso_txt">'.$row['od_invoice'].'</td>';
        echo '<td>'.$row['od_memo'].'</td>';
        echo '</tr>';
    }
    if ($i == 0)
        echo '<tr><td colspan="11">자료가 없습니다.</td></tr>';
    echo '</table>';
    echo '</body>';
    echo '</html>';

    exit;
}

function get_order($uq_id)
{
    global $g4;

    $sql = " select * from {$g4['shop_order_table']} where uq_id = '$uq_id' ";
    return sql_fetch($sql);
}

$g4['title'] = "주문내역";
include_once(G4_PATH.'/head.sub.php');

if ($case == 1)
{
    $fr_date = date_conv($fr_date);
    $to_date = date_conv($to_date);
    $sql = " SELECT DISTINCT a.uq_id FROM {$g4['shop_order_table']} a, {$g4['shop_cart_table']} b
              where a.uq_id = b.uq_id
                and a.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}
else
{
    $sql = " SELECT DISTINCT a.uq_id FROM {$g4['shop_order_table']} a, {$g4['shop_cart_table']} b
              where a.uq_id = b.uq_id
                and a.od_id between '$fr_od_id' and '$to_od_id' ";
}
if ($ct_status)
    $sql .= " and b.ct_status = '$ct_status' ";
$sql .= " order by a.od_id ";
$result = sql_query($sql);
if (mysql_num_rows($result) == 0)
{
    echo "<script>alert('출력할 내역이 없습니다.'); window.close();</script>";
    exit;
}
?>

<div id="sodr_print_pop" class="cbox">
    <h1>
        <?php
        if ($case == 1)
            echo $fr_date.' 부터 '.$to_date.' 까지 '.$ct_status.' 내역';
        else
            echo $fr_od_id.' 부터 '.$to_od_id.' 까지 '.$ct_status.' 내역';
        ?>
    </h1>

    <?php
    $mod = 10;
    $tot_total_amount = 0;
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $sql1 = " select * from {$g4['shop_order_table']} where uq_id = '{$row['uq_id']}' ";
        $row1 = sql_fetch($sql1);

        // 1.03.02
        $row1['od_addr'] = '('.$row1['od_zip1'].'-'.$row1['od_zip2'].') '.$row1['od_addr1'].' '.$row1['od_addr2'];
        $row1['od_b_addr'] = '('.$row1['od_b_zip1'].'-'.$row1['od_b_zip2'].') '.$row1['od_b_addr1'].' '.$row1['od_b_addr2'];

        $row1['od_addr'] = ($row1['od_addr']) ? $row1['od_addr'] : '입력안함';
        $row1['od_tel'] = ($row1['od_tel']) ? $row1['od_tel'] : '입력안함';
        $row1['od_hp']  = ($row1['od_hp']) ? $row1['od_hp'] : '입력안함';
        $row1['od_b_tel'] = ($row1['od_b_tel']) ? $row1['od_b_tel'] : '입력안함';
        $row1['od_b_hp']  = ($row1['od_b_hp']) ? $row1['od_b_hp'] : '입력안함';

        // 보내는 사람과 받는 사람이 완전 일치하면 간단하게 출력
        // 보내는 사람과 받는 사람이 부분 일치하더라도 원래 내용을 모두 출력
        // 지운아빠 2013-04-18
        if ($row1['od_name'] == $row1['od_b_name'] && $row1['od_addr'] == $row1['od_b_addr'] && $row1['od_tel'] == $row1['od_b_tel'] &&  $row1['od_hp'] == $row1['od_b_hp'] && $row1['od_hp'] != "&nbsp;") $samesamesame = 1;
        else $samesamesame = '';

        $od_memo = ($row1['od_memo']) ? stripslashes($row1['od_memo']) : '';
        $od_shop_memo = ($row1['od_shop_memo']) ? stripslashes($row1['od_shop_memo']) : '';
    ?>
    <!-- 반복시작 - 지운아빠 2013-04-18 -->
    <div class="sodr_print_pop_list">
        <h2>주문번호 <?php echo $row1['od_id']; ?></h2>
        <h3>보내는 사람 : <?php echo $row1['od_name']; ?></h3>
        <dl>
            <dt>주소</dt>
            <dd><?php echo $row1['od_addr']; ?></dd>
            <dt>휴대폰</dt>
            <dd><?php echo $row1['od_hp']; ?></dd>
            <dt>전화번호</dt>
            <dd><?php echo $row1['od_tel']; ?></dd>
        </dl>
        <?php if ($samesamesame) { ?>
        <p class="sodr_print_pop_same">보내는 사람과 받는 사람이 동일합니다.</p>
        <?php } else { ?>
        <h3>받는 사람 : <?php echo $row1['od_b_name']; ?></h3>
        <dl>
            <dt>주소</dt>
            <dd><?php echo $row1['od_b_addr']; ?></dd>
            <dt>휴대폰</dt>
            <dd><?php echo $row1['od_b_hp']; ?></dd>
            <dt>전화번호</dt>
            <dd><?php echo $row1['od_b_tel']; ?></dd>
        </dl>
        <?php } ?>
        <h3>주문목록</h3>
        <table>
        <thead>
        <tr>
            <th scope="col">상품명(선택사항)</th>
            <th scope="col">판매가</th>
            <th scope="col">수량</th>
            <th scope="col">소계</th>
            <th scope="col">배송비</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $sql2 = " select *
                    from {$g4['shop_cart_table']}
                   where uq_id = '{$row['uq_id']}' ";
        if ($ct_status)
            $sql2 .= " and ct_status = '$ct_status' ";
        $sql2 .= "  order by ct_id ";

        $res2 = sql_query($sql2);
        $cnt = $sub_tot_qty = $sub_tot_amount = 0;
        while ($row2 = sql_fetch_array($res2))
        {
            if($row2['io_type'])
                $row2_tot_amount = $row2['io_price'] * $row2['ct_qty'];
            else
                $row2_tot_amount = ($row2['ct_price'] + $row2['io_price']) * $row2['ct_qty'];
            $sub_tot_qty += $row2['ct_qty'];
            $sub_tot_amount += $row2_tot_amount;

            $it_name = stripslashes($row2['it_name']);
            $it_name = "$it_name ({$row2['ct_option']})";
            $ct_send_cost = ($row2['ct_send_cost'] ? '착불' : '선불');

            $fontqty1 = $fontqty2 = "";
            if ($row2['ct_qty'] >= 2)
            {
                $fontqty1 = "<strong>";
                $fontqty2 = "</strong>";
            }

        ?>
        <tr>
            <td><?php echo $it_name; ?></td>
            <td class="td_bignum"><?php echo number_format($row2['ct_price']); ?></td>
            <td class="td_smallnum"><?php echo $fontqty1; ?><?php echo number_format($row2['ct_qty']); ?><?php echo $fontqty2; ?></td>
            <td class="td_bignum"><?php echo number_format($row2_tot_amount); ?></td>
            <td><?php echo $ct_send_cost; ?></td>
        </tr>
        <?php $cnt++; } ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="row" colspan="2">합계</th>
            <td><?php echo number_format($sub_tot_qty); ?></td>
            <td><?php echo number_format($sub_tot_amount); ?></td>
            <td></td>
        </tr>
        </tfoot>
        </table>
        <?php
        $tot_tot_qty    += $sub_tot_qty;
        $tot_tot_amount += $sub_tot_amount;

        if ($od_memo) $od_memo = "<p><strong>비고</strong> $od_memo</p>";
        if ($od_shop_memo) $od_shop_memo = "<p><strong>상점메모</strong> $od_shop_memo</p>";

        echo "
                $od_memo
                $od_shop_memo
        ";
       ?>
    </div>
    <!-- 반복 끝 -->
    <?php } ?>

    <div id="sodr_print_pop_total">
        <span>
            전체
            <strong><?php echo number_format($tot_tot_qty); ?></strong>개
            <strong><?php echo number_format($tot_tot_amount); ?></strong>원
        </span>
        &lt;출력 끝&gt;
    </div>

</div>


</body>
</html>