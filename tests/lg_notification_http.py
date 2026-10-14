#!/usr/bin/env python3
"""격리 MySQL에서 LG 실제 콜백과 MyISAM/InnoDB 재통보를 검증한다.
실행: python3 tests/lg_notification_http.py 33443
localhost의 지정 포트에 root/빈 비밀번호로 접속하여 임시 DB만 만들고 삭제한다.
공통 초기화·발신 IP·SQL 실패만 대역이며 SQL 저장과 실제 콜백은 그대로 실행한다.
"""
import hashlib
import concurrent.futures
import json
import os
from pathlib import Path
import re
import signal
import socket
import subprocess
import sys
import tempfile
import time
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
PORT = int(sys.argv[1])
DB = 'lg56_test_' + str(time.time_ns())
checks = 0

def mysql(sql, database=True):
    command = ['mysql', '--no-defaults', '-h127.0.0.1', '-P' + str(PORT), '-uroot', '-N', '-B']
    if database:
        command.append(DB)
    return subprocess.check_output(command, input="SET sql_mode='NO_ENGINE_SUBSTITUTION';" + sql, text=True).strip()

def check(value, message):
    global checks
    if not value:
        raise AssertionError(message)
    checks += 1

mysql('CREATE DATABASE `' + DB + '`', False)
server = None
try:
    schema = (ROOT / 'install/gnuboard5shop.sql').read_text()
    for table in ['order', 'personalpay', 'cart', 'default', 'lg_noti']:
        statement = re.search(r'CREATE TABLE(?: IF NOT EXISTS)? `g5_shop_' + table + r'` \(.*?;', schema, re.S).group()
        mysql(statement)
    with tempfile.TemporaryDirectory(prefix='lg56-http-') as temp:
        directory = Path(temp)
        fixture = directory / 'fixture.json'
        bootstrap = r'''<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
define('_GNUBOARD_', true);
define('G5_SHOP_TABLE_PREFIX', 'g5_shop_');
define('G5_TABLE_PREFIX', 'g5_');
define('G5_LIB_PATH', ROOT_PATH.'/lib');
mysqli_report(MYSQLI_REPORT_OFF);
$db = new mysqli('127.0.0.1', 'root', '', TEST_DB, TEST_PORT);
$db->set_charset('utf8mb4');
$db->query("SET sql_mode='NO_ENGINE_SUBSTITUTION'");
$fixture = json_decode(file_get_contents(__DIR__.'/fixture.json'), true);
$default = array('de_card_test' => $fixture['test'], 'de_pg_service' => $fixture['pg']);
$config = array('cf_lg_mid' => $fixture['mid'], 'cf_lg_mert_key' => $fixture['key']);
if (isset($fixture['ip'])) $_SERVER['REMOTE_ADDR'] = $fixture['ip'];
$g5 = array('g5_shop_order_table' => 'g5_shop_order', 'g5_shop_personalpay_table' => 'g5_shop_personalpay',
    'g5_shop_cart_table' => 'g5_shop_cart', 'g5_shop_default_table' => 'g5_shop_default');
$writes = 0;
function sql_query($sql, $error = false) {
    global $db, $writes, $fixture;
    $write = preg_match('/^(UPDATE|INSERT) /', $sql);
    if ($write) $writes++;
    if ($write && isset($fixture['fail_before']) && $writes === $fixture['fail_before']) return false;
    $result = $db->query($sql);
    if ($write && isset($fixture['fail_after']) && $writes === $fixture['fail_after']) return false;
    return $result;
}
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_num_rows($result) { return $result->num_rows; }
function sql_escape_string($value) { global $db; return $db->real_escape_string($value); }
function sql_real_escape_string($value) { return sql_escape_string($value); }
function sql_error_info() { global $db; return $db->error; }
'''.replace('ROOT_PATH', repr(str(ROOT))).replace('TEST_DB', repr(DB)).replace('TEST_PORT', str(PORT))
        (directory / '_common.php').write_text(bootstrap)
        (directory / 'router.php').write_text("<?php chdir(__DIR__); require " + repr(str(ROOT / 'shop/settle_lg_common.php')) + ';')
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            http_port = sock.getsockname()[1]
        env = dict(os.environ, PHP_CLI_SERVER_WORKERS='4')
        log = open(directory / 'php.log', 'w+')
        server = subprocess.Popen(['php', '-S', '127.0.0.1:' + str(http_port), str(directory / 'router.php')], env=env,
                                  stdout=log, stderr=log, start_new_session=True)
        for _ in range(50):
            try:
                with socket.create_connection(('127.0.0.1', http_port), timeout=.1):
                    break
            except OSError:
                time.sleep(.1)

        def config(**changes):
            settings = dict(test=1, ip='13.124.18.147', pg='lg', mid='fixture', key='LOCAL-LG-FIXTURE-KEY')
            settings.update(changes)
            fixture.write_text(json.dumps(settings))

        def request(data=None, method='POST', headers=None, signing_key='LOCAL-LG-FIXTURE-KEY'):
            payload = dict(LGD_MID='tsi_fixture', LGD_TID='fixture_trade_1', LGD_OID='1001',
                LGD_AMOUNT='10000', LGD_RESPCODE='0000', LGD_TIMESTAMP='20260918120000',
                LGD_PAYDATE='20260918120000', LGD_PAYTYPE='SC0040', LGD_CASFLAG='I',
                LGD_CASSEQNO='001', LGD_ACCOUNTNUM='123456789', LGD_CASTAMOUNT='10000', LGD_CASCAMOUNT='10000')
            if data:
                for key, value in data.items():
                    if value is None: payload.pop(key, None)
                    else: payload[key] = value
            if 'LGD_HASHDATA' not in payload and 'LGD_HASHDATA[]' not in payload:
                payload['LGD_HASHDATA'] = hashlib.md5((''.join(payload.get(k, '') for k in
                    ['LGD_MID', 'LGD_OID', 'LGD_AMOUNT', 'LGD_RESPCODE', 'LGD_TIMESTAMP']) + signing_key).encode()).hexdigest()
            req = urllib.request.Request('http://127.0.0.1:' + str(http_port),
                data=urllib.parse.urlencode(payload).encode() if method == 'POST' else None, method=method, headers=headers or {})
            return urllib.request.urlopen(req, timeout=30).read() == b'OK'

        def seed(personal=False, linked=True, mobile=0, mid='tsi_fixture'):
            mysql('TRUNCATE g5_shop_lg_noti; TRUNCATE g5_shop_personalpay; TRUNCATE g5_shop_cart; TRUNCATE g5_shop_order;')
            if linked or not personal:
                mysql("INSERT INTO g5_shop_order (od_id,od_pg,od_tno,od_lg_mid,od_settle_case,od_test,od_mobile,od_cart_price,od_misu,od_status,od_bank_account) "
                      "VALUES (1001,'lg','fixture_trade_1','" + mid + "','가상계좌'," + ('1' if mid.startswith('t') else '0') + "," + str(mobile) + ",10000,10000,'주문','은행 123456789 예금주');"
                      "INSERT INTO g5_shop_cart (ct_id,od_id,ct_status) VALUES (1,1001,'주문'),(2,1001,'주문');")
            if personal:
                mysql("INSERT INTO g5_shop_personalpay (pp_id,od_id,pp_use,pp_pg,pp_tno,pp_lg_mid,pp_settle_case,pp_price,pp_bank_account) "
                      "VALUES (2001," + ('1001' if linked else '0') + ",1,'lg','fixture_trade_1','" + mid + "','가상계좌',4000,'은행 123456789 예금주');")

        def state():
            return mysql('SELECT od_receipt_price,od_misu,od_status FROM g5_shop_order; SELECT pp_receipt_price FROM g5_shop_personalpay; SELECT ct_status FROM g5_shop_cart ORDER BY ct_id;')

        def pp_request(extra=None):
            payload = dict(LGD_OID='2001', LGD_AMOUNT='4000', LGD_CASTAMOUNT='4000', LGD_CASCAMOUNT='4000')
            payload.update(extra or {})
            return request(payload)

        def cancel(extra=None):
            payload = dict(LGD_CASFLAG='C', LGD_CASTAMOUNT='0')
            payload.update(extra or {})
            return request(payload)

        # 신규/기존 스키마, 재실행과 쇼핑몰 미설치 분기를 실제 실행기로 확인한다.
        config()
        expected = mysql('SHOW CREATE TABLE g5_shop_lg_noti')
        mysql('ALTER TABLE g5_shop_order DROP od_lg_mid; ALTER TABLE g5_shop_personalpay DROP pp_lg_mid; DROP TABLE g5_shop_lg_noti;')
        migration_php = "<?php require __DIR__.'/_common.php'; require G5_LIB_PATH.'/migration.lib.php'; $m=g5_migration_parse_file(" + repr(str(ROOT / 'migrations/20260918_001_lg_notification.sql')) + "); if(isset($m['error'])) exit(1); foreach($m['statements'] as $s) { if(!g5_migration_should_run_statement($s['conditions'])) continue; $r=g5_migration_execute_statement($s); if($r['error']) {fwrite(STDERR,$r['error']); exit(1);} }"
        (directory / 'migration.php').write_text(migration_php)
        subprocess.check_call(['php', str(directory / 'migration.php')])
        subprocess.check_call(['php', str(directory / 'migration.php')])
        check(mysql('SHOW CREATE TABLE g5_shop_lg_noti') == expected, '새 설치와 기존 업그레이드 동일 스키마/재실행')
        check(mysql("SHOW COLUMNS FROM g5_shop_order LIKE 'od_lg_mid'") != '', '주문 MID 마이그레이션')
        check(mysql("SHOW COLUMNS FROM g5_shop_personalpay LIKE 'pp_lg_mid'") != '', '개인결제 MID 마이그레이션')
        for table in ['order', 'personalpay', 'default', 'lg_noti']:
            mysql('RENAME TABLE g5_shop_' + table + ' TO saved_' + table)
        subprocess.check_call(['php', str(directory / 'migration.php')])
        check(mysql("SHOW TABLES LIKE 'g5_shop_lg_noti'") == '', '쇼핑몰 미설치 건너뜀')
        for table in ['order', 'personalpay', 'default', 'lg_noti']:
            mysql('RENAME TABLE saved_' + table + ' TO g5_shop_' + table)

        for engine in ['MyISAM', 'InnoDB']:
            print('검증:', engine, flush=True)
            for table in ['order', 'personalpay', 'cart', 'lg_noti']:
                mysql('ALTER TABLE g5_shop_' + table + ' ENGINE=' + engine)
            seed()
            before = state()
            config(mid='', key='', pg='kcp')
            check(not request({'LGD_MID':'', 'LGD_TID':''}, signing_key='') and state() == before, '빈 MID/키/TID 제보 조건 차단')
            for settings in [dict(mid=''), dict(key=''), dict(key='   '), dict(key='WRONG'), dict(mid='other')]:
                config(**settings)
                check(not request() and state() == before, '미설정/다른 상점키 차단')
            for test in [0, 1]:
                config(test=test, ip='192.0.2.56')
                check(not request(headers={'X-Forwarded-For':'13.124.18.147', 'X-Real-IP':'13.124.18.147'}), '전달 헤더/IP 위조 거부')
            config()
            check(not request(method='GET'), 'GET 거부')
            for key in ['LGD_MID','LGD_TID','LGD_OID','LGD_AMOUNT','LGD_RESPCODE','LGD_TIMESTAMP','LGD_PAYDATE','LGD_PAYTYPE',
                        'LGD_CASFLAG','LGD_CASSEQNO','LGD_ACCOUNTNUM','LGD_CASTAMOUNT','LGD_CASCAMOUNT']:
                for value in ['', None, 'x\n']:
                    check(not request({key:value}), key + ' 누락/형식 거부')
                check(not request({key:None, key+'[]':'1'}), key + ' 배열 거부')
            for value in ['', '0', '0e123456789012345678901234567890', 'f'*32, 'a'*31, 'a'*33]:
                check(not request({'LGD_HASHDATA':value}), '해시 느슨한 비교/형식 거부')
            check(not request({'LGD_HASHDATA[]':'1', 'LGD_HASHDATA':None}), '해시 배열 거부')
            for amount in ['0','-1','1e4','10000.0','2147483648','999999999999','9999']:
                check(not request({'LGD_AMOUNT':amount}), '금액 범위/불일치 거부')
            for data in [{'LGD_MID':'si_fixture'}, {'LGD_TID':'other'}, {'LGD_OID':'9999'}, {'LGD_ACCOUNTNUM':'23456789'},
                         {'LGD_TIMESTAMP':'20260229120000'}, {'LGD_PAYDATE':'20260918240000'}, {'LGD_RESPCODE':'9999'},
                         {'LGD_PAYTYPE':'SC0010'}, {'LGD_CASSEQNO':'000'}, {'LGD_CASTAMOUNT':'5000'}, {'LGD_CASCAMOUNT':'5000'}]:
                check(not request(data), '거래/날짜/누적금액/부분입금 거부')
            check(state() == before, '모든 거부 요청 업무 DB 불변')
            check(mysql('SELECT COUNT(*) FROM g5_shop_lg_noti') == '0', '거부 요청 이력 생성 없음')
            for field, value in [('od_pg','kcp'),('od_settle_case','무통장'),('od_status','완료'),('od_status','취소'),('od_test','0'),
                                 ('od_lg_mid',''),('od_lg_mid','tsi_other'),('od_tno',''),('od_receipt_price','1'),('od_misu','1')]:
                seed()
                mysql("UPDATE g5_shop_order SET " + field + "='" + value + "'")
                before = state()
                check(not request() and state() == before, field + ' 바인딩/금액/상태 거부')
            for mobile in [0,1]:
                seed(mobile=mobile)
                check(request({'LGD_CASFLAG':'R','LGD_CASSEQNO':'000','LGD_CASTAMOUNT':'0','LGD_CASCAMOUNT':'0'}), '정상 발급 통보 수신')
                check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '0', '발급 통보 미입금 유지')
                check(request(), 'PC/모바일 정상 전액 입금')
                after = state()
                check(after == '10000\t0\t입금\n입금\n입금', '입금 금액/미수금/장바구니')
                check(request() and state() == after, '중복 입금 멱등성')
                check(not request({'LGD_AMOUNT':'9999','LGD_CASTAMOUNT':'9999','LGD_CASCAMOUNT':'9999'}) and state() == after, '동일 이벤트 업무값 변조 거부')
                mysql("UPDATE g5_shop_order SET od_status='완료'")
                check(request(), '완료 후 기처리 재전송 수신')
            seed()
            with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
                check(all(pool.map(lambda _: request(), range(8))), '동시 입금 통보')
            check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '10000', '동시 한 번 반영')
            check(cancel(), '입금과 같은 순번 은행 취소')
            after = state()
            check(after == '0\t10000\t주문\n주문\n주문', '은행 취소 금액/상태 복구')
            check(cancel() and request() and state() == after, '취소 이후 동일 입금 재전송으로 재입금하지 않음')
            check(request({'LGD_CASSEQNO':'002'}), '취소 후 증가한 순번 새 입금')
            check(cancel({'LGD_CASSEQNO':'003'}), '새 순번 은행 취소')
            check(request({'LGD_CASSEQNO':'002'}) and mysql('SELECT od_receipt_price FROM g5_shop_order') == '0', '과거 입금 뒤늦은 재전송 무변경')
            seed()
            check(cancel({'LGD_CASSEQNO':'002'}) and request(), '취소 선도착 후 과거 입금 차단')
            check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '0', '취소 선도착 미입금 유지')
            seed()
            check(request(), '준비 상태 은행 취소 준비')
            mysql("UPDATE g5_shop_order SET od_status='준비'; UPDATE g5_shop_cart SET ct_status='준비'")
            check(cancel() and state() == '0\t10000\t주문\n주문\n주문', '배송 준비 취소 후 출고 대기 해제')
            seed()
            check(request(), '배송 후 취소 준비')
            mysql("UPDATE g5_shop_order SET od_status='배송'; UPDATE g5_shop_cart SET ct_status='배송'")
            check(cancel() and state() == '0\t10000\t배송\n배송\n배송', '배송 이력 보존/미수금 복원')
            for field, value in [('pp_pg','kcp'),('pp_settle_case','무통장'),('pp_use','0'),('pp_lg_mid',''),('pp_lg_mid','si_fixture'),
                                 ('pp_tno',''),('pp_price','3999'),('od_id','9999')]:
                seed(personal=True)
                mysql("UPDATE g5_shop_personalpay SET " + field + "='" + value + "'")
                before = state()
                check(not pp_request() and state() == before, '개인결제 ' + field + ' 거부')
            seed(personal=True)
            with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
                check(all(pool.map(lambda _: pp_request(), range(8))), '개인결제 동시 입금')
            check(state() == '4000\t6000\t주문\n4000\n주문\n주문', '연결 주문 청구액 한 번 가산')
            check(not pp_request({'LGD_CASSEQNO':'002'}), '다른 순번 중복 입금 차단')
            check(pp_request({'LGD_CASFLAG':'C','LGD_CASTAMOUNT':'0'}), '개인결제 은행 취소')
            check(state() == '0\t10000\t주문\n0\n주문\n주문', '개인결제/연결 주문 취소액 한 번 차감')
            seed(personal=True, linked=False)
            check(pp_request() and pp_request() and pp_request({'LGD_CASFLAG':'C','LGD_CASTAMOUNT':'0'}), '독립 개인결제 입금/중복/취소')
            # 입금 후 관리자가 연결 주문을 변경해도 원래 입금 이력과 결합한다.
            for linked, target, other_receipt in [(True,1002,4000), (True,1002,6000), (True,0,0), (False,1002,4000)]:
                seed(personal=True, linked=linked)
                check(pp_request(), '연결 변경 검증용 개인결제 입금')
                if target:
                    mysql("INSERT INTO g5_shop_order (od_id,od_pg,od_test,od_cart_price,od_receipt_price,od_misu,od_status) "
                          "VALUES (1002,'kcp',1,10000," + str(other_receipt) + "," + str(10000-other_receipt) + ",'주문');"
                          "INSERT INTO g5_shop_cart (ct_id,od_id,ct_status) VALUES (3,1002,'주문');")
                mysql('UPDATE g5_shop_personalpay SET od_id=' + str(target))
                before = state()
                events = mysql('SELECT COUNT(*) FROM g5_shop_lg_noti')
                check(not pp_request({'LGD_CASFLAG':'C','LGD_CASTAMOUNT':'0'}), '입금 이후 연결 변경/해제/추가 취소 거부')
                check(state() == before and mysql('SELECT COUNT(*) FROM g5_shop_lg_noti') == events, '충돌 시 모든 주문/개인결제/장바구니/이력 보존')
                mysql('UPDATE g5_shop_personalpay SET od_id=' + ('1001' if linked else '0'))
                check(pp_request({'LGD_CASFLAG':'C','LGD_CASTAMOUNT':'0'}), '원래 연결 복원 후 취소 재통보')
                check(mysql('SELECT pp_receipt_price FROM g5_shop_personalpay') == '0', '복원 후 개인결제 취소액 반영')
                if linked:
                    check(mysql('SELECT od_receipt_price FROM g5_shop_order WHERE od_id=1001') == '0', '원래 주문만 차감')
                if target:
                    check(mysql('SELECT od_receipt_price FROM g5_shop_order WHERE od_id=1002') == str(other_receipt), '다른 주문 수납액 보존')
            for linked in [False,True]:
                seed(personal=True, linked=linked)
                check(pp_request(), '사용 중지 검증용 개인결제 입금')
                mysql('UPDATE g5_shop_personalpay SET pp_use=0')
                with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
                    check(all(pool.map(lambda _: pp_request({'LGD_CASFLAG':'C','LGD_CASTAMOUNT':'0'}), range(8))), '사용 중지 후 동시 은행 취소/재통보')
                check(mysql('SELECT pp_receipt_price,pp_use FROM g5_shop_personalpay') == '0\t0', '사용 중지 유지 및 취소 한 번 차감')
                if linked:
                    check(mysql('SELECT od_receipt_price,od_misu FROM g5_shop_order') == '0\t10000', '사용 중지 후 연결 주문 복원')
                before = state()
                check(not pp_request({'LGD_CASSEQNO':'002'}) and state() == before, '사용 중지 상태 신규 입금 계속 거부')
            # 각 쓰기 전 실패와 성공 후 응답 유실. 실제 SQL은 그대로 실행한다.
            for personal in [False,True]:
                for mode in ['fail_before','fail_after']:
                    for write_no in range(1,7 if personal else 6):
                        seed(personal=personal)
                        if personal: mysql('UPDATE g5_shop_personalpay SET pp_price=10000')
                        config(**{mode:write_no})
                        payload = {'LGD_OID':'2001'} if personal else {}
                        check(not request(payload), '중간 실패 시 실패 응답')
                        config()
                        check(request(payload), '재통보로 중간 실패 복구')
                        check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '10000', '복구 중복 가산 없음')
            for personal in [False,True]:
                for mode in ['fail_before','fail_after']:
                    for write_no in range(1,7 if personal else 6):
                        seed(personal=personal)
                        if personal: mysql('UPDATE g5_shop_personalpay SET pp_price=10000')
                        config()
                        payload = {'LGD_OID':'2001'} if personal else {}
                        check(request(payload), '취소 복구 검증용 입금')
                        config(**{mode:write_no})
                        check(not cancel(payload), '취소 중간 실패 응답')
                        config()
                        check(cancel(payload), '재통보로 취소 중간 실패 복구')
                        check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '0', '취소 복구 중복 차감 없음')
            seed()
            mysql("CREATE TRIGGER lg56_fail BEFORE UPDATE ON g5_shop_order FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test failure'")
            check(not request(), '실제 DB 오류 실패 응답')
            mysql('DROP TRIGGER lg56_fail')
            check(request(), '실제 DB 오류 후 재처리')
            seed(personal=True)
            config(fail_before=3)
            check(not pp_request(), '개인결제 반영 뒤 연결 주문 실패')
            mysql('UPDATE g5_shop_order SET od_receipt_price=1')
            before = state()
            config()
            check(not pp_request() and state() == before, '외부 수정 충돌 시 과거 계획 덮어쓰기 차단')
            seed()
            before = state()
            mysql('RENAME TABLE g5_shop_lg_noti TO lg56_missing')
            check(not request() and state() == before, '이력 테이블 누락 차단')
            mysql('RENAME TABLE lg56_missing TO g5_shop_lg_noti')
            seed()
            mysql('ALTER TABLE g5_shop_order DROP od_lg_mid')
            before = state()
            check(not request() and state() == before, 'MID 스키마 누락 차단')
            subprocess.check_call(['php', str(directory / 'migration.php')])
            for ip in ['13.124.18.147','13.124.108.35','3.36.173.151','3.38.81.32','115.92.221.121',
                       '115.92.221.122','115.92.221.123','115.92.221.125','115.92.221.126','115.92.221.127']:
                for mid in ['si_fixture','tsi_fixture']:
                    seed(mid=mid)
                    config(ip=ip, pg='kcp', test=0 if mid.startswith('t') else 1)
                    check(request({'LGD_MID':mid}), 'PG/테스트 설정 전환 후 기존 거래 통보')
            config()
        log.flush(); log.seek(0)
        output = log.read()
        check('PHP Warning' not in output and 'PHP Fatal' not in output, 'PHP 경고/치명 오류 없음')
        print('PASS:', checks, 'checks (HTTP, MySQL, MyISAM/InnoDB)', flush=True)
finally:
    if server:
        os.killpg(server.pid, signal.SIGTERM)
        server.wait(timeout=10)
    mysql('DROP DATABASE `' + DB + '`', False)
