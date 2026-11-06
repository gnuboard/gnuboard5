"""관리자 부분취소 처리기를 임시 MariaDB 테이블과 루프백 토스 대역으로 검증한다.
G5_PARTIAL_TEST_DBCONFIG에 테스트 연결용 G5_MYSQL_* PHP 설정 경로를 지정한다.
"""
from concurrent.futures import ThreadPoolExecutor
import json
import os
from http.server import BaseHTTPRequestHandler, HTTPServer
from pathlib import Path
import subprocess
from tempfile import TemporaryDirectory
from threading import Thread

root = Path(__file__).resolve().parents[1]
key = "aZ/?#%'\\".ljust(200, 'X')
order = '1234567890123456'


class Provider(BaseHTTPRequestHandler):
    def reply(self, data):
        self.send_response(200)
        self.end_headers()
        self.wfile.write(json.dumps(data).encode())

    def do_GET(self):
        self.reply(dict(orderId=order, paymentKey=self.server.key, balanceAmount=self.server.balance))

    def do_POST(self):
        data = json.loads(self.rfile.read(int(self.headers['Content-Length'])))
        self.server.posts.append((self.headers['Idempotency-Key'], data))
        self.server.balance -= data['cancelAmount']
        if self.server.mode == 'lost':
            return
        cancel = dict(cancelAmount=data['cancelAmount'], refundableAmount=self.server.balance,
                      taxFreeAmount=data.get('taxFreeAmount', 0), transactionKey='txn', cancelStatus='DONE')
        if self.server.mode == 'wrong':
            cancel['cancelAmount'] += 1
        self.reply(dict(orderId=order, paymentKey=key, balanceAmount=self.server.balance,
                        status='PARTIAL_CANCELED' if self.server.balance else 'CANCELED',
                        lastTransactionKey='txn', cancels=[cancel]))

    def log_message(self, *args):
        pass


bootstrap = """<?php
define('_GNUBOARD_', true);
define('G5_MYSQLI_USE', true);
define('G5_PATH', __DIR__);
define('G5_LIB_PATH', getenv('REFUND_ROOT').'/lib');
define('G5_SHOP_PATH', __DIR__.'/shop');
require getenv('G5_PARTIAL_TEST_DBCONFIG');
require G5_LIB_PATH.'/shop.lib.php';
$db = new mysqli(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB);
$db->set_charset('utf8mb4');
$db->query("SET SESSION sql_mode=''");
$fixture = json_decode(getenv('REFUND_FIXTURE'),true);
$g5 = array('g5_shop_order_table'=>'fixture_order','g5_shop_cart_table'=>'fixture_cart');
$default = array('de_send_cost_case'=>'무료');
$config = array('cf_toss_client_key'=>'','cf_toss_secret_key'=>'','cf_lg_mid'=>'');
$auth = array();
foreach (array('order','cart') as $table)
    $db->query("CREATE TEMPORARY TABLE fixture_$table LIKE ".G5_SHOP_TABLE_PREFIX.$table);
$stmt = $db->prepare("INSERT INTO fixture_order SET od_id=?, od_tno=?, od_pg=?, od_misu=?, od_receipt_price=1500, od_refund_price=?");
$stmt->bind_param('sssii',$fixture['id'],$fixture['key'],$fixture['pg'],$fixture['misu'],$fixture['before']);
$stmt->execute();
$_POST = array('od_id'=>$fixture['id'],'refund_before'=>(string)$fixture['form'],
    'mod_tax_mny'=>$fixture['tax'],'mod_free_mny'=>$fixture['free'],'mod_memo'=>'test');
function sql_query($q,$error=true) {
    global $db,$fixture;
    if ($fixture['db_fail'] && preg_match('/^update/i',ltrim($q))) return false;
    return $db->query($q);
}
function sql_fetch($q){return sql_query($q)->fetch_assoc();}
function sql_fetch_array($r){return $r->fetch_assoc();}
function sql_real_escape_string($s){global $db;return $db->real_escape_string($s);}
function get_sql_affected_rows(){global $db;return $db->affected_rows;}
function get_text($s){return $s;}
function auth_check_menu($a,$b,$c){}
function check_admin_token(){}
function safe_replace_regex($v,$type){return $v;}
function clean_xss_tags($v,$a,$b){return $v;}
function alert($message){throw new RuntimeException($message);}
function alert_close($message){alert($message);}
"""
runner = """<?php
chdir(__DIR__);
ob_start();
try { require getenv('REFUND_ROOT').'/adm/shop_admin/orderpartcancelupdate.php'; $out='ok'; }
catch (RuntimeException $e) { $out=$e->getMessage(); }
ob_end_clean();
$row=sql_fetch('SELECT od_refund_price,od_misu FROM fixture_order');
echo json_encode(array($out,(int)$row['od_refund_price'],(int)$row['od_misu']));
"""
with TemporaryDirectory() as tmp, HTTPServer(('127.0.0.1', 0), Provider) as pg:
    directory = Path(tmp)
    Thread(target=pg.serve_forever, daemon=True).start()
    for name in ['head.sub.php', 'tail.sub.php']:
        (directory / name).touch()
    (directory / '_common.php').write_text(bootstrap)
    (directory / 'run.php').write_text(runner)
    for module in ['toss', 'lg']:
        dest = directory / 'shop' / module
        dest.mkdir(parents=True)
        (dest / 'orderpartcancel.inc.php').symlink_to(root / 'shop' / module / 'orderpartcancel.inc.php')
    (directory / 'shop/settle_lg.inc.php').write_text("""<?php
$configPath = $CST_PLATFORM = $LGD_MID = '';
$config['cf_lg_mert_key'] = '';
class XPay {
    public $values = array();
    function set_config_value($name,$value){}
    function Init_TX($mid){}
    function Set($name,$value){$this->values[$name] = $value;}
    function TX(){return $this->values['LGD_CANCELAMOUNT'] === 300 && $this->values['LGD_CANCELTAXFREEAMOUNT'] === 0;}
    function Response_Code(){return '0000';}
    function Response($name,$index){return $this->values[$name];}
}
""")
    client = (root / 'shop/toss/toss.inc.php').read_text()
    (directory / 'shop/toss/toss.inc.php').write_text(client.replace('https://api.tosspayments.com', 'http://127.0.0.1:'+str(pg.server_port)))
    pg.posts = []
    pg.key = key
    pg.mode = ''
    pg.balance = 1500

    def request(**changes):
        fixture = dict(id=order, key=key, pg='toss', before=0, form=0, misu=-1500,
                       tax='300', free='200', db_fail=False)
        fixture.update(changes)
        proc = subprocess.run(['php', str(directory/'run.php')], text=True, capture_output=True,
                              env=dict(os.environ, REFUND_ROOT=str(root), REFUND_FIXTURE=json.dumps(fixture)))
        assert proc.returncode == 0 and not proc.stderr, proc.stderr
        return json.loads(proc.stdout)

    for options in [dict(misu=500),dict(misu=0),dict(misu=-400),dict(tax='-5'),dict(before=1300,form=1300),
                    dict(pg='lg'),dict(pg='lg',tax='0'),dict(form=1)]:
        assert request(**options)[0] != 'ok'
    assert request(pg='lg',free='0') == ['ok',300,-1200]
    pg.key = key.swapcase()
    assert request()[0] != 'ok' and not pg.posts
    pg.key = key
    assert request() == ['ok', 500, -1000]
    first_key, first_body = pg.posts[0]
    assert first_body['cancelAmount'] == 500 and first_body['taxFreeAmount'] == 200 and first_body['paymentKey'] == key
    assert request(before=500,misu=-1000)[0] != 'ok' and len(pg.posts) == 1
    assert request(before=500,form=500,misu=-1000) == ['ok',1000,-500]
    assert pg.posts[-1][0] != first_key
    assert request(before=1000,form=1000,misu=-500) == ['ok',1500,0]
    for mode in ['lost','wrong','db_fail']:
        pg.mode, pg.balance = mode, 1500
        count = len(pg.posts)
        result = request(db_fail=(mode=='db_fail'))
        assert result[0] != 'ok' and result[1:] == [0,-1500], (mode,result)
        assert request()[0] != 'ok' and len(pg.posts) == count + 1
        assert pg.posts[-1][0] == first_key
    pg.mode, pg.balance = '', 1500
    count = len(pg.posts)
    with ThreadPoolExecutor(max_workers=2) as pool:
        outcomes = list(pool.map(lambda _: request(), range(2)))
    assert sum(out[0] == 'ok' for out in outcomes) == 1 and len(pg.posts) == count + 1
    pg.shutdown()
print('통과: 환불 한도, XPay 면세 차단, 취소 3회, 오래된 폼, 응답 유실·불일치·DB 실패 후 중복 방지')
