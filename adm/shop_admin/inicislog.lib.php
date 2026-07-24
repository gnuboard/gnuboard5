<?php
if (!defined('_GNUBOARD_')) exit;

if (!function_exists('inicis_admin_status_label')) {
    function inicis_admin_status_label($status)
    {
        $labels = array(
            'request_ready' => '결제창 요청 준비',
            'request_expired' => '결제창 종료·미진행',
            'authentication_received' => '인증결과 수신',
            'authentication_failed' => '인증 실패',
            'validation_failed' => '주문 검증 실패',
            'approval_started' => '승인 요청 중',
            'communication_failed' => '승인 통신 실패',
            'approval_failed' => '승인 실패',
            'approved' => '승인 성공',
            'order_saving' => '주문 저장 중',
            'order_saved' => '주문 저장 완료',
            'notification_received' => '서버 통보 수신',
            'notification_failed' => '서버 통보 검증 실패',
            'notification_acknowledged' => '처리 완료 통보 확인',
            'vbank_issued' => '가상계좌 발급',
            'paid' => '입금 통보 처리 완료',
            'paid_after_cancel' => '취소 후 입금·환불 필요',
            'refund_required' => '환불 필요',
            'refund_completed' => '환불 확인 완료',
            'cancel_requested' => '취소 요청 중',
            'canceled' => '취소 완료',
            'cancel_failed' => '취소 확인 필요',
            'partial_canceled' => '부분취소 완료',
            'partial_cancel_failed' => '부분취소 확인 필요',
            'inquiry_success' => 'KG 거래조회 완료',
            'inquiry_failed' => 'KG 거래조회 실패',
            'approval_reference_recovered' => '승인 TID 복구'
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}

if (!function_exists('inicis_admin_pay_type_label')) {
    function inicis_admin_pay_type_label($pay_type, $easy_pay)
    {
        $easy_labels = array(
            'SAMSUNGPAY' => '삼성페이',
            'LPAY' => 'L.pay',
            'KAKAOPAY' => '카카오페이',
            'EASYPAY' => '간편결제'
        );
        $easy_pay = strtoupper((string) $easy_pay);
        if (isset($easy_labels[$easy_pay]))
            return $easy_labels[$easy_pay].' (CARD)';

        $pay_labels = array('CARD' => '신용카드', 'BANK' => '계좌이체', 'VBANK' => '가상계좌', 'HPP' => '휴대폰');
        $pay_type = strtoupper((string) $pay_type);

        return isset($pay_labels[$pay_type]) ? $pay_labels[$pay_type] : $pay_type;
    }
}

if (!function_exists('inicis_admin_stage_label')) {
    function inicis_admin_stage_label($stage)
    {
        $labels = array(
            'request' => '결제 요청',
            'authentication' => '결제 인증',
            'validation' => '주문 검증',
            'approval' => '결제 승인',
            'order' => '주문 저장',
            'notification' => '서버 통보',
            'cancel' => '결제 취소',
            'reconcile' => 'KG 거래대사'
        );

        return isset($labels[$stage]) ? $labels[$stage] : $stage;
    }
}

if (!function_exists('inicis_admin_pg_status_label')) {
    function inicis_admin_pg_status_label($status)
    {
        $status = strtoupper((string) $status);
        $labels = array(
            '0' => '승인',
            '1' => '취소',
            '9' => '거래 없음',
            'N' => '가상계좌 입금대기',
            'Y' => '가상계좌 입금완료',
            'C' => '가상계좌 입금 전 취소',
            'APPROVAL' => '승인',
            'CANCEL' => '전체취소',
            'PART_CANCEL' => '부분취소',
            'DEPOSIT_COMPLETED' => '가상계좌 입금완료',
            'NON_DEPOSIT' => '가상계좌 입금대기',
            'DEPOSIT_CANCELED' => '가상계좌 입금취소',
            'WAITING_FOR_REFUND' => '가상계좌 환불대기',
            'REFUND_COMPLETED' => '가상계좌 환불완료'
        );

        return isset($labels[$status]) ? $labels[$status] : $status;
    }
}

if (!function_exists('inicis_admin_source_label')) {
    function inicis_admin_source_label($source)
    {
        $labels = array(
            'web' => 'PC 브라우저',
            'mobile' => '모바일 브라우저',
            'server' => '이니시스 통보',
            'system' => '시스템',
            'admin' => '관리자'
        );

        return isset($labels[$source]) ? $labels[$source] : $source;
    }
}

if (!function_exists('inicis_admin_primary_status')) {
    function inicis_admin_primary_status($row)
    {
        if (!empty($row['ip_refund_required']))
            return 'refund_required';
        if (!empty($row['ip_noti_status']) && $row['ip_noti_status'] === 'notification_failed')
            return 'notification_failed';
        if (!empty($row['ip_cancel_status']) && in_array($row['ip_cancel_status'], array('cancel_failed', 'partial_cancel_failed')))
            return $row['ip_cancel_status'];
        if (!empty($row['ip_cancel_status']) && $row['ip_cancel_status'] === 'cancel_requested')
            return 'cancel_requested';
        if (!empty($row['ip_noti_status']) && $row['ip_noti_status'] === 'notification_received')
            return 'notification_received';

        // 서버 통보가 이후 정상 확인·처리 상태로 넘어갔는데도 이전 통보 검증 실패가
        // 원본 상태에 남아 있으면, 최신 통보 상태를 대표 상태로 사용한다.
        if (isset($row['ip_status']) && $row['ip_status'] === 'notification_failed'
            && !empty($row['ip_noti_status']) && $row['ip_noti_status'] !== 'notification_failed')
            return $row['ip_noti_status'];

        return isset($row['ip_status']) ? $row['ip_status'] : '';
    }
}

if (!function_exists('inicis_admin_has_order')) {
    function inicis_admin_has_order($row)
    {
        if (isset($row['ip_order_type']) && $row['ip_order_type'] === 'personal')
            return !empty($row['personal_oid']);

        return !empty($row['order_oid']);
    }
}

if (!function_exists('inicis_admin_order_tid')) {
    function inicis_admin_order_tid($row)
    {
        if (isset($row['ip_order_type']) && $row['ip_order_type'] === 'personal')
            return isset($row['personal_tid']) ? $row['personal_tid'] : '';

        return isset($row['order_tid']) ? $row['order_tid'] : '';
    }
}

if (!function_exists('inicis_admin_order_amount')) {
    function inicis_admin_order_amount($row)
    {
        if (isset($row['ip_order_type']) && $row['ip_order_type'] === 'personal')
            return isset($row['personal_amount']) ? (int) $row['personal_amount'] : 0;

        return isset($row['order_amount']) ? (int) $row['order_amount'] : 0;
    }
}

if (!function_exists('inicis_admin_is_anomaly')) {
    function inicis_admin_is_anomaly($row)
    {
        if (!empty($row['ip_audit_error']))
            return true;

        $status = isset($row['ip_status']) ? $row['ip_status'] : '';
        $noti_status = isset($row['ip_noti_status']) ? $row['ip_noti_status'] : '';
        $cancel_status = isset($row['ip_cancel_status']) ? $row['ip_cancel_status'] : '';
        if (!empty($row['ip_refund_required']) || in_array($status, array('paid_after_cancel', 'refund_required')))
            return true;
        if ($noti_status === 'notification_failed')
            return true;
        if (in_array($cancel_status, array('cancel_failed', 'partial_cancel_failed')))
            return true;
        if ($status === 'partial_cancel_failed')
            return true;
        $has_order = inicis_admin_has_order($row);
        $saved_tid = $has_order ? inicis_admin_order_tid($row) : '';
        $amount_mismatch = $has_order && (int) $row['ip_amount'] > 0
            && inicis_admin_order_amount($row) !== (int) $row['ip_amount'];
        $tid_mismatch = $has_order && !empty($row['ip_tid']) && $saved_tid !== $row['ip_tid'];
        $mismatch = $amount_mismatch || $tid_mismatch;
        $is_personal = isset($row['ip_order_type']) && $row['ip_order_type'] === 'personal';
        $order_canceled = $has_order && !$is_personal
            && isset($row['order_status']) && $row['order_status'] === '취소';
        $order_receipt = $is_personal
            ? (isset($row['personal_receipt_price']) ? (int) $row['personal_receipt_price'] : 0)
            : (isset($row['order_receipt_price']) ? (int) $row['order_receipt_price'] : 0);
        $order_misu = !$is_personal && isset($row['order_misu']) ? (int) $row['order_misu'] : 0;
        $vbank_paid = isset($row['ip_pay_type']) && $row['ip_pay_type'] === 'VBANK'
            && ($status === 'paid' || in_array($noti_status, array('paid', 'paid_after_cancel')));
        if ($status !== 'refund_completed' && $vbank_paid
            && ($order_canceled || !$has_order || $order_receipt !== (int) $row['ip_amount'] || $order_misu !== 0))
            return true;
        if (isset($row['ip_pay_type']) && $row['ip_pay_type'] === 'VBANK'
            && ($status === 'vbank_issued' || $noti_status === 'vbank_issued') && $order_canceled
            && $status !== 'canceled' && $cancel_status !== 'canceled')
            return true;

        $local_order_active = false;
        if ($has_order) {
            if (isset($row['ip_order_type']) && $row['ip_order_type'] === 'personal')
                $local_order_active = isset($row['personal_receipt_price']) && (int) $row['personal_receipt_price'] > 0;
            else
                $local_order_active = isset($row['order_status']) && $row['order_status'] !== '취소';
        }

        $pg_status = !empty($row['ip_pg_result_code']) && $row['ip_pg_result_code'] === '00'
            ? strtoupper((string) $row['ip_pg_status']) : '';
        $pg_mismatch = $pg_status !== ''
            && ((!empty($row['ip_pg_tid']) && !empty($row['ip_tid']) && $row['ip_pg_tid'] !== $row['ip_tid'])
                || ((int) $row['ip_pg_amount'] > 0 && (int) $row['ip_amount'] > 0 && (int) $row['ip_pg_amount'] !== (int) $row['ip_amount']));
        $pg_paid = in_array($pg_status, array('0', 'Y', 'APPROVAL', 'PART_CANCEL', 'DEPOSIT_COMPLETED'));
        $pg_pending = in_array($pg_status, array('N', 'NON_DEPOSIT'));
        $pg_attention = $pg_status === 'WAITING_FOR_REFUND';
        $pg_canceled = in_array($pg_status, array('1', '9', 'C', 'CANCEL', 'DEPOSIT_CANCELED', 'REFUND_COMPLETED'));

        if ($pg_attention)
            return true;

        // 부분취소 합계로 전액 환불된 주문이 취소 상태이면 PG의 부분취소 표시와 일치하는 종결 상태로 본다.
        $part_cancel_settled = $pg_status === 'PART_CANCEL' && !$local_order_active && !$is_personal
            && isset($row['order_refund_price'], $row['order_receipt_price'])
            && (int) $row['order_receipt_price'] > 0
            && (int) $row['order_refund_price'] >= (int) $row['order_receipt_price'];
        if ($pg_paid)
            return $pg_mismatch || (!$part_cancel_settled && (!$local_order_active || $mismatch));

        $updated = isset($row['ip_updated_at']) ? strtotime($row['ip_updated_at']) : false;
        if ($pg_pending)
            return $pg_mismatch || $status === 'canceled' || $status === 'cancel_failed' || $mismatch
                || (!$has_order && $updated !== false && $updated < G5_SERVER_TIME - 180);

        if ($pg_canceled)
            return $pg_mismatch || $local_order_active;

        if (in_array($status, array('authentication_received', 'approval_started', 'cancel_requested'))
            || $noti_status === 'notification_received' || $cancel_status === 'cancel_requested')
            return $updated !== false && $updated < G5_SERVER_TIME - 180;
        if ($status === 'communication_failed')
            return true;
        if ($status === 'validation_failed' && !empty($row['ip_tid']))
            return true;
        if ($status === 'cancel_failed' || $cancel_status === 'cancel_failed')
            return true;

        if ($status === 'canceled') {
            return $local_order_active;
        }

        if ($status === 'notification_received')
            return $updated !== false && $updated < G5_SERVER_TIME - 180;

        if ($has_order)
            return $mismatch;

        $approved_states = array('approved', 'order_saving', 'order_saved', 'notification_received', 'vbank_issued', 'paid');
        if (!in_array($status, $approved_states))
            return false;

        return $updated !== false && $updated < G5_SERVER_TIME - 180;
    }
}

if (!function_exists('inicis_admin_anomaly_sql')) {
    function inicis_admin_anomaly_sql()
    {
        $order_amount = "(o.od_cart_price + o.od_send_cost + o.od_send_cost2 - o.od_cart_coupon - o.od_coupon - o.od_send_coupon - o.od_receipt_point)";
        $has_order = "((p.ip_order_type = 'personal' and pp.pp_id is not null) or (p.ip_order_type <> 'personal' and o.od_id is not null))";
        $saved_tid = "if(p.ip_order_type = 'personal', ifnull(pp.pp_tno, ''), ifnull(o.od_tno, ''))";
        $saved_amount = "if(p.ip_order_type = 'personal', ifnull(pp.pp_price, 0), ifnull($order_amount, 0))";
        $mismatch = "($has_order and ((p.ip_tid <> '' and p.ip_tid <> $saved_tid) or (p.ip_amount > 0 and p.ip_amount <> $saved_amount)))";
        $local_active = "((p.ip_order_type = 'personal' and pp.pp_id is not null and pp.pp_receipt_price > 0) or (p.ip_order_type <> 'personal' and o.od_id is not null and o.od_status <> '취소'))";
        $local_canceled = "(p.ip_order_type <> 'personal' and o.od_id is not null and o.od_status = '취소')";
        $vbank_paid_mismatch = "(p.ip_status <> 'refund_completed' and p.ip_pay_type = 'VBANK'
                                 and (p.ip_status = 'paid' or p.ip_noti_status in ('paid','paid_after_cancel'))
                                 and ((p.ip_order_type = 'personal' and (pp.pp_id is null or pp.pp_receipt_price <> p.ip_amount))
                                      or (p.ip_order_type <> 'personal' and (o.od_id is null or o.od_status = '취소' or o.od_receipt_price <> p.ip_amount or o.od_misu <> 0))))";
        $vbank_canceled_before_payment = "(p.ip_pay_type = 'VBANK' and (p.ip_status = 'vbank_issued' or p.ip_noti_status = 'vbank_issued')
                                            and p.ip_status <> 'canceled' and p.ip_cancel_status <> 'canceled' and $local_canceled)";
        $stale = "p.ip_updated_at < date_sub(now(), interval 3 minute)";
        $pg_paid = "(p.ip_pg_result_code = '00' and p.ip_pg_status in ('0','Y','APPROVAL','PART_CANCEL','DEPOSIT_COMPLETED'))";
        $pg_pending = "(p.ip_pg_result_code = '00' and p.ip_pg_status in ('N','NON_DEPOSIT'))";
        $pg_attention = "(p.ip_pg_result_code = '00' and p.ip_pg_status = 'WAITING_FOR_REFUND')";
        $pg_canceled = "(p.ip_pg_result_code = '00' and p.ip_pg_status in ('1','9','C','CANCEL','DEPOSIT_CANCELED','REFUND_COMPLETED'))";
        $pg_known = "($pg_paid or $pg_pending or $pg_attention or $pg_canceled)";
        $part_cancel_settled = "(p.ip_pg_result_code = '00' and p.ip_pg_status = 'PART_CANCEL'
                                  and $local_canceled and o.od_receipt_price > 0 and o.od_refund_price >= o.od_receipt_price)";
        $pg_mismatch = "(p.ip_pg_result_code = '00' and ((p.ip_pg_tid <> '' and p.ip_tid <> '' and p.ip_pg_tid <> p.ip_tid) or (p.ip_pg_amount > 0 and p.ip_amount > 0 and p.ip_pg_amount <> p.ip_amount)))";
        $approved_states = "'approved','order_saving','order_saved','notification_received','vbank_issued','paid'";
        $local_anomaly = "(p.ip_status = 'cancel_failed'
                            or p.ip_status = 'communication_failed'
                            or (p.ip_status = 'validation_failed' and p.ip_tid <> '')
                            or (p.ip_status in ('authentication_received','approval_started','cancel_requested') and $stale)
                            or (p.ip_noti_status = 'notification_received' and $stale)
                            or (p.ip_cancel_status = 'cancel_requested' and $stale)
                            or (p.ip_status = 'canceled' and $local_active)
                            or (p.ip_status = 'notification_received' and $stale)
                            or $mismatch
                            or (not $has_order and p.ip_status in ($approved_states) and $stale))";

        return "(p.ip_audit_error = '1'
                  or p.ip_refund_required = '1'
                  or p.ip_status in ('paid_after_cancel','refund_required')
                  or p.ip_noti_status = 'notification_failed'
                  or p.ip_cancel_status in ('cancel_failed','partial_cancel_failed')
                  or p.ip_status = 'partial_cancel_failed'
                  or $vbank_paid_mismatch
                  or $vbank_canceled_before_payment
                  or $pg_mismatch
                  or $pg_attention
                  or ($pg_paid and not $part_cancel_settled and (not $local_active or $mismatch))
                  or ($pg_pending and (p.ip_status in ('canceled','cancel_failed') or $mismatch or (not $has_order and $stale)))
                  or ($pg_canceled and $local_active)
                  or (not $pg_known and $local_anomaly))";
    }
}

if (!function_exists('inicis_admin_alert_key')) {
    function inicis_admin_alert_key($row)
    {
        return md5(
            (isset($row['ip_status']) ? $row['ip_status'] : '').'|'.
            (isset($row['ip_tid']) ? $row['ip_tid'] : '').'|'.
            (isset($row['ip_amount']) ? $row['ip_amount'] : '').'|'.
            (isset($row['ip_pg_result_code']) ? $row['ip_pg_result_code'] : '').'|'.
            (isset($row['ip_pg_status']) ? $row['ip_pg_status'] : '').'|'.
            (isset($row['ip_pg_tid']) ? $row['ip_pg_tid'] : '').'|'.
            (isset($row['ip_pg_amount']) ? $row['ip_pg_amount'] : '').'|'.
            (isset($row['ip_noti_status']) ? $row['ip_noti_status'] : '').'|'.
            (isset($row['ip_noti_code']) ? $row['ip_noti_code'] : '').'|'.
            (isset($row['ip_cancel_status']) ? $row['ip_cancel_status'] : '').'|'.
            (!empty($row['ip_refund_required']) ? '1' : '0').'|'.
            (inicis_admin_has_order($row) ? '1' : '0').'|'.
            inicis_admin_order_tid($row).'|'.inicis_admin_order_amount($row).'|'.
            (isset($row['order_status']) ? $row['order_status'] : '').'|'.
            (isset($row['order_receipt_price']) ? $row['order_receipt_price'] : '').'|'.
            (isset($row['order_misu']) ? $row['order_misu'] : '').'|'.
            (isset($row['personal_receipt_price']) ? $row['personal_receipt_price'] : '').'|'.
            (!empty($row['ip_audit_error']) ? '1' : '0')
        );
    }
}

if (!function_exists('inicis_admin_recommendation')) {
    function inicis_admin_recommendation($row)
    {
        if (!empty($row['ip_audit_error']))
            return '감사 상세 이력 기록이 일부 누락됐습니다. 서버 오류 로그를 확인하고 KG이니시스 상점관리자 거래내역을 기준으로 상태를 확정하십시오.';

        if (!empty($row['ip_refund_required']) || in_array($row['ip_status'], array('paid_after_cancel', 'refund_required')))
            return '취소된 주문에 가상계좌 입금이 확인됐습니다. 상품을 발송하지 말고 KG이니시스 상점관리자에서 입금 TID를 확인한 뒤 고객 환불을 처리하고 KG 거래조회를 다시 실행하십시오.';
        if (!empty($row['ip_noti_status']) && $row['ip_noti_status'] === 'notification_failed')
            return '최근 KG 서버 통보를 검증하거나 저장하지 못했습니다. 통보 실패 코드와 서버 오류 로그를 확인하고 상점관리자에서 입금통보 결과를 재전송하십시오.';
        if (!empty($row['ip_cancel_status']) && $row['ip_cancel_status'] === 'cancel_failed')
            return 'PG 전체취소 결과를 확정하지 못했습니다. 영카트 주문상태를 변경하지 말고 KG이니시스 상점관리자의 원거래를 직접 확인하십시오.';
        if (!empty($row['ip_cancel_status']) && $row['ip_cancel_status'] === 'partial_cancel_failed')
            return '부분취소 결과를 확정하지 못했습니다. KG이니시스 상점관리자의 원거래와 부분취소 거래내역을 직접 대조하십시오.';
        if (!empty($row['ip_noti_status']) && $row['ip_noti_status'] === 'notification_received')
            return '가상계좌 입금 통보 수신 후 주문 반영 완료 기록이 없습니다. 주문 입금액과 미수금을 확인하고, 불일치하면 KG이니시스 상점관리자에서 입금 통보를 재전송하십시오.';
        if (!empty($row['ip_cancel_status']) && $row['ip_cancel_status'] === 'cancel_requested')
            return 'PG 취소 요청 후 결과가 확정되지 않았습니다. 영카트 주문을 추가로 변경하지 말고 KG이니시스 상점관리자에서 원거래 취소 여부를 먼저 확인하십시오.';

        if (!empty($row['ip_pg_checked_at']) && $row['ip_pg_result_code'] !== '00')
            return 'KG 거래조회가 실패했습니다. 해당 MID의 거래조회 계약, INIAPI KEY와 서버 IPv4를 확인한 후 다시 조회하거나 KG이니시스 상점관리자에서 직접 조회하십시오.';

        $has_order = inicis_admin_has_order($row);
        $pg_status = !empty($row['ip_pg_result_code']) && $row['ip_pg_result_code'] === '00' ? strtoupper((string) $row['ip_pg_status']) : '';
        $pg_paid = in_array($pg_status, array('0', 'Y', 'APPROVAL', 'PART_CANCEL', 'DEPOSIT_COMPLETED'));
        $pg_pending = in_array($pg_status, array('N', 'NON_DEPOSIT'));
        $pg_attention = $pg_status === 'WAITING_FOR_REFUND';
        $pg_canceled = in_array($pg_status, array('1', '9', 'C', 'CANCEL', 'DEPOSIT_CANCELED', 'REFUND_COMPLETED'));

        if ($pg_status !== '' && ((!empty($row['ip_pg_tid']) && !empty($row['ip_tid']) && $row['ip_pg_tid'] !== $row['ip_tid'])
            || ((int) $row['ip_pg_amount'] > 0 && (int) $row['ip_amount'] > 0 && (int) $row['ip_pg_amount'] !== (int) $row['ip_amount'])))
            return 'KG 거래조회 결과와 영카트 결제 이력의 TID 또는 금액이 다릅니다. 자동 조치하지 말고 KG이니시스 상점관리자 원거래 내역을 직접 확인하십시오.';
        if ($pg_attention)
            return '가상계좌 입금 후 환불 대기 상태입니다. 환불이 완료될 때까지 KG이니시스 상점관리자에서 상태를 계속 확인하십시오.';
        if ($pg_paid && !$has_order)
            return 'KG이니시스에는 승인 또는 입금이 있으나 영카트 주문이 없습니다. 원 주문 데이터와 재고·포인트 반영 여부를 확인한 뒤 안전한 복원이 불가능하면 상점관리자에서 승인 취소하십시오.';
        if ($pg_pending && !$has_order)
            return '가상계좌가 발급됐지만 영카트 주문이 없습니다. 고객 입금 전에 주문 복원 가능 여부를 확인하고, 복원이 불가능하면 해당 가상계좌 거래를 정리하십시오.';
        $recommend_is_personal = isset($row['ip_order_type']) && $row['ip_order_type'] === 'personal';
        $order_canceled_match = $has_order && ($recommend_is_personal
            ? !(isset($row['personal_receipt_price']) && (int) $row['personal_receipt_price'] > 0)
            : (isset($row['order_status']) && $row['order_status'] === '취소'));
        if ($pg_canceled && $order_canceled_match)
            return 'KG이니시스 거래와 영카트 주문이 모두 취소 상태로 일치합니다. 별도 조치가 필요하지 않습니다.';
        if ($pg_canceled && $has_order)
            return 'KG이니시스 거래는 취소 또는 거래 없음 상태입니다. 영카트 주문의 결제금액·상태를 확인하고 PG 상태와 일치하도록 관리자 처리하십시오.';
        if ($pg_status === 'PART_CANCEL' && $order_canceled_match && !$recommend_is_personal
            && isset($row['order_refund_price'], $row['order_receipt_price'])
            && (int) $row['order_receipt_price'] > 0
            && (int) $row['order_refund_price'] >= (int) $row['order_receipt_price'])
            return 'KG이니시스 거래가 부분취소 합계로 전액 환불되었고 영카트 주문도 취소 상태입니다. 별도 조치가 필요하지 않습니다.';
        if (isset($row['ip_status']) && in_array($row['ip_status'], array('authentication_received', 'approval_started', 'communication_failed')) && empty($row['ip_tid']))
            return '최종 승인 TID가 영카트에 저장되기 전에 처리가 중단됐을 수 있습니다. 같은 주문을 다시 결제시키기 전에 KG이니시스 상점관리자에서 주문번호 OID로 승인 여부를 확인하십시오.';
        if (isset($row['ip_status']) && $row['ip_status'] === 'validation_failed' && !empty($row['ip_tid']))
            return '승인 TID가 생성된 뒤 주문 검증이 중단됐습니다. KG 거래조회 결과를 확인하고 주문이 없다면 승인 취소 여부를 결정하십시오.';
        if ($has_order && ((!empty($row['ip_tid']) && inicis_admin_order_tid($row) !== $row['ip_tid'])
            || ((int) $row['ip_amount'] > 0 && inicis_admin_order_amount($row) !== (int) $row['ip_amount'])))
            return '결제 이력과 주문 DB의 TID 또는 금액이 다릅니다. 주문 상태를 변경하기 전에 KG이니시스 원거래 TID와 승인금액을 직접 대조하십시오.';
        if (isset($row['ip_status']) && $row['ip_status'] === 'cancel_failed')
            return '자동 취소 결과를 확정하지 못했습니다. KG 거래조회를 다시 실행하고 승인 상태라면 상점관리자에서 취소 여부를 결정하십시오.';
        if (isset($row['ip_status']) && $row['ip_status'] === 'partial_cancel_failed')
            return '부분취소 결과를 확정하지 못했습니다. KG이니시스 상점관리자의 원거래와 부분취소 거래내역을 직접 대조하십시오.';

        return inicis_admin_is_anomaly($row)
            ? 'KG이니시스 상점관리자에서 승인 TID와 금액을 확인한 뒤 영카트 주문 상태와 맞추십시오.'
            : '현재 별도 조치가 필요하지 않습니다. KG 거래상태와 영카트 주문의 TID 및 금액이 일치합니다.';
    }
}
