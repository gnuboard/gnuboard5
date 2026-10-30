<?php
if (!defined('_GNUBOARD_') || !defined('G5_IS_ADMIN')) exit;
require_once(G5_LIB_PATH.'/shop_pg_approval.lib.php');
$approval_rows = shop_pg_approval_admin_rows(isset($pg_approval_oid) ? $pg_approval_oid : $od_id);
?>
<section>
    <details class="pg_approval_details">
    <summary>PG 승인 카드정보 <span class="pg_approval_expand">(펼치기)</span><span class="pg_approval_collapse">(접기)</span></summary>
    <div class="local_desc01 local_desc"><p>승인 당시 PG가 반환한 값입니다. 미수신은 일시불 또는 일반할부를 의미하지 않습니다. 무이자 표시만으로 가맹점 부담 여부를 단정하지 말고 PG별 응답 항목과 계약 조건을 확인하세요. 취소 상태는 주문 결제 내역에서 확인하세요.</p></div>
    <div class="tbl_head01 tbl_wrap"><table>
        <caption>PG 승인 카드정보</caption>
        <thead><tr><th scope="col">승인 기록 시각</th><th scope="col">연동 방식 / 기기</th><th scope="col">금액 / 결과</th><th scope="col">카드·할부 응답</th></tr></thead>
        <tbody>
        <?php foreach ($approval_rows as $approval_row) { ?>
        <tr>
            <td><?php echo get_text($approval_row['created_at']); ?></td>
            <td><?php echo get_text($approval_row['pg'].' / '.$approval_row['device']); ?></td>
            <td><?php echo number_format((int)$approval_row['amount']); ?>원 / <?php echo get_text($approval_row['result_code']); ?></td>
            <td class="td_left">
            <?php
            $approval_card = json_decode($approval_row['card_info'], true);
            if (!is_array($approval_card)) $approval_card = array();
            foreach (shop_pg_approval_card_fields($approval_row['pg']) as $approval_field) {
                $approval_value = array_key_exists($approval_field, $approval_card) ? $approval_card[$approval_field] : null;
                if (is_bool($approval_value)) $approval_value = $approval_value ? 'true' : 'false';
                echo get_text($approval_field).': '.($approval_value === null || $approval_value === '' ? '미수신' : htmlspecialchars((string)$approval_value, ENT_QUOTES, 'UTF-8')).'<br>';
            }
            ?>
            </td>
        </tr>
        <?php } ?>
        <?php if (!$approval_rows) { ?><tr><td colspan="4" class="empty_table">이 주문에 별도로 기록된 PG 승인 카드정보가 없습니다.<br>승인 카드정보 저장 기능 적용 전 주문은 이 정보가 표시되지 않을 수 있습니다.<br>이 정보의 유무는 결제 성공·실패를 의미하지 않습니다. 결제 상태는 주문결제 내역과 결제상세정보에서 확인하세요.</td></tr><?php } ?>
        </tbody>
    </table></div>
    </details>
</section>
