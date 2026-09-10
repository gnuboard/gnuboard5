"""실제 처리 파일의 순차 SQL/승인 경로 회귀검증. 외부 PG는 모의한다.
실행: python tests/shop_cart_followup.py /path/to/php
PHP pdo_sqlite가 필요하며 임시 SQLite DB만 생성한다.
"""
import ast, json, re, sqlite3, subprocess, sys, tempfile, atexit
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
temporary = tempfile.TemporaryDirectory(prefix='cart-followup-')
atexit.register(temporary.cleanup)
OUT = Path(temporary.name)
PHP = Path(sys.argv[1]).resolve() if len(sys.argv) > 1 else Path('php')
PHP_ARGS = sys.argv[2:]
tree = ast.parse((ROOT / 'tests/shop_cart_http.py').read_text(encoding='utf-8'))
bootstrap = next(n.value.value for n in ast.walk(tree) if isinstance(n, ast.Assign) and any(isinstance(t, ast.Name) and t.id == 'bootstrap' for t in n.targets))
start = bootstrap.index("$link = new mysqli")
end = bootstrap.index('function safe_replace_regex', start)
bootstrap = bootstrap[:start] + '''$link = new PDO('sqlite:'.__DIR__.'/fixture.sqlite');
$link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
function sql_query($sql, $error=true) { global $link; return $link->query($sql); }
function sql_fetch_array($result) { return $result->fetch(PDO::FETCH_ASSOC); }
function sql_fetch($sql) { return sql_fetch_array(sql_query($sql)) ?: array(); }
function sql_escape_string($s) { return str_replace("'", "''", (string)$s); }
function sql_real_escape_string($s) { return sql_escape_string($s); }
''' + bootstrap[end:]
bootstrap = bootstrap.replace('require TEST_VALIDATOR;', 'require '+json.dumps(str(ROOT / 'lib/shop.cartvalidate.lib.php'))+';')
bootstrap += '''
$_SESSION['ss_cart_id'] = '123456789';
define('G5_MSHOP_PATH', __DIR__);
define('G5_SHOP_PATH', __DIR__);
define('G5_HTTPS_MSHOP_URL', '/mobile/shop');
$g5['g5_shop_inicis_log_table'] = 'g5_shop_inicis_log';
$default['de_inicis_mid'] = 'test-mid';
function get_shop_order_data($id) { return array(); }
if (getenv('CASE') === 'approved') {
    $default['de_pg_service'] = 'inicis';
    $od_settle_case = '신용카드';
    $_POST['P_HASH'] = 'simulated-verified-approval';
    $_SESSION['P_TID'] = 'simulated-approved-tid';
    $_SESSION['P_AMT'] = '50000';
    $_SESSION['P_HASH'] = $_POST['P_HASH'];
} else {
    $_POST = array('action'=>'cart_update', 'it_id'=>array('item'),
        'io_id'=>array('item'=>array('', 'wrap')), 'io_type'=>array('item'=>array('0','1')),
        'ct_qty'=>array('item'=>array('1','1')));
    $_REQUEST = $_POST;
}
'''
bootstrap = bootstrap.replace("getenv('CASE') === 'approved'", "in_array(getenv('CASE'), array('approved', 'callback'))")
bootstrap += '''
if (getenv('SCENARIO') === 'direct') $sw_direct = $_POST['sw_direct'] = $_REQUEST['sw_direct'] = 1;
if (getenv('SCENARIO') === 'replace') $_POST['act'] = 'optionmod';
if (in_array(getenv('SCENARIO'), array('multi','cert'))) {
    $_POST['it_id'][] = 'other';
    $_POST['io_id']['other'] = array('');
    $_POST['io_type']['other'] = array('0');
    $_POST['ct_qty']['other'] = array('1');
}
if (getenv('SCENARIO') === 'cert') $is_admin = false;
function shop_member_cert_check($id,$type) { return $id === 'other' ? '구매 자격 확인 필요' : ''; }
if (getenv('CASE') === 'callback') {
    $_SESSION['ss_order_id'] = '1001';
    $g5['g5_shop_order_data_table'] = 'order_data';
    $default['de_inicis_mid'] = 'test-mid';
    $_REQUEST = array('P_NOTI'=>'test-order','P_REQ_URL'=>'https://example.invalid/approval','P_STATUS'=>'00','P_TID'=>'test-tid');
    function is_inicis_url_return($s) { return $s; }
    function iconv_utf8($s) { return $s; }
    function get_search_string($s) { return $s; }
    foreach (array('CURLOPT_PORT','CURLOPT_URL','CURLOPT_POST','CURLOPT_POSTFIELDS','CURLOPT_RETURNTRANSFER') as $i=>$c) define($c,$i);
    function curl_init() { return true; }
    function curl_setopt($h,$k,$v) { return true; }
    function curl_exec($h) {
        global $link;
        $GLOBALS['review_pg_approved'] = true;
        if (getenv('SCENARIO') === 'deleted')
            $link->exec('DELETE FROM g5_shop_cart');
        else if (getenv('SCENARIO') !== 'unchanged')
            $link->exec('UPDATE g5_shop_item SET it_price=50000');
        return 'P_STATUS=00&P_TID=test-tid&P_MID=test-mid&P_AMT=40000&P_TYPE=CARD';
    }
    $link->exec('DROP TABLE IF EXISTS order_data');
    $link->exec('CREATE TABLE order_data (od_id TEXT, dt_data TEXT)');
    $stmt = $link->prepare('INSERT INTO order_data VALUES (?,?)');
    $stmt->execute(array('test-order',base64_encode(serialize(array('od_settle_case'=>'신용카드')))));
}
'''
bootstrap = bootstrap.replace("array('error'=>$message)", "array('error'=>$message, 'simulated_pg_approved'=>!empty($GLOBALS['review_pg_approved']), 'canceled_tid'=>isset($GLOBALS['canceled_tid']) ? $GLOBALS['canceled_tid'] : '', 'session_tid'=>get_session('P_TID'))")
(OUT/'bootstrap.php').write_text(bootstrap, encoding='utf-8')
(OUT/'mailer.lib.php').write_text('<?php', encoding='utf-8')
(OUT/'settle_inicis.inc.php').write_text('<?php', encoding='utf-8')
(OUT/'inicis/libs').mkdir(parents=True)
(OUT/'inicis/cart_validation_cancel.php').write_bytes((ROOT/'mobile/shop/inicis/cart_validation_cancel.php').read_bytes())
(OUT/'inicis/libs/inicis_youngcart_fn.php').write_text('''<?php
function get_type_inicis_paymethod($method) { return 'Card'; }
function inicis_tid_cancel($args) {
    $GLOBALS['canceled_tid'] = $args['tid'];
    return json_encode(array('resultCode'=>getenv('SCENARIO') === 'cancel_fail' ? '99' : '00'));
}
''', encoding='utf-8')
for name, source in [('ajax','shop/ajax.action.php'),('theme','theme/basic/shop/ajax.action.php'),('normal','shop/cartupdate.php'),('approved','mobile/shop/orderformupdate.php')]:
    text = (ROOT/source).read_text(encoding='utf-8').replace("include_once('./_common.php');", "include_once('./bootstrap.php');", 1)
    if name=='approved':
        text=text.split('// 변수 초기화',1)[0]+"echo json_encode(array('error'=>'', 'checkpoint'=>'validated', 'simulated_pg_approved'=>!empty($GLOBALS['review_pg_approved']), 'canceled_tid'=>isset($GLOBALS['canceled_tid']) ? $GLOBALS['canceled_tid'] : '')); exit;"
    (OUT/(name+'.php')).write_text(text, encoding='utf-8')
(OUT/'orderformupdate.php').write_text((OUT/'approved.php').read_text(encoding='utf-8'), encoding='utf-8')
callback=(ROOT/'mobile/shop/inicis/pay_approval.php').read_text(encoding='utf-8').replace("include_once('./_common.php');", "include_once('./bootstrap.php');",1).replace("include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');", "// PG settings supplied by isolated bootstrap")
(OUT/'callback.php').write_text(callback,encoding='utf-8')

def reset(approved=False):
    db = sqlite3.connect(OUT/'fixture.sqlite')
    schema = (ROOT/'install/gnuboard5shop.sql').read_text(encoding='utf-8')
    for table in ['item','item_option','cart','inicis_log']:
        db.execute('DROP TABLE IF EXISTS g5_shop_'+table)
        block = re.search(r'CREATE TABLE IF NOT EXISTS `g5_shop_'+table+r'` \((.*?);',schema,re.S).group(1)
        # Source column names/defaults, SQLite storage for deterministic sequential SQL.
        cols=[]
        for line in block.splitlines():
            m = re.match(r'\s*`([^`]+)`\s+(.+)',line)
            if not m: continue
            col, decl = m.groups()
            typ = 'INTEGER' if re.match(r'(tinyint|smallint|int|bigint)',decl) else 'TEXT'
            default = re.search(r"DEFAULT ('[^']*'|\d+)",decl,re.I)
            cols.append('"'+col+'" '+typ+' DEFAULT '+(default.group(1) if default else ("0" if typ=='INTEGER' else "''")))
        db.execute('CREATE TABLE g5_shop_'+table+' ('+','.join(cols)+')')
    db.execute("INSERT INTO g5_shop_item (it_id,it_name,it_use,it_price,it_stock_qty,it_sc_type) VALUES ('item','item',1,50000,100,1)")
    db.execute("INSERT INTO g5_shop_cart (ct_id,od_id,it_id,ct_status,ct_price,io_id,io_type,io_price,ct_qty,ct_select) VALUES (1,'123456789','item','쇼핑',?,'',0,0,1,1)",(40000 if approved else 50000,))
    if not approved:
        db.execute("INSERT INTO g5_shop_item_option (it_id,io_id,io_type,io_use,io_price,io_stock_qty) VALUES ('item','wrap',1,1,1000,100)")
        db.execute("INSERT INTO g5_shop_cart (ct_id,od_id,it_id,ct_status,ct_price,io_id,io_type,io_price,ct_qty,ct_select) VALUES (2,'123456789','item','쇼핑',50000,'wrap',1,900,1,1)")
    db.commit()
    return db

import os
results=[]
cases=[(r,s) for r in ['ajax','theme','normal'] for s in ['stale','stock','maximum','multi','valid']]
cases += [('normal','direct'),('normal','replace'),('normal','cert'),('approved','forged')]
cases += [('callback',s) for s in ['before','during','cancel_fail','deleted','existing_log','unchanged']]
for route, scenario in cases:
    db=reset(route in ['approved','callback'])
    atexit.register(db.close)
    if scenario == 'valid' or scenario in ['stock','maximum','multi','direct','cert']:
        db.execute('UPDATE g5_shop_cart SET io_price=1000 WHERE io_type=1')
    if scenario == 'stock': db.execute('UPDATE g5_shop_item_option SET io_stock_qty=1')
    if scenario == 'maximum': db.execute('UPDATE g5_shop_item SET it_buy_max_qty=1')
    if scenario in ['multi','cert']:
        db.execute("INSERT INTO g5_shop_item (it_id,it_name,it_use,it_price,it_stock_qty) VALUES ('other','other',1,30000,100)")
        db.execute("INSERT INTO g5_shop_cart (ct_id,od_id,it_id,ct_status,ct_price,io_id,io_type,io_price,ct_qty) VALUES (3,'123456789','other','쇼핑',20000,'',0,0,1)")
        if scenario=='cert': db.execute("UPDATE g5_shop_cart SET ct_price=30000 WHERE it_id='other'")
    if scenario == 'direct':
        db.execute('UPDATE g5_shop_cart SET ct_direct=1')
        db.execute("INSERT INTO g5_shop_cart (ct_id,od_id,it_id,ct_status,ct_price,io_id,io_type,io_price,ct_qty,ct_select) VALUES (3,'999','item','쇼핑',50000,'',0,0,100,1)")
    if route == 'callback' and scenario != 'before': db.execute('UPDATE g5_shop_item SET it_price=40000')
    if scenario=='existing_log': db.execute("INSERT INTO g5_shop_inicis_log (oid,P_TID,P_STATUS) VALUES (1001,'test-tid','00')")
    db.commit()
    before=db.execute('SELECT ct_id,ct_qty FROM g5_shop_cart ORDER BY ct_id').fetchall()
    p=subprocess.run([str(PHP)]+PHP_ARGS+[str(OUT/(route+'.php'))],cwd=OUT,env=dict(os.environ,CASE=route,SCENARIO=scenario),capture_output=True,text=True,encoding='utf-8')
    after=db.execute('SELECT ct_id,ct_qty FROM g5_shop_cart ORDER BY ct_id').fetchall()
    assert p.returncode == 0, p.stderr
    response=json.loads(p.stdout)
    assert 'PHP Warning' not in p.stderr and 'PHP Fatal' not in p.stderr, p.stderr
    if scenario=='unchanged':
        assert response['error']=='' and response['checkpoint']=='validated', response
    elif scenario in ['valid','replace']:
        assert response['error'] == '', (route,scenario,response)
        quantities=db.execute('SELECT io_type,SUM(ct_qty) FROM g5_shop_cart GROUP BY io_type ORDER BY io_type').fetchall()
        assert quantities == [(0,2),(1,2)] if scenario=='valid' else quantities==[(0,1),(1,1)], quantities
    else:
        assert response['error'], (route,scenario,response)
        if scenario != 'deleted': assert before==after, (route,scenario,before,after)
    if route == 'callback':
        assert response['simulated_pg_approved'] == (scenario!='before'), response
        assert response['canceled_tid'] == ('' if scenario in ['before','unchanged'] else 'test-tid'), response
        if scenario not in ['before','unchanged']:
            status=db.execute('SELECT P_STATUS FROM g5_shop_inicis_log').fetchone()[0]
            assert status==('cancel_failed' if scenario=='cancel_fail' else 'cancel'), status
            assert response['session_tid']==('test-tid' if scenario=='cancel_fail' else ''), response
    if scenario=='forged': assert response['canceled_tid']=='', response
    results.append(route+'/'+scenario)
    db.close()
print(str(len(results))+'개 후속 회귀 검증 통과 (SQLite, 모의 PG): '+', '.join(results))
