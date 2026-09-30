"""격리 설치본의 실제 PHP/DB/HTTP로 이니시스 서명 흐름을 검증한다. PG 호출 없음."""
import base64
import hashlib
import json
import os
from pathlib import Path
import secrets
import subprocess
import time
import urllib.request
import urllib.parse

config_path = Path(os.environ['G5_ORDER_TEST_CONFIG'])
c = json.loads(config_path.read_text())
root = Path(c['root'])
assert (root/'data/.order-state-test').read_text().strip() == 'KVE-2026-2140-local-test'
base = 'http://127.0.0.1:' + str(c['port'])
results = []

def fixture(action, **args):
    proc = subprocess.run([c['php'], '-c', c['ini'], str(root/'tests/shop_order_state_fixture.php')],
        input=json.dumps(dict(action=action, **args)), text=True, capture_output=True, check=True)
    return json.loads(proc.stdout)

def sql(query):
    result = fixture('sql', sql=query)
    if isinstance(result, dict): assert result.get('ok'), 'SQL 실패'
    return result

def post(path, sid, fields):
    request = urllib.request.Request(base+path, data=urllib.parse.urlencode(fields).encode(), headers={
        'Origin': base, 'Referer': base+'/shop/orderform.php', 'Cookie': 'PHPSESSID='+sid,
        'User-Agent': 'Inicis-Isolated-Test'})
    with urllib.request.urlopen(request, timeout=15) as response:
        return response.read().decode(), dict(response.headers)

def check(name, ok):
    results.append(dict(case=name, passed=bool(ok)))
    print(('PASS ' if ok else 'FAIL ')+name, flush=True)
    assert ok, name

def sign(oid, sid, mobile, amount='1200'):
    body, _ = post(('/mobile' if mobile else '')+'/shop/inicis/makesignature.php', sid, dict(oid=oid, price=amount))
    try: return json.loads(body)
    except ValueError: return dict(error='non-json denial')

def make(personal=False, method='신용카드'):
    oid = str(time.time_ns()//1000)
    sid = secrets.token_hex(16)
    state = dict(ss_order_id=oid, ss_cart_id=str(int(oid)+1), ss_direct='')
    fields = dict(od_name='INICIS_FAKE_BUYER', od_pwd='FixtureOnly!', od_settle_case=method,
        od_price='1300', od_send_cost='200', od_send_cost2='100', od_send_coupon='50', od_temp_point='350')
    if personal:
        date = '2026-09-30 00:00:00'
        sql("INSERT INTO g5_shop_personalpay SET pp_id='%s',od_id=0,pp_use=1,pp_price=1200,pp_time='%s',pp_content='',pp_shop_memo='',pp_cash_info=''" % (oid,date))
        state.update(ss_personalpay_id=oid, ss_personalpay_hash=hashlib.md5((oid+'1200'+date).encode()).hexdigest())
        fields.update(pp_id=oid, pp_settle_case=method)
    fixture('session', sid=sid, data=state)
    body, headers = post('/shop/ajax.orderdatasave.php', sid, fields)
    check('개인결제 저장' if personal else '주문 저장', body == '' and 'X-G5-Order-State' in headers)
    return oid, sid

# 테스트 전용 DB임을 fixture가 확인한다. 운영 설정/데이터를 읽거나 변경하지 않는다.
sql("UPDATE g5_shop_default SET de_pg_service='inicis',de_inicis_pro_use=0,de_card_test=1,de_escrow_use=0,de_easy_pay_services=''")
for personal in [False, True]:
    oid, sid = make(personal)
    for mobile in [False, True]:
        result = sign(oid,sid,mobile)
        check(('모바일' if mobile else 'PC')+(' 개인' if personal else ' 일반')+' 서명', result.get('error') == '')
        if mobile:
            expected = base64.b64encode(hashlib.sha512(('1200'+oid+result['timestamp']+'3CB8183A4BE283555ACC8363C0360223').encode()).digest()).decode()
            check('실제 HashKey SHA512', result['hash'] == expected)
        else:
            expected = hashlib.sha256(('oid='+oid+'&price=1200&signKey=SU5JTElURV9UUklQTEVERVNfS0VZU1RS&timestamp='+result['timestamp']).encode()).hexdigest()
            check('실제 SignKey SHA256', result['verification'] == expected)
        check('금액 불일치 차단', sign(oid,sid,mobile,'1').get('error') != '')
        check('다른 세션 차단', sign(oid,secrets.token_hex(16),mobile).get('error') != '')
    if not personal:
        sql("UPDATE g5_shop_order_data SET dt_data=CONCAT(dt_data,'X') WHERE od_id='%s'" % oid)
        for mobile in [False,True]: check('저장 데이터 변조 차단',sign(oid,sid,mobile).get('error') != '')

for method in ['lpay','inicis_kakaopay','삼성페이']:
    sql("UPDATE g5_shop_default SET de_pg_service='kcp',de_samsung_pay_use=1,de_inicis_lpay_use=1,de_inicis_kakaopay_use=1,de_easy_pay_services=''")
    oid,sid=make(method=method)
    for mobile in ([True] if method=='삼성페이' else [False,True]):
        check('타 PG 병행 '+method+(' 모바일' if mobile else ' PC'),sign(oid,sid,mobile).get('error')=='')

for pg in ['inicis','kcp']:
    sql("UPDATE g5_shop_default SET de_pg_service='%s',de_easy_pay_use=1,de_easy_pay_services='inicis_configured,inicis_kakaopay',de_inicis_lpay_use=0,de_inicis_kakaopay_use=1" % pg)
    oid,sid=make(method='lpay')
    for mobile in [False,True]:
        check('카카오페이만 활성화 시 L.pay 서명 거부 '+pg+str(mobile),sign(oid,sid,mobile).get('error')!='')

Path(c['base'],'inicis-http-results.json').write_text(json.dumps(results,ensure_ascii=False,indent=2))
print('총 %d건 통과' % len(results))
