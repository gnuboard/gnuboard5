"""사용법: python3 tests/kcp_cash_http.py /tmp/kve2513-.../mysql.sock
실제 PHP 엔드포인트와 원장 DB를 사용하고 KCP 전송 클래스만 격리 사본에서 대체한다.
"""
import sys, pathlib, tempfile, subprocess, socket, time, urllib.request, urllib.parse, urllib.error, http.cookiejar, re, json, shutil
root=pathlib.Path(__file__).resolve().parents[1]
sock=sys.argv[1]
assert sock.startswith('/tmp/kve2513-')
work=pathlib.Path(tempfile.mkdtemp(prefix='kve2513-http-'))
(work/'shop/kcp').mkdir(parents=True)
for name in ['pp_cli_hub.php','pp_cli_result.php','taxsave_form.php']:
    shutil.copyfile(root/'shop/kcp'/name,work/'shop/kcp'/name)
(work/'head.sub.php').write_text('<!doctype html><html lang="ko"><meta charset="utf-8"><body>')
(work/'tail.sub.php').write_text('</body></html>')
bootstrap='''<?php
define('KCP_CASH_TEST_BOOTSTRAP',true);
$argv=array('bootstrap',SOCKET);
require ROOT.'/tests/kcp_cash_test.php';
session_start(); $session =& $_SESSION;
$is_member=($_SESSION['actor'] ?? 'guest')==='owner';
$is_admin=($_SESSION['actor'] ?? '')==='super' ? 'super' : '';
$member=array('mb_id'=>$is_member?'owner':'');
$default['de_card_test']=1; $default['de_escrow_use']=0;
define('G5_LIB_PATH',ROOT.'/lib'); define('G5_PATH',WORK); define('G5_SHOP_PATH',WORK.'/shop'); define('G5_SHOP_URL','/shop');
$fault=$_SESSION['fault'] ?? '';
function alert($message) {http_response_code(400);echo htmlspecialchars($message,ENT_QUOTES,'UTF-8');exit;}
function goto_url($url) {header('Location: '.$url);exit;}
'''.replace('SOCKET',repr(sock)).replace('ROOT',repr(str(root))).replace('WORK',repr(str(work)))
(work/'shop/kcp/_common.php').write_text(bootstrap)
(work/'fixture.php').write_text('''<?php require __DIR__.'/shop/kcp/_common.php';
setup_cash('InnoDB');fixture_cash();$_SESSION['actor']=$_GET['actor'] ?? 'owner';$_SESSION['fault']=$_GET['fault'] ?? '';
mysqli_query($db,"UPDATE cash_order SET od_name='<script>x</script>'"); echo 'ready';''')
(work/'form.php').write_text("<?php require __DIR__.'/shop/kcp/_common.php'; $od_id='100'; $tx='order';include __DIR__.'/shop/kcp/taxsave_form.php';")
(work/'shop/kcp/pp_cli_hub_lib.php').write_text('''<?php
class C_PAYPLUS_CLI_T {
    public $m_res_cd;public $m_res_data;public $order=array();public $mod=array();
    function mf_clear(){$this->order=array();$this->mod=array();}
    function mf_set_data_us($k,$v){return $k.'='.$v.chr(31);}
    function mf_set_ordr_data($k,$v){$this->order[$k]=$v;}
    function mf_set_modx_data($k,$v){$this->mod[$k]=$v;}
    function mf_do_tx(...$args){
        file_put_contents(LOGFILE,json_encode(array('code'=>$args[4],'host'=>$args[6],'order'=>base64_encode(serialize($this->order)),'mod'=>$this->mod))."\\n",FILE_APPEND|LOCK_EX);
        $this->m_res_cd='0000';$this->m_res_data=array('cash_no'=>'12345678901234','receipt_no'=>'100000001','app_time'=>'20261014090000','reg_stat'=>'NTRW','reg_desc'=>'');
    }
}
'''.replace('LOGFILE',repr(str(work/'gateway.jsonl'))))
with socket.socket() as s:s.bind(('127.0.0.1',0));port=s.getsockname()[1]
log=(work/'server.log').open('w')
proc=subprocess.Popen(['php8.4','-d','error_reporting=-1','-S',f'127.0.0.1:{port}','-t',str(work)],stdout=log,stderr=log)
base=f'http://127.0.0.1:{port}';jar=http.cookiejar.CookieJar();client=urllib.request.build_opener(urllib.request.HTTPCookieProcessor(jar));checks=[]
def req(path,data=None,opener=client):
    try:
        with opener.open(base+path,urllib.parse.urlencode(data).encode() if data is not None else None,timeout=15) as r:return r.status,r.read().decode()
    except urllib.error.HTTPError as e:return e.code,e.read().decode()
def check(ok,name):
    assert ok,name
    checks.append(name);print('PASS HTTP '+name,flush=True)
def count():return len((work/'gateway.jsonl').read_text().splitlines()) if (work/'gateway.jsonl').exists() else 0
def form():
    code,text=req('/form.php');assert code==200,text
    token=re.search('name="cash_token" value="([a-f0-9]+)"',text).group(1)
    return {'req_tx':'pay','tx':'order','ordr_idxx':'100','cash_token':token,'tr_code':'0','id_info':'01000000000'},text
try:
    for i in range(100):
        try:req('/fixture.php');break
        except OSError:time.sleep(.05)
    anonymous=urllib.request.build_opener()
    r,text=form();check('&lt;script&gt;' in text and '<script>alert' not in text,'발급 화면 원장 출력 이스케이프')
    before=count();code,_=req('/shop/kcp/pp_cli_hub.php',r,anonymous);check(code==400 and count()==before,'무쿠키 엔드포인트 차단')
    bad=dict(r,req_tx='mod');code,_=req('/shop/kcp/pp_cli_hub.php',bad);check(code==400 and count()==before,'직접 취소 차단')
    bad=dict(r,cash_token='0'*64);code,_=req('/shop/kcp/pp_cli_hub.php',bad);check(code==400 and count()==before,'CSRF 차단')
    code,_=req('/shop/kcp/pp_cli_hub.php');check(code==400 and count()==before,'GET 차단')
    code,_=req('/shop/kcp/pp_cli_result.php',{'res_cd':'0000','res_msg':'FAKE'},anonymous);check(code==400,'위조 결과 POST 차단')
    code,text=req('/shop/kcp/pp_cli_hub.php',dict(r,amt_tot='999999',corp_type='1',mod_value='OTHER'));check(code==200 and '현금영수증이 발급되었습니다' in text and '1,000원' in text and count()==before+1,'실제 처리파일 정상 발급·서버 결과 표시')
    code,_=req('/shop/kcp/pp_cli_hub.php',r);check(code==400 and count()==before+1,'실제 엔드포인트 재발급 차단')
    record=json.loads((work/'gateway.jsonl').read_text().splitlines()[-1]);check(record['code']=='07010000' and record['host']=='testpaygw.kcp.co.kr','테스트 PG 전송 경로')
    req('/fixture.php?fault=sql');r,_=form();before=count();code,text=req('/shop/kcp/pp_cli_hub.php',r)
    check(code==200 and '발급을 취소했습니다' in text and count()==before+2,'저장 실패 보상 취소 응답')
    record=json.loads((work/'gateway.jsonl').read_text().splitlines()[-1]);check(record['code']=='07020000' and record['mod']['mod_value']=='12345678901234','내부 보상 취소 거래번호 결합')
    print('HTTP TOTAL',len(checks),'ARTIFACTS',work,flush=True)
finally:
    proc.terminate();proc.wait(timeout=10);log.close()
    errors=(work/'server.log').read_text()
    assert not re.search(r'PHP (Warning|Fatal|Deprecated|Notice)',errors),errors
