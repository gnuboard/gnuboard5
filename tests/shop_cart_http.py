#!/usr/bin/env python3
"""격리 MySQL 전용: python3 tests/shop_cart_http.py /path/to/test-mysql.sock

고유 테스트 DB를 생성·삭제한다. 실제 장바구니 처리 파일을 HTTP로 실행하며,
주문서는 PG/메일/주문 저장을 실행하기 전 검증 경계까지만 실행한다.
인증·세션·메일·리다이렉트 등 주변 기능은 아래 테스트 부트스트랩으로 대체한다.
"""
import http.cookiejar
import json
from pathlib import Path
import socket
import subprocess
import sys
import tempfile
import time
import urllib.parse
import urllib.request

ROOT = Path(__file__).resolve().parents[1]
SOCKET = sys.argv[1]
DB = 'kve2345_test_' + str(time.time_ns())

def mysql(sql, database=True):
    command = ['mysql', '--no-defaults', '--socket=' + SOCKET, '-u', 'root', '-N', '-B']
    if database:
        command.append(DB)
    return subprocess.check_output(command, input="SET sql_mode='';" + sql, text=True).strip()

mysql('CREATE DATABASE `' + DB + '`', False)
server = None
try:
    import re
    schema = (ROOT / 'install/gnuboard5shop.sql').read_text()
    for table in ['item', 'item_option', 'cart']:
        statement = re.search(r'CREATE TABLE IF NOT EXISTS `g5_shop_' + table + r'` \(.*?;', schema, re.S).group()
        mysql("SET sql_mode='';" + statement)
    mysql("SET sql_mode=''; INSERT INTO g5_shop_item (it_id,it_name,it_use,it_price,it_stock_qty,it_sc_type) VALUES ('item','상품',1,50000,100,1),('other','다른 상품',1,30000,100,1)")
    with tempfile.TemporaryDirectory(prefix='kve2345-http-') as temp:
        directory = Path(temp)
        bootstrap = r'''<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');
session_start();
define('_GNUBOARD_', true);
define('G5_SHOP_URL', '/shop');
define('G5_BBS_URL', '/bbs');
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
define('G5_OPTION_ID_FILTER', '/[\'"\\\\]/');
define('G5_LIB_PATH', __DIR__);
$g5 = array('g5_shop_item_table'=>'g5_shop_item', 'g5_shop_item_option_table'=>'g5_shop_item_option', 'g5_shop_cart_table'=>'g5_shop_cart');
$member = array('mb_id'=>'', 'mb_level'=>1);
$default = array('de_level_sell'=>1, 'de_pg_service'=>'kcp');
$config = array('cf_use_point'=>0);
$is_admin = true; $is_member = false;
$sw_direct = !empty($_POST['sw_direct']) ? 1 : 0;
$od_settle_case = '무통장';
$link = new mysqli('localhost','root','',TEST_DB,0,TEST_SOCKET);
$link->set_charset('utf8');
$link->query("SET sql_mode=''");
function sql_query($sql, $error=true) { global $link; return $link->query($sql); }
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_fetch($sql) { return sql_fetch_array(sql_query($sql)) ?: array(); }
function sql_escape_string($s) { global $link; return $link->real_escape_string($s); }
function sql_real_escape_string($s) { return sql_escape_string($s); }
function safe_replace_regex($s,$type) { return preg_replace('/[^a-z0-9_\-]/i','',$s); }
function clean_xss_tags($s) { return strip_tags($s); }
function get_real_client_ip() { return '127.0.0.1'; }
function set_cart_id($direct) { if(empty($_SESSION['ss_cart_id'])) $_SESSION['ss_cart_id'] = (string)random_int(100000000,999999999); $_SESSION['ss_cart_direct'] = $_SESSION['ss_cart_id']; }
function get_session($key) { return isset($_SESSION[$key]) ? $_SESSION[$key] : ''; }
function set_session($key,$value) { $_SESSION[$key]=$value; }
function cart_item_clean() {}
function is_inicis_order_pay($method) { return false; }
function check_request_origin($url) {}
function get_cart_count($id) { $r=sql_fetch("SELECT COUNT(*) n FROM g5_shop_cart WHERE od_id='".sql_escape_string($id)."'"); return $r['n']; }
function get_it_stock_qty($id) { $r=sql_fetch("SELECT it_stock_qty FROM g5_shop_item WHERE it_id='".sql_escape_string($id)."'"); return $r['it_stock_qty']; }
function get_option_stock_qty($id,$option,$type) { $r=sql_fetch("SELECT io_stock_qty FROM g5_shop_item_option WHERE it_id='".sql_escape_string($id)."' AND io_id='".sql_escape_string($option)."' AND io_type='".(int)$type."'"); return $r['io_stock_qty']; }
function alert($message,$url='') { die(json_encode(array('error'=>$message))); }
function goto_url($url) { die(json_encode(array('error'=>'','redirect'=>$url))); }
require TEST_VALIDATOR;
'''
        constants = '<?php\ndefine("TEST_DB",' + json.dumps(DB) + ');\ndefine("TEST_SOCKET",' + json.dumps(SOCKET) + ');\ndefine("TEST_VALIDATOR",' + json.dumps(str(ROOT / 'lib/shop.cartvalidate.lib.php')) + ');\n?>\n'
        (directory / 'bootstrap.php').write_text(constants + bootstrap)
        (directory / 'mailer.lib.php').write_text('<?php')
        routes = {'ajax': 'shop/ajax.action.php', 'theme': 'theme/basic/shop/ajax.action.php', 'normal': 'shop/cartupdate.php', 'pc_order': 'shop/orderformupdate.php', 'mobile_order': 'mobile/shop/orderformupdate.php'}
        for route, source in routes.items():
            text = (ROOT / source).read_text().replace("include_once('./_common.php');", "include_once('./bootstrap.php');", 1)
            if route.endswith('_order'):
                text = text.split('// 변수 초기화', 1)[0] + "echo json_encode(array('error'=>'','checkpoint'=>'validated')); exit;"
            (directory / (route + '.php')).write_text(text)
        with socket.socket() as sock:
            sock.bind(('127.0.0.1', 0))
            port = sock.getsockname()[1]
        with (directory / 'server.log').open('w+') as log:
            server = subprocess.Popen(['php', '-S', '127.0.0.1:' + str(port), '-t', temp], stdout=log, stderr=log, cwd=temp)
            for attempt in range(100):
                try:
                    with socket.create_connection(('127.0.0.1', port), timeout=.1):
                        break
                except OSError:
                    time.sleep(.05)
            client = urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
            count = 0
            def request(route, fields):
                data = urllib.parse.urlencode(fields).encode()
                try:
                    with client.open('http://127.0.0.1:' + str(port) + '/' + route + '.php', data) as response:
                        return json.loads(response.read())
                except Exception:
                    print((directory / 'server.log').read_text(), file=sys.stderr)
                    raise
            def payload(types=('0',), ids=('',)):
                data = [('action','cart_update'),('it_id[0]','item')]
                for i, (kind, identity) in enumerate(zip(types, ids)):
                    data += [('io_type[item]['+str(i)+']',kind),('io_id[item]['+str(i)+']',identity),('ct_qty[item]['+str(i)+']','1')]
                return data
            def expect(value, description):
                if not value:
                    raise AssertionError(description)
            for engine in ['MyISAM','InnoDB']:
                for table in ['item','item_option','cart']:
                    mysql('ALTER TABLE g5_shop_' + table + ' ENGINE=' + engine)
                for route in ['ajax','theme','normal']:
                    mysql('DELETE FROM g5_shop_cart; DELETE FROM g5_shop_item_option')
                    for bad in [payload(('1',)), payload(('0','1'),('','')), payload(('01',)), payload(('1x',)), payload(('0',),('missing',))]:
                        result = request(route,bad)
                        expect(result['error'] != '' and mysql('SELECT COUNT(*) FROM g5_shop_cart') == '0', route+' 잘못된 입력')
                        count += 1
                    malformed = [
                        [('action','cart_update'),('it_id[0]','item'),('io_id[item][0]',''),('io_type[item][0][nested]','0'),('ct_qty[item][0]','1')],
                        [('action','cart_update'),('it_id[0]','item'),('io_id[item][0][nested]',''),('io_type[item][0]','0'),('ct_qty[item][0]','1')],
                        [('action','cart_update'),('it_id[0]','item'),('io_id[item][0]',''),('io_type[item][0]','0'),('ct_qty[item][1]','1')],
                        [('action','cart_update'),('it_id[0]','item'),('io_id','bad'),('io_type[item][0]','0'),('ct_qty[item][0]','1')],
                        payload()+[('ct_qty[item][0]','1.5')],
                        payload()+[('io_value[item][0][nested]','bad')],
                    ]
                    for bad in malformed:
                        expect(request(route,bad)['error'] != '' and mysql('SELECT COUNT(*) FROM g5_shop_cart') == '0', '배열 구조 및 수량 검증')
                        count += 1
                    expect(request(route,payload())['error'] == '',route+' 정상 추가')
                    expect(mysql('SELECT SUM(IF(io_type=1,io_price*ct_qty,(ct_price+io_price)*ct_qty)) FROM g5_shop_cart') == '50000',route+' 정상 가격')
                    count += 1
                    # 옵션 변경/바로구매 실패가 기존 행을 삭제하면 안 된다.
                    before = mysql('SELECT ct_id,ct_qty FROM g5_shop_cart')
                    expect(request(route,payload(('1',))+[('sw_direct','1'),('act','optionmod')])['error'] != '', '잘못된 수정 거부')
                    expect(mysql('SELECT ct_id,ct_qty FROM g5_shop_cart') == before,'기존 장바구니 보존')
                    count += 1
                    bad_second = payload()+[('it_id[1]','other'),('io_type[other][0]','1'),('io_id[other][0]',''),('ct_qty[other][0]','1')]
                    expect(request(route,bad_second)['error'] != '', '다중 상품 검증')
                    expect(mysql('SELECT ct_id,ct_qty FROM g5_shop_cart') == before,'다중 요청 부분 변경 방지')
                    count += 1
                    expect(request(route,payload())['error'] == '', '정상 병합')
                    expect(mysql('SELECT COUNT(*),SUM(ct_qty) FROM g5_shop_cart') == '1\t2','수량 병합 결과')
                    count += 1
                    # 패치 전 변조 행이 정상이 되어 보이도록 병합되지 않는지 확인
                    mysql('UPDATE g5_shop_cart SET io_type=1,io_price=0,ct_select=1')
                    for order_route in ['pc_order','mobile_order']:
                        expect(request(order_route,[])['error'] != '', '과거 조작 주문 거부')
                        count += 1
                    mysql('UPDATE g5_shop_cart SET io_type=0')
                    for order_route in ['pc_order','mobile_order']:
                        expect(request(order_route,[]) .get('checkpoint') == 'validated','정상 주문 검증 진입')
                        count += 1
                    mysql("DELETE FROM g5_shop_cart; INSERT INTO g5_shop_item_option (it_id,io_id,io_type,io_use,io_price,io_stock_qty) VALUES ('item','wrap',1,1,1000,100)")
                    expect(request(route,payload(('0','1'),('','wrap')))['error'] == '', '정상 보조옵션')
                    expect(mysql('SELECT SUM(IF(io_type=1,io_price*ct_qty,(ct_price+io_price)*ct_qty)) FROM g5_shop_cart') == '51000','보조옵션 포함 합계')
                    count += 1
                    # 타입은 다르지만 ID가 같은 옵션을 서로 병합하지 않는다.
                    mysql("DELETE FROM g5_shop_cart; DELETE FROM g5_shop_item_option; INSERT INTO g5_shop_item_option (it_id,io_id,io_type,io_use,io_price,io_stock_qty) VALUES ('item','same',0,1,2000,100),('item','same',1,1,1000,100)")
                    pair = payload(('0','1'),('same','same'))
                    expect(request(route,pair)['error'] == '', '동일 ID 다른 종류 추가')
                    expect(request(route,pair)['error'] == '', '동일 ID 다른 종류 병합')
                    expect(mysql('SELECT COUNT(*),SUM(IF(io_type=1,io_price*ct_qty,(ct_price+io_price)*ct_qty)) FROM g5_shop_cart') == '2\t106000', '서로 다른 종류 병합 방지')
                    count += 1
            server.terminate(); server.wait(); server = None
            log.seek(0); errors = log.read()
            expect('PHP Warning' not in errors and 'PHP Fatal' not in errors,errors)
            print(str(count)+'개 HTTP/DB 검증 통과 (MyISAM·InnoDB, 주문은 검증 경계까지)')
finally:
    if server:
        server.terminate(); server.wait()
    mysql('DROP DATABASE `' + DB + '`', False)
