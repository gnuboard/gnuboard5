#!/usr/bin/env python3
"""격리 MySQL에서 KCP 실제 콜백과 MyISAM/InnoDB 재통보를 검증한다.
실행: python3 tests/kcp_notification_http.py 33443
localhost의 지정 포트에 root/빈 비밀번호로 접속하여 임시 DB만 만들고 삭제한다.
공통 초기화·발신 IP·SQL 실패만 대역이며 SQL 저장과 실제 콜백은 그대로 실행한다.
"""
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
DB = 'kcp43_test_' + str(time.time_ns())
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
    for table in ['order', 'personalpay', 'cart', 'default', 'kcp_noti']:
        statement = re.search(r'CREATE TABLE(?: IF NOT EXISTS)? `g5_shop_' + table + r'` \(.*?;', schema, re.S).group()
        mysql(statement)
    with tempfile.TemporaryDirectory(prefix='kcp43-http-') as temp:
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
$default = array('de_card_test' => $fixture['test'], 'de_pg_service' => 'kcp');
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
        (directory / 'router.php').write_text("<?php chdir(__DIR__); require " + repr(str(ROOT / 'shop/settle_kcp_common.php')) + ';')
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
            fixture.write_text(json.dumps(dict(test=1, ip='210.122.176.144', **changes)))

        def request(data=None, method='POST', headers=None):
            payload = dict(site_cd='T0000', tno='20260910123456', order_no='1001', tx_cd='TX00',
                           tx_tm='20260910120000', ipgm_mnyx='10000', account='T123456789',
                           noti_id='20260910123456789012', op_cd='50')
            if data:
                for key, value in data.items():
                    if value is None:
                        payload.pop(key, None)
                    else:
                        payload[key] = value
            req = urllib.request.Request('http://127.0.0.1:' + str(http_port),
                data=urllib.parse.urlencode(payload).encode() if method == 'POST' else None,
                method=method, headers=headers or {})
            return 'value="0000"' in urllib.request.urlopen(req, timeout=30).read().decode()

        def seed(personal=False, linked=True, mobile=0, site='T0000'):
            mysql('TRUNCATE g5_shop_kcp_noti; TRUNCATE g5_shop_personalpay; TRUNCATE g5_shop_cart; TRUNCATE g5_shop_order;')
            if linked or not personal:
                mysql("INSERT INTO g5_shop_order (od_id,od_pg,od_tno,od_kcp_site_cd,od_settle_case,od_test,od_mobile,od_cart_price,od_misu,od_status,od_bank_account) "
                      "VALUES (1001,'kcp','20260910123456','" + site + "','가상계좌'," + ('1' if site.startswith('T') else '0') + "," + str(mobile) + ",10000,10000,'주문','은행 T123456789');"
                      "INSERT INTO g5_shop_cart (ct_id,od_id,ct_status) VALUES (1,1001,'주문'),(2,1001,'주문');")
            if personal:
                mysql("INSERT INTO g5_shop_personalpay (pp_id,od_id,pp_use,pp_pg,pp_tno,pp_kcp_site_cd,pp_settle_case,pp_price,pp_bank_account) "
                      "VALUES (2001," + ('1001' if linked else '0') + ",1,'kcp','20260910123456','" + site + "','가상계좌',4000,'은행 T123456789');")

        def state():
            return mysql('SELECT od_receipt_price,od_misu,od_status FROM g5_shop_order; SELECT pp_receipt_price FROM g5_shop_personalpay; SELECT ct_status FROM g5_shop_cart ORDER BY ct_id;')

        def pp_request(extra=None):
            return request(dict(order_no='2001', ipgm_mnyx='4000', **(extra or {})))

        # 실제 마이그레이션 파서/실행기로 기존 스키마와 재실행을 검증한다.
        mysql('ALTER TABLE g5_shop_order DROP od_kcp_site_cd; ALTER TABLE g5_shop_personalpay DROP pp_kcp_site_cd; DROP TABLE g5_shop_kcp_noti;')
        config()
        migration_php = "<?php require __DIR__.'/_common.php'; require G5_LIB_PATH.'/migration.lib.php'; $m=g5_migration_parse_file(" + repr(str(ROOT / 'migrations/20260910_001_kcp_notification.sql')) + "); if(isset($m['error'])) exit(1); foreach($m['statements'] as $s) { if(!g5_migration_should_run_statement($s['conditions'])) continue; $r=g5_migration_execute_statement($s); if($r['error']) {fwrite(STDERR,$r['error']); exit(1);} }"
        (directory / 'migration.php').write_text(migration_php)
        subprocess.check_call(['php', str(directory / 'migration.php')])
        subprocess.check_call(['php', str(directory / 'migration.php')])
        check(mysql("SHOW COLUMNS FROM g5_shop_order LIKE 'od_kcp_site_cd'") != '', '마이그레이션 실행')
        for engine in ['MyISAM', 'InnoDB']:
            for table in ['order', 'personalpay', 'cart', 'kcp_noti']:
                mysql('ALTER TABLE g5_shop_' + table + ' ENGINE=' + engine)
            seed()
            before = state()
            for test in [0, 1]:
                fixture.write_text(json.dumps(dict(test=test, ip='192.0.2.43')))
                check(not request(headers={'X-Forwarded-For': '210.122.176.144'}), '비허용 IP/전달 헤더 거부')
            config()
            check(not request(method='GET'), 'GET 거부')
            for key in ['site_cd', 'tno', 'order_no', 'tx_cd', 'tx_tm', 'ipgm_mnyx', 'account', 'noti_id', 'op_cd']:
                for value in ['', None, 'x\n']:
                    check(not request({key: value}), key + ' 형식 거부')
                check(not request({key: None, key + '[]': '1'}), key + ' 배열 거부')
            for amount in ['0', '-1', '1e4', '10000.0', '2147483648', '999999999999', '9999']:
                check(not request({'ipgm_mnyx': amount}), '잘못된 금액 거부')
            for data in [{'site_cd':'T0007'}, {'tno':'20260910123457'}, {'order_no':'9999'},
                         {'account':'T999'}, {'tx_tm':'20260229120000'}, {'tx_tm':'20260910240000'}, {'op_cd':'51'}]:
                check(not request(data), '거래/날짜 불일치 거부')
            check(state() == before, '거부 요청 DB 불변')
            for field, value in [('od_pg','lg'),('od_settle_case','무통장'),('od_status','완료'),('od_status','취소'),('od_test','0'),('od_kcp_site_cd','')]:
                seed()
                mysql("UPDATE g5_shop_order SET " + field + "='" + value + "'")
                before = state()
                check(not request() and state() == before, field + ' 바인딩/상태 거부')
            for mobile in [0, 1]:
                seed(mobile=mobile)
                check(request(), 'PC/모바일 정상 입금')
                after = state()
                check(after == '10000\t0\t입금\n입금\n입금', '입금 금액/미수금/장바구니')
                check(request() and state() == after, '재전송 멱등성')
                mysql("UPDATE g5_shop_order SET od_status='완료'")
                check(request(), '완료 후 동일 재전송 성공')
            seed()
            with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
                check(all(pool.map(lambda _: request(), range(8))), '동시 입금 통보')
            check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '10000', '동시 입금 한 번 반영')
            check(request({'op_cd':'13'}), '같은 noti_id 망취소')
            after = state()
            check(after == '0\t10000\t주문\n주문\n주문', '망취소 금액/상태 복원')
            check(request({'op_cd':'13'}) and request() and state() == after, '망취소/입금 역순 재전송')
            check(request({'noti_id':'20260910123456789013'}), '망취소 후 새 입금')
            seed()
            check(request({'op_cd':'13'}) and request(), '망취소 선도착 처리')
            check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '0', '선도착 망취소 재입금 방지')
            seed(personal=True)
            for field, value in [('pp_pg','lg'),('pp_settle_case','무통장'),('pp_use','0'),('pp_kcp_site_cd',''),('od_id','9999')]:
                seed(personal=True)
                mysql("UPDATE g5_shop_personalpay SET " + field + "='" + value + "'")
                before = state()
                check(not pp_request() and state() == before, '개인결제 ' + field + ' 거부')
            seed(personal=True)
            with concurrent.futures.ThreadPoolExecutor(max_workers=8) as pool:
                check(all(pool.map(lambda _: pp_request(), range(8))), '개인결제 동시 통보')
            check(state() == '4000\t6000\t주문\n4000\n주문\n주문', '개인결제 청구액만 가산')
            check(not pp_request({'noti_id':'20260910123456789013'}), '다른 통보 ID 중복 입금 거부')
            check(pp_request({'op_cd':'13'}), '개인결제 망취소')
            check(state() == '0\t10000\t주문\n0\n주문\n주문', '개인결제 망취소 차감')
            seed(personal=True, linked=False)
            check(pp_request() and pp_request() and pp_request({'op_cd':'13'}), '독립 개인결제 입금/재전송/취소')
            # 각 쓰기 직전 실패 및 DB 성공 후 응답 유실을 재현한다.
            for personal in [False, True]:
                for mode in ['fail_before', 'fail_after']:
                    for write_no in range(1, 7 if personal else 6):
                        seed(personal=personal)
                        if personal:
                            mysql('UPDATE g5_shop_personalpay SET pp_price=10000')
                        config(**{mode:write_no})
                        check(not request({'order_no':'2001'} if personal else {}), '중간 실패 시 실패 응답')
                        config()
                        check(request({'order_no':'2001'} if personal else {}), '재통보로 중간 실패 복구')
                        check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '10000', '복구 중복 가산 방지')
            # 망취소의 각 쓰기 단계도 실패 후 같은 이벤트로 복구한다.
            for personal in [False, True]:
                for write_no in range(1, 7 if personal else 6):
                    seed(personal=personal)
                    if personal:
                        mysql('UPDATE g5_shop_personalpay SET pp_price=10000')
                    config()
                    payload = {'order_no':'2001'} if personal else {}
                    check(request(payload), '망취소 실패 검증용 입금')
                    payload['op_cd'] = '13'
                    config(fail_after=write_no)
                    check(not request(payload), '망취소 중간 실패 응답')
                    config()
                    check(request(payload), '망취소 재통보 복구')
                    check(mysql('SELECT od_receipt_price FROM g5_shop_order') == '0', '망취소 중복 차감 방지')
            seed()
            config()
            mysql("CREATE TRIGGER kcp43_fail BEFORE UPDATE ON g5_shop_order FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='test failure'")
            check(not request(), '실제 DB 오류 실패 응답')
            mysql('DROP TRIGGER kcp43_fail')
            check(request(), '실제 DB 오류 후 복구')
            seed(personal=True)
            mysql('UPDATE g5_shop_order SET od_test=0')
            check(not pp_request(), '테스트 개인결제/운영 연결 주문 거부')
            seed()
            before = state()
            mysql('RENAME TABLE g5_shop_kcp_noti TO kcp43_missing')
            check(not request() and state() == before, '미적용 스키마 실패 시 DB 불변')
            mysql('RENAME TABLE kcp43_missing TO g5_shop_kcp_noti')
            seed()
            check(request({'tx_cd':'TX02'}), '바인딩된 에스크로 통보 수신')
            check(state() == '0\t10000\t주문\n주문\n주문', '에스크로 수신 금액 불변')
            seed(personal=True)
            config(fail_before=3)  # 개인결제 금액 반영 뒤 연결 주문 변경 전
            check(not pp_request(), '부분 처리 실패')
            mysql('UPDATE g5_shop_order SET od_receipt_price=1')
            before = state()
            config()
            check(not pp_request() and state() == before, '실패 후 외부 변경 충돌 보존')
            # 운영/에스크로 상점코드와 발신지 분리
            for site, ip, test in [('SR123','103.215.144.173',0),('SR123','103.215.144.174',0),('SR123','210.122.72.173',0),('T0007','210.122.176.144',1)]:
                seed(site=site)
                fixture.write_text(json.dumps(dict(test=test,ip=ip)))
                check(request({'site_cd':site}), '환경별 정상 상점/발신지')
            config()
        log.flush()
        log.seek(0)
        output = log.read()
        check('PHP Warning' not in output and 'PHP Fatal' not in output, 'PHP 경고/치명적 오류 없음')
        print('PASS:', checks, 'checks (HTTP, MySQL 8, MyISAM/InnoDB)')
finally:
    if server:
        os.killpg(server.pid, signal.SIGTERM)
        server.wait(timeout=10)
    mysql('DROP DATABASE `' + DB + '`', False)
