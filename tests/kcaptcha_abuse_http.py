#!/usr/bin/env python3
"""실제 설치본의 common.php·세션·암호화·SQL·기본 스킨으로 요청 제한을 검증한다.
격리 MySQL(root/빈 비밀번호) 소켓 필요. 메일 전송만 공식 mailer 훅으로 캡처한다.
실행: python3 tests/kcaptcha_abuse_http.py /path/to/isolated/mysql.sock
선택: G5_TEST_PLAYWRIGHT=/path/to/playwright 모듈을 설정하면 PC·모바일 브라우저 검사도 실행한다.
"""
from pathlib import Path
from concurrent.futures import ThreadPoolExecutor
import base64, http.cookiejar, json, os, re, shutil, signal, socket, subprocess, sys, tempfile, time
import urllib.request, urllib.parse

ROOT = Path(__file__).resolve().parents[1]
SOCKET = sys.argv[1]
DB = 'g5_captcha_installed_' + str(os.getpid())
checks = 0

def sql(query):
    return subprocess.check_output(['mysql','--no-defaults','--socket='+SOCKET,'-uroot','-N'],input="SET SESSION sql_mode='';"+query,text=True).strip()

def check(ok, label):
    global checks
    assert ok, label
    checks += 1
    print('PASS', label, flush=True)

sql('CREATE DATABASE '+DB)
server = None
try:
    with tempfile.TemporaryDirectory(prefix='g5-captcha-installed-') as temp:
        path=Path(temp)
        # symlink된 common.php는 원본 설치 경로를 참조하므로 추적 파일을 별도로 복사한다.
        names=subprocess.check_output(['git','ls-files','-z'],cwd=ROOT).decode().split('\0')
        names=sorted(set(names+[str(p.relative_to(ROOT)) for p in (ROOT/'migrations').glob('*.sql')]))
        for name in names:
            if not name or name.startswith(('tests/','bin/')) or not (ROOT/name).is_file(): continue
            target=path/name; target.parent.mkdir(parents=True,exist_ok=True)
            shutil.copyfile(ROOT/name,target)
        (path/'data').mkdir(exist_ok=True)
        with socket.socket() as sock:
            sock.bind(('127.0.0.1',0)); port=sock.getsockname()[1]
        base='http://127.0.0.1:'+str(port)
        config=(ROOT/'config.php').read_text().replace("define('G5_DOMAIN', '');", "define('G5_DOMAIN', '"+base+"');")
        (path/'config.php').write_text(config)
        post=dict(mysql_host='localhost',mysql_user='root',mysql_pass='',mysql_db=DB,table_prefix='fixture_',admin_id='fixtureadmin',admin_pass='fixture-admin-password',admin_name='검증관리자',admin_email='admin@example.com',g5_install=1,g5_shop_prefix='fixture_shop_',g5_shop_install=0)
        encoded=base64.b64encode(json.dumps(post).encode()).decode()
        installer=path/'install_fixture.php'
        installer.write_text("<?php $_POST=json_decode(base64_decode('"+encoded+"'),true); $_SERVER['SERVER_NAME']='127.0.0.1'; $_SERVER['SCRIPT_NAME']='/install/install_db.php'; $_SERVER['REQUEST_METHOD']='POST'; chdir(__DIR__.'/install'); require './install_db.php';")
        run=subprocess.run(['php','-d','mysqli.default_socket='+SOCKET,str(installer)],capture_output=True,text=True)
        check(run.returncode==0 and (path/'data/dbconfig.php').exists() and '설치가 완료되었습니다' in run.stdout,'실제 신규 설치 및 사용자 지정 접두어')
        installer.unlink()
        sql('USE '+DB+"; UPDATE fixture_config SET cf_email_use=1, cf_captcha='kcaptcha', cf_captcha_mp3='basic', cf_theme='', cf_use_email_certify=0; CREATE TABLE fixture_mail_capture (recipient varchar(100)); CREATE TABLE fixture_query_audit (value int);")
        # 최초 관리자의 전체 스키마를 복제하되 일반 회원으로 만들어 인증 관련 필수값을 유지한다.
        sql('USE '+DB+"; CREATE TEMPORARY TABLE test_member AS SELECT * FROM fixture_member LIMIT 1; UPDATE test_member SET mb_no=2, mb_id='member', mb_name='검증회원', mb_nick='검증회원', mb_email='member@example.com', mb_level=2, mb_lost_certify='original'; INSERT INTO fixture_member SELECT * FROM test_member;")
        (path/'extend/zz_test_mail.extend.php').write_text("""<?php
if (!defined('_GNUBOARD_')) exit;
function test_capture_mail($name,$from,$to) {
    sql_query("INSERT INTO fixture_mail_capture VALUES ('".sql_real_escape_string($to)."')");
    $mode = @file_get_contents(G5_DATA_PATH.'/mail_mode');
    if ($mode === 'newer') sql_query("UPDATE fixture_member SET mb_lost_certify='newer-request' WHERE mb_id='member'");
    return array('return'=>$mode !== 'fail' && $mode !== 'newer');
}
add_replace('mailer','test_capture_mail',10,3);
function test_audit_query($result,$sql) {
    if (strpos(ltrim($sql),'UPDATE `fixture_abuse_rate`') === 0 ||
        (stripos(ltrim($sql),'SELECT') === 0 && strpos($sql,'fixture_member') !== false)) {
        $mode = @file_get_contents(G5_DATA_PATH.'/rate_audit_mode');
        if ($mode === 'insert') sql_query('INSERT INTO fixture_query_audit VALUES (1)');
        if ($mode === 'select') sql_query('SELECT 1');
    }
}
add_event('sql_query_after','test_audit_query',10,2);
""")
        (path/'data/rate_audit_mode').write_text('insert')
        # 시험 전용 진입점도 운영 common.php와 라이브러리를 그대로 사용한다.
        (path/'test_rate.php').write_text("""<?php
require './common.php';
require_once G5_LIB_PATH.'/abuse_rate.lib.php';
session_write_close();
echo json_encode(g5_abuse_rate_allow('concurrent','one',3600,3));
""")
        (path/'test_ready.php').write_text("<?php require './common.php'; require_once G5_LIB_PATH.'/abuse_rate.lib.php'; echo json_encode(g5_abuse_rate_storage_ready());")
        (path/'test_migrate.php').write_text("<?php require './common.php'; require G5_LIB_PATH.'/migration.lib.php'; echo json_encode(g5_migration_run()); ")
        (path/'test_keys.php').write_text("<?php require './common.php'; echo json_encode(array('key'=>G5_TOKEN_ENCRYPTION_KEY));")
        log=open(path/'server.log','w+')
        env=dict(os.environ,PHP_CLI_SERVER_WORKERS='8')
        server=subprocess.Popen(['php','-d','mysqli.default_socket='+SOCKET,'-d','opcache.enable_cli=0','-d','sendmail_path=/bin/false','-S','127.0.0.1:'+str(port),'-t',str(path)],env=env,stdout=log,stderr=log,start_new_session=True)
        for _ in range(100):
            try:
                with socket.create_connection(('127.0.0.1',port),timeout=.1): break
            except OSError: time.sleep(.05)
        else: raise RuntimeError('PHP server startup failed')
        def client(): return urllib.request.build_opener(urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
        def request(c,url,data=None):
            body=urllib.parse.urlencode(data).encode() if data is not None else None
            req=urllib.request.Request(base+url,data=body,headers={'Referer':base+'/bbs/password_lost.php','User-Agent':'Gnuboard-local-test'})
            try: return c.open(req,timeout=20).read()
            except urllib.error.HTTPError:
                log.flush();log.seek(0); print(log.read()[-5000:]);raise
        def html(c,url,data=None): return request(c,url,data).decode()
        refs=[(path/('plugin/kcaptcha/mp3/basic/'+str(n)+'.mp3')).read_bytes() for n in range(10)]
        def challenge(c):
            request(c,'/plugin/kcaptcha/kcaptcha_session.php',{})
            url=html(c,'/plugin/kcaptcha/kcaptcha_mp3.php',{})
            audio=request(c,url.replace(base,''));size=len(refs[0])
            return ''.join(str(refs.index(audio[i:i+size])) for i in range(0,len(audio),size))
        def lost(c,answer,email='member@example.com'):
            return html(c,'/bbs/password_lost2.php',{'captcha_key':answer,'mb_email':email})
        def mail_count(): return int(sql('SELECT COUNT(*) FROM '+DB+'.fixture_mail_capture'))
        def cert(): return sql("SELECT mb_lost_certify FROM "+DB+".fixture_member WHERE mb_id='member'")
        def clear_rates(): sql('TRUNCATE '+DB+'.fixture_abuse_rate')
        # DB 내부 식별자로 정책별 사용량을 관찰한다. 실제 운영 키·개인정보는 출력하지 않는다.
        import hmac, hashlib
        secret=json.loads(request(client(),'/test_keys.php'))['key']
        def rate_key(scope,identity): return hmac.new(secret.encode(),(scope+'\0'+identity).encode(),hashlib.sha256).hexdigest()
        global_key=rate_key('password_lost_global','all')
        def global_state(): return sql("SELECT ar_next FROM "+DB+".fixture_abuse_rate WHERE ar_key='"+global_key+"'")
        check(request(client(),'/test_ready.php')==b'true','신규 설치 저장소 준비 상태')
        before=sql('SELECT COUNT(*) FROM '+DB+'.fixture_abuse_rate')
        request(client(),'/test_ready.php')
        check(sql('SELECT COUNT(*) FROM '+DB+'.fixture_abuse_rate')==before,'준비 상태 조회에서 카운터 생성 없음')
        for suffix in ['?device=pc','?device=mobile']:
            page=html(client(),'/bbs/password_lost.php'+suffix)
            check('name="mb_email"' in page and 'captcha_key' in page,'실제 기본 스킨 렌더링 '+suffix)
        c=client();answer=challenge(c)
        check(len(answer)==6,'실제 세션·암호화·음성 생성 경로: 복원 문제 잔존 확인')
        check(request(c,'/plugin/kcaptcha/kcaptcha_result.php',{'captcha_key':answer})==b'1','기존 AJAX 사전 검사')
        check('안내 메일이 발송됩니다' in lost(c,answer) and mail_count()==1,'실제 common.php와 mailer 훅을 통한 정상 복구 요청')
        old_cert=cert();old_global=global_state()
        check('안내 메일이 발송됩니다' in lost(c,answer),'공통 캡차 동작 유지 및 수신자 제한 일반 응답')
        c2=client();a2=challenge(c2);lost(c2,a2,'MEMBER@example.com')
        check(mail_count()==1 and cert()==old_cert,'동일 정답·새 세션·새 정답·대문자 이메일 반복에서 부작용 차단')
        check(global_state()==old_global,'수신자 제한 요청은 전체 발송 한도 미소모')
        check(int(sql('SELECT COUNT(*) FROM '+DB+'.fixture_query_audit'))>0,'같은 업무 연결의 INSERT 감사 훅 실행 중에도 제한 유지')
        (path/'data/rate_audit_mode').write_text('select')
        lost(c,answer)
        check(mail_count()==1 and cert()==old_cert,'SELECT 감사 훅 실행 중에도 중복 발송 차단')
        (path/'data/rate_audit_mode').write_text('insert')
        unknown=client();lost(unknown,challenge(unknown),'unknown@example.com')
        check(global_state()==old_global and mail_count()==1,'미등록 이메일은 전체 발송 한도 미소모')
        for _ in range(14): msg=lost(client(),'incorrect')
        check('요청이 많습니다' in msg,'캡차 성공 여부 및 세션 교체와 독립적인 IP 제한')
        clear_rates()
        actors=[]
        for _ in range(8):
            actor=client();actors.append((actor,challenge(actor)))
        before=mail_count()
        with ThreadPoolExecutor(max_workers=8) as pool: messages=list(pool.map(lambda pair:lost(*pair),actors))
        check(all('안내 메일이 발송됩니다' in m for m in messages) and mail_count()==before+1,'실제 독립 세션 8개 동시 제출에서 메일 1회: '+str(mail_count()-before)+' / '+str([re.findall(r'alert\((.*?)\);', m) for m in messages if '안내 메일이 발송됩니다' not in m]))
        clear_rates()
        with ThreadPoolExecutor(max_workers=16) as pool: results=list(pool.map(lambda _:request(client(),'/test_rate.php'),range(24)))
        check(results.count(b'true')==3 and results.count(b'false')==21,'동시 요청 24건 중 burst 3 엄수: '+str(results))
        old=sql('SELECT MAX(ar_next) FROM '+DB+'.fixture_abuse_rate')
        request(client(),'/test_rate.php')
        check(sql('SELECT MAX(ar_next) FROM '+DB+'.fixture_abuse_rate')==old,'거부된 요청의 회복 시각 보존')
        sql('UPDATE '+DB+'.fixture_abuse_rate SET ar_next=UNIX_TIMESTAMP()-1')
        check(request(client(),'/test_rate.php')==b'true','시간 경과 후 허용량 회복')
        clear_rates()
        sql("INSERT INTO "+DB+".fixture_abuse_rate VALUES ('"+global_key+"',UNIX_TIMESTAMP()+100)")
        actor=client();old_cert=cert();before=mail_count()
        check('안내 메일이 발송됩니다' in lost(actor,challenge(actor)),'전체 발송 한도 초과도 회원 존재 여부 비노출')
        check(mail_count()==before and cert()==old_cert,'전체 한도 차단 시 메일·인증값 보존')
        recipient_key=rate_key('password_lost_recipient','member@example.com')
        check(sql("SELECT COUNT(*) FROM "+DB+".fixture_abuse_rate WHERE ar_key='"+recipient_key+"'")=='0','전체 한도 거부 시 수신자 한도 미소모')
        sql("UPDATE "+DB+".fixture_abuse_rate SET ar_next=UNIX_TIMESTAMP()-1 WHERE ar_key='"+global_key+"'")
        retry=client();lost(retry,challenge(retry))
        check(mail_count()==before+1,'전체 한도 회복 직후 수신자 재요청 발송 성공')
        # 여러 한도 중 두 번째 UPDATE를 실패시켜 첫 번째 갱신도 복구되는지 검사한다.
        clear_rates()
        failed_key=max(recipient_key,global_key)
        sql("USE "+DB+";\nDELIMITER //\nCREATE TRIGGER rate_failure BEFORE UPDATE ON fixture_abuse_rate FOR EACH ROW BEGIN IF NEW.ar_key='"+failed_key+"' THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='fixture rate failure'; END IF; END//\nDELIMITER ;\n")
        actor=client();old_cert=cert();before=mail_count()
        check('관리자에게 문의' in lost(actor,challenge(actor)),'두 번째 한도 갱신 오류를 서비스 오류로 거부')
        check(sql("SELECT COUNT(*) FROM "+DB+".fixture_abuse_rate WHERE ar_key IN ('"+recipient_key+"','"+global_key+"')")=='0' and cert()==old_cert and mail_count()==before,'중간 SQL 실패 시 두 한도·메일·인증값 모두 보존')
        sql('DROP TRIGGER '+DB+'.rate_failure')
        clear_rates()
        sql('UPDATE '+DB+'.fixture_config SET cf_email_use=0')
        actor=client();lost(actor,challenge(actor))
        check(global_state()=='' and cert()==old_cert and mail_count()==before,'메일 사용 중지 시 발송 한도·인증값 보존')
        sql('UPDATE '+DB+'.fixture_config SET cf_email_use=1')
        # 최고관리자 로그인은 실제 로그인 처리 경로를 사용한다.
        admin=client();request(admin,'/bbs/login_check.php',{'mb_id':'fixtureadmin','mb_password':'fixture-admin-password','url':base+'/adm/'})
        page=html(admin,'/adm/dbupgrade.php')
        notice='비밀번호 찾기 요청 제한 저장소를 확인할 수 없습니다.'
        check('DB 업그레이드' in page and notice not in page,'실제 최고관리자 화면 정상 상태')
        sql('ALTER TABLE '+DB+'.fixture_abuse_rate DROP PRIMARY KEY')
        check(request(client(),'/test_ready.php')==b'false' and notice in html(admin,'/adm/dbupgrade.php'),'기본키가 없는 잘못된 저장소 구조도 관리자에게 안내')
        actor=client();before=mail_count();old_cert=cert()
        check('관리자에게 문의' in lost(actor,challenge(actor)) and mail_count()==before and cert()==old_cert,'기본키 누락 상태의 첫 요청부터 실제 발송 거부')
        sql('ALTER TABLE '+DB+'.fixture_abuse_rate ADD PRIMARY KEY (ar_key)')
        sql('ALTER TABLE '+DB+'.fixture_abuse_rate ENGINE=MyISAM')
        actor=client()
        check(request(client(),'/test_ready.php')==b'false' and '관리자에게 문의' in lost(actor,challenge(actor)) and mail_count()==before,'비트랜잭션 엔진은 준비 상태·실제 처리 모두 거부')
        sql("DELETE FROM "+DB+".fixture_migrations WHERE migration_id='20261008_002_abuse_rate_transaction'")
        converted=json.loads(request(client(),'/test_migrate.php'))
        check(converted['success'] and request(client(),'/test_ready.php')==b'true','실제 후속 마이그레이션으로 InnoDB 전환')
        clear_rates()
        missing=client();a=challenge(missing)
        sql('DROP TABLE '+DB+'.fixture_abuse_rate')
        check(request(client(),'/test_ready.php')==b'false','누락 저장소 준비 상태 거부')
        msg=lost(missing,a)
        check('관리자에게 문의' in msg and '요청이 많습니다' not in msg,'저장소 오류를 한도 초과와 구분')
        check(mail_count()==before and cert()==old_cert,'저장소 오류에서도 부작용 차단')
        page=html(admin,'/adm/dbupgrade.php')
        check(notice in page and 'dbupgrade.php' in page,'실제 최고관리자 화면에서 업그레이드 안내')
        check(sql("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='"+DB+"' AND table_name='fixture_abuse_rate'")=='0','관리자 화면 조회로 DDL 실행하지 않음')
        # 배포 전 상태를 재현하고 실제 마이그레이션 실행기로 복구한다.
        sql("DELETE FROM "+DB+".fixture_migrations WHERE migration_id IN ('20261008_001_abuse_rate','20261008_002_abuse_rate_transaction')")
        migrated=json.loads(request(client(),'/test_migrate.php'))
        check(migrated['success'] and request(client(),'/test_ready.php')==b'true','실제 마이그레이션 적용 후 준비 상태 회복')
        check(notice not in html(admin,'/adm/dbupgrade.php'),'업그레이드 후 관리자 안내 해제')
        actor=client();lost(actor,challenge(actor))
        check(mail_count()==before+1,'업그레이드 후 정상 발송 재개')
        # 잘못된 링크는 기존 인증값을 보존하고, 정상 링크는 동시 요청 중 한 번만 사용한다.
        previous=cert()
        wrong=html(client(),'/bbs/password_lost_certify.php?mb_no=2&mb_nonce=wrong')
        check(wrong.strip()=='Error' and cert()==previous,'잘못된 복구 토큰이 기존 링크를 보존')
        nonce,password_hash=previous.split(' ',1)
        with ThreadPoolExecutor(max_workers=8) as pool:
            recovered=list(pool.map(lambda _:html(client(),'/bbs/password_lost_certify.php?mb_no=2&mb_nonce='+nonce),range(8)))
        check(sum('비밀번호가 변경됐습니다' in message for message in recovered)==1 and cert()=='','정상 복구 링크 동시 요청 중 한 번만 소모')
        check(sql("SELECT mb_password FROM "+DB+".fixture_member WHERE mb_id='member'")==password_hash,'링크 소모와 비밀번호 변경을 원자적으로 처리')
        clear_rates(); actor=client(); lost(actor,challenge(actor)); previous=cert()
        mail_mode=path/'data/mail_mode'
        mail_mode.write_text('fail')
        clear_rates(); before=mail_count(); actor=client(); msg=lost(actor,challenge(actor))
        check('안내 메일이 발송됩니다' in msg and cert()==previous and mail_count()==before+1,'메일러 실패 시 기존 복구 링크 복원 및 조건부 안내: '+str({'message':'안내 메일이 발송됩니다' in msg,'preserved':cert()==previous,'attempts':mail_count()-before}))
        remaining=int(sql("SELECT ar_next-UNIX_TIMESTAMP() FROM "+DB+".fixture_abuse_rate WHERE ar_key='"+recipient_key+"'"))
        check(0<remaining<=60 and global_state()!='','메일 실패 시 수신자 대기 최대 60초, 전체 한도 유지')
        actor=client(); lost(actor,challenge(actor))
        check(mail_count()==before+1 and cert()==previous,'메일 실패 직후 무제한 재시도 차단')
        sql("UPDATE "+DB+".fixture_abuse_rate SET ar_next=UNIX_TIMESTAMP()-1 WHERE ar_key='"+recipient_key+"'")
        mail_mode.write_text('success')
        actor=client(); lost(actor,challenge(actor))
        check(mail_count()==before+2 and cert()!=previous,'실패 대기 회복 후 정상 발송 재개')
        current_next=int(sql("SELECT ar_next FROM "+DB+".fixture_abuse_rate WHERE ar_key='"+recipient_key+"'"))
        (path/'test_stale_reservation.php').write_text("<?php require './common.php'; require G5_LIB_PATH.'/abuse_rate.lib.php'; g5_password_lost_mail_failed(array('key'=>'"+recipient_key+"','next'=>"+str(current_next-1)+"));")
        request(client(),'/test_stale_reservation.php')
        check(sql("SELECT ar_next FROM "+DB+".fixture_abuse_rate WHERE ar_key='"+recipient_key+"'")==str(current_next),'오래된 실패 예약이 새 수신자 한도를 변경하지 않음')
        mail_mode.write_text('newer'); clear_rates(); actor=client(); lost(actor,challenge(actor))
        check(cert()=='newer-request','메일 실패 복원이 더 최신 인증값을 덮어쓰지 않음')
        mail_mode.write_text('success')
        # 설정은 기본값으로 즉시 동작하며, 저장값 오류는 제한을 생략하지 않는다.
        defaults=dict(ip=dict(interval=6,burst=10),recipient=dict(interval=300,burst=1),global_=dict(interval=1,burst=30))
        policy={'ip':defaults['ip'],'recipient':defaults['recipient'],'global':defaults['global_']}
        policy['ip']['burst']=1
        sql("UPDATE "+DB+".fixture_config SET cf_password_lost_policy='"+json.dumps(policy)+"'")
        clear_rates(); lost(client(),'incorrect'); msg=lost(client(),'incorrect')
        check('요청이 많습니다' in msg,'관리자 저장 정책을 실제 요청에서 적용')
        sql("UPDATE "+DB+".fixture_config SET cf_password_lost_policy='invalid'")
        check('관리자에게 문의' in lost(client(),'incorrect'),'손상된 저장 정책을 오류로 거부')
        sql("UPDATE "+DB+".fixture_config SET cf_password_lost_policy='0'")
        check('관리자에게 문의' in lost(client(),'incorrect'),'0 문자열 정책도 기본값으로 우회하지 않음')
        sql("UPDATE "+DB+".fixture_config SET cf_password_lost_policy=''")
        clear_rates()
        check('자동등록방지' in lost(client(),'incorrect'),'설정이 비어 있으면 배포 기본값 사용')
        # 새 컬럼은 기존 설치에도 실제 마이그레이션으로 추가한다.
        sql('ALTER TABLE '+DB+'.fixture_config DROP COLUMN cf_password_lost_policy')
        sql("DELETE FROM "+DB+".fixture_migrations WHERE migration_id='20261008_003_password_lost_policy'")
        clear_rates()
        check('자동등록방지' in lost(client(),'incorrect'),'설정 컬럼 추가 전에도 기본값으로 기존 설치 호환')
        check(json.loads(request(client(),'/test_migrate.php'))['success'] and 'password_lost_policy[ip][interval]' in html(admin,'/adm/config_form.php'),'정책 마이그레이션과 최고관리자 설정 화면')
        clear_rates()
        (path/'test_structure_change.php').write_text("<?php require './common.php'; require G5_LIB_PATH.'/abuse_rate.lib.php'; g5_password_lost_rate_allow('ip','schema-change'); sql_query('ALTER TABLE fixture_abuse_rate ENGINE=MyISAM'); echo json_encode(g5_password_lost_mail_allow('schema@example.com'));")
        check(request(client(),'/test_structure_change.php')==b'null','같은 요청의 두 트랜잭션 사이 구조 변경도 거부')
        sql('ALTER TABLE '+DB+'.fixture_abuse_rate ENGINE=InnoDB')
        # 제한 전용 연결은 작업자가 장시간 대기하지 않도록 현대 MySQL에서 2초에 중단한다.
        clear_rates(); locked_key=rate_key('concurrent','one')
        sql("INSERT INTO "+DB+".fixture_abuse_rate VALUES ('"+locked_key+"',0)")
        lock_script=path/'hold_lock.php'
        lock_script.write_text("<?php $db=new mysqli('localhost','root','','"+DB+"',0,'"+SOCKET+"'); $db->begin_transaction(); $db->query(\"SELECT * FROM fixture_abuse_rate WHERE ar_key='"+locked_key+"' FOR UPDATE\"); echo \"locked\\n\"; flush(); sleep(4); $db->rollback();")
        holder=subprocess.Popen(['php',str(lock_script)],stdout=subprocess.PIPE,text=True)
        try:
            assert holder.stdout.readline().strip()=='locked'
            started=time.monotonic(); result=request(client(),'/test_rate.php'); elapsed=time.monotonic()-started
            check(result==b'null' and elapsed<3.5,'행 잠금 대기를 약 2초에서 중단하고 오류로 거부')
        finally: holder.wait(timeout=8)
        check(request(client(),'/test_rate.php')==b'true','잠금 해제 후 정상 요청 회복')
        if os.environ.get('G5_TEST_PLAYWRIGHT'):
            clear_rates()
            for number,device in [(3,'pc'),(4,'mobile')]:
                sql('USE '+DB+"; CREATE TEMPORARY TABLE browser_member AS SELECT * FROM fixture_member WHERE mb_id='member'; UPDATE browser_member SET mb_no="+str(number)+", mb_id='browser"+device+"', mb_nick='browser"+device+"', mb_email='browser"+device+"@example.com'; INSERT INTO fixture_member SELECT * FROM browser_member;")
            before_browser=mail_count()
            node=subprocess.run(['node',str(ROOT/'tests/kcaptcha_abuse_browser.js'),base,str(path)],env=os.environ,text=True,capture_output=True)
            check(node.returncode==0,'PC·모바일 Chromium 정상 제출: '+node.stdout.strip()+node.stderr)
            check(mail_count()==before_browser+2,'PC·모바일 브라우저 제출 각각 메일 훅 1회')
        log.flush();log.seek(0);output=log.read()
        check('Fatal error' not in output and 'Uncaught' not in output,'PHP 치명 오류 없음')
        print('통과:',checks,'(실제 설치 HTTP/DB, 외부 메일 전송 없음)',flush=True)
finally:
    if server:
        os.killpg(server.pid,signal.SIGTERM);server.wait(timeout=10)
    sql('DROP DATABASE '+DB)
