<?php
if (!defined('_GNUBOARD_')) exit;

// 기본 주문서와 PG별 폼 필드의 명시적 허용 목록. 비밀번호/상태 토큰은 별도 저장한다.
function shop_order_allowed_fields($pg)
{
    $common = explode(' ', 'inicis_pro nhnkcp_pay_case ad_default ad_sel_addr ad_subject amountValue cp_id cp_price f_cp_id f_cp_prc it_id it_name it_notax it_price item_coupon max_temp_point naverpay_point_direct o_cp_id o_cp_prc od_addr1 od_addr2 od_addr3 od_addr_jibeon od_b_addr1 od_b_addr2 od_b_addr3 od_b_addr_jibeon od_b_hp od_b_name od_b_tel od_b_zip od_bank_account od_coupon od_cp_id od_deposit_name od_email od_goods_name od_hope_date od_hp od_ip od_memo od_name od_price od_send_cost od_send_cost2 od_send_coupon od_settle_case od_tel od_temp_point od_test od_zip org_od_price post_cart_id pp_email pp_hp pp_id pp_name pp_settle_case s_cp_id s_cp_prc sc_cp_id sw_direct');
    $pg_fields = array(
        'toss' => 'amountCurrency buyeremail buyertel cardUseAppCardOnly cardUseCardPoint cardUseEscrow cardeasyPay cardflowMode comm_free_mny comm_tax_mny comm_vat_mny customerEmail customerMobilePhone customerName escrowProducts good_mny id_info method od_id orderId orderName submitChecked taxFreeAmount tr_code tx windowTarget',
        'kcp' => 'ActionResult KCP_PAY_MODULE Ret_URL amt_sup amt_svc amt_tax amt_tot applepay_direct approval_key bank_issu bank_name bask_cntx buyr_mail buyr_name buyr_tel1 buyr_tel2 cash_authno cash_id_info cash_tr_code cash_tsdtime cash_yn comm_free_mny comm_tax_mny comm_vat_mny complex_pnt_yn corp_type currency def_site_cd deli_term disp_tax_yn enc_data enc_info eng_flag epnt_issu escrow_foot escw_used fix_inst good_cd good_expr good_info good_mny good_name id_info ipgm_date kakaopay_direct kcp_noint kcp_noint_quota module_type naverpay_direct nhnkcp_pay_case not_used_card ordr_idxx param_opt_1 param_opt_2 param_opt_3 pay_method pay_mod payco_direct pt_memcorp_cd quotaopt rcvr_add1 rcvr_add2 rcvr_mail rcvr_name rcvr_tel1 rcvr_tel2 rcvr_zipx req_tx res_cd res_msg ret_pay_method save_ocb settle_method shop_name shop_user_id site_cd site_logo site_name skin_indx submitChecked tablet_size tar_opener tax_flag tk_shop_id tno tr_code trace_no trad_time tran_cd tx use_pay_method used_card used_card_CCXX used_card_YN vcnt_expire_term vcnt_expire_term_time wish_vbank_list',
        'inicis' => 'gopaymethod DEF_RESERVED P_AMT P_APPL_NUM P_AUTH_DT P_AUTH_NO P_CARD_ISSUER P_EMAIL P_GOODS P_HASH P_HPP_CORP P_HPP_METHOD P_MID P_MOBILE P_NEXT_URL P_NOTI P_NOTI_URL P_OID P_QUOTABASE P_RESERVED P_RETURN_URL P_SKIP_TERMS P_TAX P_TAXFREE P_TYPE P_UNAME P_VACT_BANK P_VACT_NAME P_VACT_NUM acceptmethod buyeremail buyername buyertel charset closeUrl comm_free_mny comm_tax_mny comm_vat_mny currency good_mny goodname id_info ini_logoimage_url ini_menuarea_url mKey mid nointerest od_id oid parentemail payViewType popupUrl price quotabase recvaddr recvname recvpostnum recvtel res_cd returnUrl signature submitChecked tax taxfree timestamp tr_code tx version',
        'lg' => 'CST_MID CST_PLATFORM LGD_AMOUNT LGD_BUYER LGD_BUYERADDRESS LGD_BUYEREMAIL LGD_BUYERID LGD_BUYERIP LGD_BUYERPHONE LGD_CASHRECEIPTYN LGD_CASNOTEURL LGD_CUSTOM_FIRSTPAY LGD_CUSTOM_PROCESSTYPE LGD_CUSTOM_SKIN LGD_CUSTOM_USABLEPAY LGD_EASYPAY_ONLY LGD_ENCODING LGD_ENCODING_RETURNURL LGD_ESCROW_ADDRESS1 LGD_ESCROW_ADDRESS2 LGD_ESCROW_BUYERPHONE LGD_ESCROW_ZIPCODE LGD_HASHDATA LGD_MID LGD_OID LGD_PAYKEY LGD_PRODUCTINFO LGD_RECEIVER LGD_RECEIVERPHONE LGD_RETURNURL LGD_TAXFREEAMOUNT LGD_TIMESTAMP LGD_VERSION LGD_WINDOW_VER comm_free_mny comm_tax_mny comm_vat_mny good_mny id_info od_id res_cd submitChecked tr_code tx',
        'nicepay' => 'Amt BuyerEmail BuyerName BuyerTel CharSet DirectEasyPay DirectShowOpt EasyPayCardCode EasyPayMethod EasyPayQuota EdiDate GoodsCl GoodsName GoodsVat MID Moid MultiEasyPayQuota NicepayReserved NpLang PayMethod ReqReserved ReturnURL SelectCardCode SelectQuota ServiceAmt SignData SupplyAmt TaxFreeAmt TransType VbankExpDate buyeremail buyertel comm_free_mny comm_tax_mny comm_vat_mny good_mny id_info od_id submitChecked tr_code tx',
        'samsungpay' => 'DEF_RESERVED P_AMT P_APPL_NUM P_AUTH_DT P_AUTH_NO P_CARD_ISSUER P_EMAIL P_GOODS P_HASH P_HPP_CORP P_HPP_METHOD P_MID P_MOBILE P_NEXT_URL P_NOTI P_NOTI_URL P_OID P_QUOTABASE P_RESERVED P_RETURN_URL P_SKIP_TERMS P_TAX P_TAXFREE P_TYPE P_UNAME P_VACT_BANK P_VACT_NAME P_VACT_NUM good_mny res_cd samsungpay_form',
    );
    return array_unique(array_merge($common, isset($pg_fields[$pg]) ? explode(' ', $pg_fields[$pg]) : array()));
}

function shop_order_filter_data($input, $pg)
{
    $out = array();
    foreach (shop_order_allowed_fields($pg) as $key) {
        if (!array_key_exists($key, $input)) continue;
        $value = $input[$key];
        if (is_array($value)) {
            if (count($value) > 1000) shop_order_access_fail();
            foreach ($value as $k=>$v) {
                if (!is_scalar($v) || strlen((string)$v) > 65536 || !preg_match('/\A[0-9A-Za-z_-]{1,100}\z/D', (string)$k)) shop_order_access_fail();
            }
        } elseif (!is_scalar($value) || strlen((string)$value) > 65536) shop_order_access_fail();
        $out[$key] = $value;
    }
    if (strlen(serialize($out)) > 1048576) shop_order_access_fail();
    return $out;
}

function shop_order_toss_providers()
{
    $providers = array();
    foreach (shop_easypay_catalog('toss') as $provider) $providers[] = $provider[1];
    return $providers;
}
