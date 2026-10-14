"""실제 PHP/SQL/HTTP와 결제 전송 대역을 이용한다. G5_ORDER_TEST_CONFIG는 로컬 설치 설정 JSON."""
from pathlib import Path
import concurrent.futures, hashlib, json, os, secrets, subprocess, time, urllib.request, urllib.parse, re
from http.cookiejar import CookieJar

C=json.loads(Path(os.environ['G5_ORDER_TEST_CONFIG']).read_text(encoding='utf-8'))
ROOT=Path(C['root']); BASE='http://127.0.0.1:'+str(C['port']); RESULTS=[]
def call(action,**kw):
    p=subprocess.run([C['php'],'-c',C['ini'],str(ROOT/'tests/shop_order_state_fixture.php')],input=json.dumps(dict(action=action,**kw)).encode(),capture_output=True)
    if p.returncode: raise RuntimeError(p.stderr.decode('utf-8','replace')+p.stdout.decode('utf-8','replace'))
    try:return json.loads(p.stdout)
    except ValueError:
        body=p.stdout.decode('utf-8','replace')
        return dict(denied='결제 요청을 확인할 수 없습니다' in body,body=body)
def sql(q):return call('sql',sql=q)
def check(name,value):
    RESULTS.append(dict(case=name,passed=bool(value)));print(('PASS ' if value else 'FAIL ')+name,flush=True)
    if not value:raise AssertionError(name)
def http(path,sid=None,data=None):
    headers={'Origin':BASE,'Referer':BASE+'/shop/orderform.php','User-Agent':'Local-Order-State-Test'}
    if sid:headers['Cookie']='PHPSESSID='+sid
    req=urllib.request.Request(BASE+path,headers=headers,data=None if data is None else urllib.parse.urlencode(data).encode())
    opener=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(CookieJar()))
    with opener.open(req,timeout=20) as r:return r.read().decode('utf-8','replace'),dict(r.headers)
def make(pg='toss',sid=None,member='',order=None,checkout=None):
    oid=order or str(time.time_ns()//1000);cart=str(int(oid)+1);sid=sid or secrets.token_hex(16)
    sql("UPDATE g5_shop_default SET de_pg_service='%s',de_card_test=1"%pg)
    state=dict(ss_order_id=oid,ss_cart_id=cart,ss_cart_direct=cart,ss_direct='')
    if checkout: state=checkout
    call('session',sid=sid,data=state)
    fields=dict(od_name='STATE_FAKE_BUYER',od_pwd='StateFakePassword!',od_email='fake@example.invalid',od_tel='000-0000',od_addr1='FICTIONAL_ADDRESS',od_settle_case='신용카드',amountValue='1000',good_mny='1000',orderId=oid,unknown_sensitive='SHOULD_NOT_BE_STORED')
    body,h=http('/shop/ajax.orderdatasave.php',sid,fields)
    if body or 'X-G5-Order-State' not in h:raise AssertionError('save failed '+body[-500:])
    sql("INSERT INTO g5_shop_cart SET od_id='%s',ct_select=1,ct_status='쇼핑',ct_time=NOW()"%cart)
    return oid,sid,h['X-G5-Order-State'],cart
def return_page(oid,token=None,sid=None,mobile=False,**params):
    q=dict(orderId=oid,paymentKey='test_key',amount='1000');q.update(params)
    if token:q['g5_order_state']=token
    return http(('/mobile' if mobile else '')+'/shop/toss/returnurl.php?'+urllib.parse.urlencode(q),sid)[0]

def main():
    sql('CREATE TABLE IF NOT EXISTS g5_order_test_pg (od_id varchar(20) PRIMARY KEY, approvals int, queries int, data mediumtext) ENGINE=InnoDB')
    check('new installation contains InnoDB state table',sql("SHOW TABLE STATUS LIKE 'g5_shop_order_access'")[0]['Engine']=='InnoDB')
    check('existing migration skips installed table',call('migration')[0].get('skipped') is True)
    migration=call('migration-sandbox')
    check('upgrade schema equals fresh install',migration['same_schema'])
    check('migration repeat is idempotent',migration['repeat_skipped'])
    check('migration skips shopless installations',migration['no_shop_skipped'])
    for mobile in [False,True]:
        oid,sid,token,cart=make()
        label='mobile' if mobile else 'pc'
        b=return_page(oid,token=token,mobile=mobile)
        check(label+' cookieless token return','STATE_FAKE_BUYER' in b and 'name="g5_order_state"' in b and 'StateFakePassword!' not in b)
        check(label+' no token denied','STATE_FAKE_BUYER' not in return_page(oid,mobile=mobile))
        bad=token[:-1]+('a' if token[-1]!='a' else 'b')
        check(label+' wrong token denied','STATE_FAKE_BUYER' not in return_page(oid,token=bad,mobile=mobile))
        oid2,sid2,token2,c2=make()
        check(label+' other order token denied','STATE_FAKE_BUYER' not in return_page(oid,token=token2,mobile=mobile))
        check(label+' other session denied','STATE_FAKE_BUYER' not in return_page(oid,sid=sid2,mobile=mobile))
        check(label+' wrong amount denied','STATE_FAKE_BUYER' not in return_page(oid,token=token,mobile=mobile,amount='999'))
        check(label+' changed payment key denied','STATE_FAKE_BUYER' not in return_page(oid,token=token,mobile=mobile,paymentKey='other_key'))
        data=call('load',id=oid,pg='toss',request={'g5_order_state':token})
        check(label+' whitelist removes password and unknown fields','od_pwd' not in data and 'unknown_sensitive' not in data)
        state=sql("SELECT state_json,token_hash FROM g5_shop_order_access WHERE od_id='%s'"%oid)[0]
        check(label+' token stored as hash',state['token_hash']==hashlib.sha256(token.encode()).hexdigest() and token not in state['state_json'])
        prepared=call('prepare',request={'g5_order_state':token},post={'od_name':'ATTACKER','od_temp_point':'999999'})
        check(label+' final business fields restored from server',prepared['post']['od_name']=='STATE_FAKE_BUYER' and prepared['post']['od_temp_point']=='')
    oid,sid,token,cart=make(pg='kcp')
    post=dict(ordr_idxx=oid,enc_data='fixture',enc_info='fixture',tran_cd='fixture')
    b,h=http('/mobile/shop/kcp/order_approval_form.php?'+urllib.parse.urlencode(dict(g5_order_state=token)),data=post)
    check('KCP cookieless POST bypasses cookie loop only after state check','STATE_FAKE_BUYER' in b and 'name="g5_order_state"' in b)
    check('KCP Ret_URL carries token','order_approval_form.php?g5_order_state='+token in b)
    bad=token[:-1]+('a' if token[-1]!='a' else 'b')
    b,h=http('/mobile/shop/kcp/order_approval_form.php?'+urllib.parse.urlencode(dict(g5_order_state=bad)),data=post)
    check('KCP forged token cannot use cookie-loop exception','STATE_FAKE_BUYER' not in b and '결제 요청을 확인할 수 없습니다' in b)
    # 한 브라우저의 두 탭: 저장 시에도 서버의 주문별 체크아웃 nonce를 대조한다.
    sid=secrets.token_hex(16);a=str(time.time_ns()//1000);b=str(int(a)+10);ca=str(int(a)+1);cb=str(int(b)+1)
    call('session',sid=sid,data={'ss_order_id':a,'ss_cart_id':ca,'ss_direct':''})
    html=call('checkout',sid=sid,id=a);nonce=re.search('name="g5_checkout_nonce" value="([a-f0-9]+)"',html).group(1)
    session=call('session-get',sid=sid);session.update(ss_order_id=b,ss_cart_id=cb);call('session',sid=sid,data=session)
    body,h=http('/shop/ajax.orderdatasave.php',sid,dict(g5_checkout_id=a,g5_checkout_nonce=nonce,od_name='TAB_A',od_pwd='test',amountValue='1000',od_settle_case='신용카드'))
    check('tab A saves its registered order after tab B changes session',body=='' and h.get('X-G5-Order-State','').startswith(a+'.'))
    token_a=h['X-G5-Order-State'];session=call('session-get',sid=sid);session.update(ss_order_id=b,ss_cart_id=cb);call('session',sid=sid,data=session)
    data=call('load',sid=sid,id=a,pg='kcp',request={'g5_order_state':token_a})
    check('tab A return restores its own cart',data.get('od_name')=='TAB_A' and call('session-get',sid=sid)['ss_cart_id']==ca)
    # 병렬 별도 PHP 프로세스/DB 연결: 요청당 세션 잠금만으로 통과하는 검사가 아니다.
    oid,sid,token,cart=make()
    def approve(mode='normal'):return call('approve',id=oid,request={'g5_order_state':token},mode=mode)
    with concurrent.futures.ThreadPoolExecutor(max_workers=3) as pool:
        out=list(pool.map(lambda _:approve(),range(3)))
    ledger=sql("SELECT approvals,queries FROM g5_order_test_pg WHERE od_id='%s'"%oid)[0]
    check('three concurrent returns approve once',all(x is True for x in out) and int(ledger['approvals'])==1 and int(ledger['queries'])==2)
    oid,sid,token,cart=make()
    first=approve('timeout');second=approve()
    ledger=sql("SELECT approvals,queries FROM g5_order_test_pg WHERE od_id='%s'"%oid)[0]
    check('lost approval response recovered by query without reapproval',first.get('denied') and second is True and int(ledger['approvals'])==1 and int(ledger['queries'])==1)
    reconciled=call('reconcile-toss',id=oid)
    check('operator Toss reconciliation resumes verified approval',reconciled['status']=='approved')
    sql("UPDATE g5_shop_order_access SET status='finalizing' WHERE od_id='%s'"%oid)
    check('crash before order insert can resume approved transaction',approve() is True)
    sql("UPDATE g5_shop_order_access SET status='finalizing' WHERE od_id='%s'"%oid)
    sql("INSERT INTO g5_shop_order SET od_id='%s',od_tno='test_key',od_memo='',od_shop_memo='',od_cash_info=''"%oid)
    check('partial order insertion is held for reconciliation',approve().get('denied') is True)
    oid,sid,token,cart=make();approve()
    cancelled=dict(orderId=oid,paymentKey='test_key',totalAmount=1000,status='CANCELED',method='카드')
    sql("UPDATE g5_order_test_pg SET data='%s' WHERE od_id='%s'"%(json.dumps(cancelled),oid))
    check('cancelled PG payment cannot replay cached approval',approve().get('denied') is True)
    oid,sid,token,cart=make();approve('timeout')
    pending=dict(orderId=oid,paymentKey='test_key',totalAmount=1000,status='IN_PROGRESS',method='카드')
    sql("UPDATE g5_order_test_pg SET data='%s' WHERE od_id='%s'"%(json.dumps(pending),oid))
    check('PG-confirmed unapproved request retries with stable identity',approve() is True)
    oid,sid,token,cart=make();approve('timeout')
    sql("DELETE FROM g5_order_test_pg WHERE od_id='%s'"%oid)
    check('failed PG lookup does not blindly confirm',approve().get('denied') is True and not sql("SELECT od_id FROM g5_order_test_pg WHERE od_id='%s'"%oid))
    for status in ['approving','approved','finalizing','unknown','cancel_pending']:
        x,_,t,_=make();sql("UPDATE g5_shop_order_access SET status='%s',expires=1,updated_at=1 WHERE od_id='%s'"%(status,x))
        call('cleanup',apply=True)
        check('cleanup preserves '+status,bool(sql("SELECT od_id FROM g5_shop_order_data WHERE od_id='%s'"%x)))
    x,_,t,_=make();sql("UPDATE g5_shop_order_access SET expires=1,updated_at=1 WHERE od_id='%s'"%x)
    call('cleanup',apply=False);check('cleanup dry run retains rows',bool(sql("SELECT od_id FROM g5_shop_order_data WHERE od_id='%s'"%x)))
    call('cleanup',apply=True);check('expired never-approved data purged',not sql("SELECT od_id FROM g5_shop_order_data WHERE od_id='%s'"%x))
    x,_,t,_=make();call('complete',id=x)
    state=sql("SELECT token_hash,state_json,response_json,status FROM g5_shop_order_access WHERE od_id='%s'"%x)[0]
    check('completion erases bearer and password state',state['status']=='completed' and not state['token_hash'] and not state['state_json'] and not state['response_json'])
    check('completed token is consumed',call('load',id=x,pg='toss',request={'g5_order_state':t}).get('denied') is True)
    x,_,t,_=make();sql("DELETE FROM g5_shop_order_access WHERE od_id='%s'"%x)
    check('legacy orderId alone cannot issue token',call('load',id=x,pg='toss').get('denied') is True)
    q=call('quarantine',id=x);check('legacy quarantine issues no token',q.get('token_issued') is False)
    check('quarantined legacy cannot be resurrected by old token',call('load',id=x,pg='toss',request={'g5_order_state':t}).get('denied') is True)

if __name__=='__main__':
    try:main()
    finally:
        output=Path(os.environ.get('G5_ORDER_TEST_RESULTS','shop-order-state-results.json'))
        output.write_text(json.dumps(RESULTS,ensure_ascii=False,indent=2),encoding='utf-8')
    print('PASS',len(RESULTS),'checks')
