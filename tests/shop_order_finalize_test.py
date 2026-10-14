"""실제 최종 주문 PHP/DB 실행. 별도 테스트 설치본에서만 TossPayments 전송 클래스를 대체한다."""
from pathlib import Path
from html.parser import HTMLParser
import json, os, secrets, time, hashlib
import shop_order_state_test as T

class Form(HTMLParser):
    def __init__(self):super().__init__();self.fields={}
    def handle_starttag(self,tag,attrs):
        a=dict(attrs)
        if tag=='input' and 'name' in a:self.fields[a['name']]=a.get('value','')

def run():
    T.sql('CREATE TABLE IF NOT EXISTS g5_order_test_pg (od_id varchar(20) PRIMARY KEY, approvals int, queries int, data mediumtext) ENGINE=InnoDB')
    T.sql("UPDATE g5_shop_default SET de_pg_service='toss',de_card_use=1,de_send_cost_list='0',de_send_cost_limit='0',de_sms_use1=0,de_sms_use2=0,de_sms_use3=0,de_sms_use4=0")
    T.sql("UPDATE g5_config SET cf_email_use=0")
    for pg,mobile,is_member in [('toss',False,False),('toss',True,False),('kcp',True,False),('toss',False,True)]:
        T.sql("UPDATE g5_shop_default SET de_pg_service='%s',de_card_test=1"%pg)
        oid=str(time.time_ns()//1000);cart=str(int(oid)+1);item='test'+secrets.token_hex(5);sid=secrets.token_hex(16)
        T.sql("INSERT INTO g5_shop_item SET it_id='%s',ca_id='10',it_name='LOCAL TEST ITEM',it_price=1000,it_stock_qty=100,it_use=1,it_soldout=0,it_sc_type=1,it_explan='',it_mobile_explan='',it_head_html='',it_tail_html='',it_mobile_head_html='',it_mobile_tail_html='',it_info_value='',it_shop_memo=''"%item)
        T.sql("INSERT INTO g5_shop_cart SET od_id='%s',it_id='%s',it_name='LOCAL TEST ITEM',ct_price=1000,ct_qty=1,ct_select=1,ct_status='쇼핑',io_type=0,io_id='',io_price=0,ct_option='LOCAL TEST ITEM',ct_time=NOW(),ct_send_cost=0"%(cart,item))
        T.call('session',sid=sid,data=dict(ss_order_id=oid,ss_cart_id=cart,ss_direct=''),member=is_member)
        fields=dict(od_name='FINAL_FAKE_BUYER',od_pwd='FinalGuestPassword!',od_email='fake@example.invalid',od_tel='000',od_hp='000',od_zip='00000',od_addr1='FAKE',od_addr2='',od_addr3='',od_addr_jibeon='R',od_b_name='FINAL_FAKE_BUYER',od_b_tel='000',od_b_hp='000',od_b_zip='00000',od_b_addr1='FAKE',od_b_addr2='',od_b_addr3='',od_b_addr_jibeon='R',od_memo='',od_deposit_name='',od_price='1000',od_send_cost='0',od_send_cost2='0',od_send_coupon='0',od_temp_point='0',amountValue='1000',good_mny='1000',od_settle_case='신용카드',orderId=oid)
        body,h=T.http('/shop/ajax.orderdatasave.php',sid,fields);token=h['X-G5-Order-State']
        if pg=='toss':body=T.return_page(oid,token=token,mobile=mobile)
        else:body,_=T.http('/mobile/shop/kcp/order_approval_form.php?'+T.urllib.parse.urlencode(dict(g5_order_state=token)),data=dict(ordr_idxx=oid,enc_data='fixture',enc_info='fixture',tran_cd='fixture',req_tx='pay',res_cd='0000',good_mny='1000',use_pay_method='100000000000'))
        parser=Form();parser.feed(body)
        expected_tno='test_key' if pg=='toss' else 'KCPTEST'+oid
        T.check(('mobile' if mobile else 'pc')+' restored order has no plain password','od_pwd' not in parser.fields and parser.fields.get('g5_order_state')==token)
        response,headers=T.http(('/mobile' if mobile else '')+'/shop/orderformupdate.php',data=parser.fields)
        order=T.sql("SELECT od_id,mb_id,od_tno,od_pwd,od_receipt_price,od_status FROM g5_shop_order WHERE od_id='%s'"%oid)
        if not order:
            Path(T.C['root']).parent.joinpath('finalize-failure.html').write_text(response,encoding='utf-8')
            print(response[-1200:])
        T.check(('mobile' if mobile else 'pc')+' final order saved with fake PG',len(order)==1 and order[0]['od_tno']==expected_tno and int(order[0]['od_receipt_price'])==1000)
        state=T.sql("SELECT status,state_json,token_hash FROM g5_shop_order_access WHERE od_id='%s'"%oid)[0]
        T.check(('mobile' if mobile else 'pc')+' completed state and temp cleared',state['status']=='completed' and not state['state_json'] and not state['token_hash'] and not T.sql("SELECT od_id FROM g5_shop_order_data WHERE od_id='%s'"%oid))
        T.check(('mobile' if mobile else 'pc')+' cart moved once',T.sql("SELECT od_id FROM g5_shop_cart WHERE it_id='%s'"%item)[0]['od_id']==oid)
        T.check(('mobile' if mobile else 'pc')+' stored password remains hashed',order[0]['od_pwd']!='FinalGuestPassword!' and len(order[0]['od_pwd'])>40)
        if not is_member:
            inquiry,_=T.http(('/mobile' if mobile else '')+'/shop/orderinquiry.php',data=dict(od_id=oid,od_pwd='FinalGuestPassword!'))
            T.check(('mobile' if mobile else 'pc')+' guest order inquiry accepts original password',oid in inquiry and '주문이 존재하지 않습니다' not in inquiry)
            inquiry,_=T.http(('/mobile' if mobile else '')+'/shop/orderinquiry.php',data=dict(od_id=oid,od_pwd='wrong'))
            T.check(('mobile' if mobile else 'pc')+' guest order inquiry rejects wrong password','주문이 존재하지 않습니다' in inquiry)
        else:T.check('member token restores payment-scoped owner',order[0]['mb_id']=='testadmin')
        T.http(('/mobile' if mobile else '')+'/shop/orderformupdate.php',data=parser.fields)
        T.check(('mobile' if mobile else 'pc')+' duplicate final POST cannot reapprove',int(T.sql("SELECT approvals FROM g5_order_test_pg WHERE od_id='%s'"%oid)[0]['approvals'])==1)
    T.sql("UPDATE g5_shop_default SET de_pg_service='toss'")
    for mobile in [False,True]:
        oid=str(time.time_ns()//1000);sid=secrets.token_hex(16);dt='2026-09-14 12:00:00'
        T.sql("INSERT INTO g5_shop_personalpay SET pp_id='%s',pp_use=1,pp_price=1000,pp_time='%s',pp_content='',pp_shop_memo='',pp_cash_info=''"%(oid,dt))
        T.call('session',sid=sid,data=dict(ss_personalpay_id=oid,ss_personalpay_hash=hashlib.md5((oid+'1000'+dt).encode()).hexdigest()))
        body,h=T.http('/shop/ajax.orderdatasave.php',sid,dict(pp_id=oid,pp_name='PERSONAL_FAKE',pp_email='fake@example.invalid',pp_hp='000',pp_settle_case='신용카드',amountValue='1000',good_mny='1000',orderId=oid))
        token=h['X-G5-Order-State'];body=T.return_page(oid,token=token,mobile=mobile);parser=Form();parser.feed(body)
        response,_=T.http(('/mobile' if mobile else '')+'/shop/personalpayformupdate.php',data=parser.fields)
        pp=T.sql("SELECT pp_tno,pp_receipt_price FROM g5_shop_personalpay WHERE pp_id='%s'"%oid)[0]
        if not pp['pp_tno']:print(response[-1000:])
        T.check(('mobile' if mobile else 'pc')+' personal payment completes without session cookie',pp['pp_tno']=='test_key' and int(pp['pp_receipt_price'])==1000)
        T.check(('mobile' if mobile else 'pc')+' personal state consumed',T.sql("SELECT status FROM g5_shop_order_access WHERE od_id='%s'"%oid)[0]['status']=='completed')
    oid,sid,token,cart=T.make()
    T.call('approve',id=oid,request={'g5_order_state':token})
    cancelled=T.call('cancel',id=oid,request={'g5_order_state':token})
    T.check('automatic cancellation uses authorized order instead of injected order',cancelled=='cancelled')
    T.check('cancelled transaction cannot be restored','STATE_FAKE_BUYER' not in T.return_page(oid,token=token))

if __name__=='__main__':
    adapter=T.ROOT/'shop/toss/toss.inc.php';original=adapter.read_bytes()
    kcp=T.ROOT/'mobile/shop/kcp/pp_ax_hub_lib.php';kcp_original=kcp.read_bytes()
    try:
        adapter.write_bytes(Path(__file__).with_name('toss_http_stub.php').read_bytes())
        kcp.write_bytes(Path(__file__).with_name('kcp_http_stub.php').read_bytes());run()
    finally:
        adapter.write_bytes(original);kcp.write_bytes(kcp_original)
        Path(os.environ.get('G5_ORDER_FINALIZE_RESULTS','shop-order-finalize-results.json')).write_text(json.dumps(T.RESULTS,ensure_ascii=False,indent=2),encoding='utf-8')
