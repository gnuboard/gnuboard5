#!/usr/bin/env python3
"""실행: python3 tests/board_access_http.py

Linux의 PHP/mariadbd/mariadb-install-db가 필요하다. 전용 임시 MariaDB(네트워크
비활성)와 localhost PHP 서버에 새로 설치하며 운영 설정/DB를 읽지 않는다.
실제 처리 파일, 토큰, 로그인, 세션, DB를 사용한다. 테스트 사본의 fixture는
CAPTCHA 정답 준비와 구 세션 주입/관찰에만 사용하고 제품 권한 함수는 대체하지 않는다.
"""
from pathlib import Path
import base64, html, http.cookiejar, json, os, re, shutil, socket, subprocess, tempfile, time
import urllib.request, urllib.parse, urllib.error

root=Path(__file__).resolve().parents[1]
work=Path(tempfile.mkdtemp(prefix='g5-board-access-'))
site=work/'site'; dbdir=work/'db'; sock=str(work/'mysql.sock')
checks=[]; dbproc=None; web=None
def check(ok,name,detail=''):
    if not ok: raise AssertionError(name+' '+(str(re.findall(r'alert\([^\n]*',str(detail))) or str(detail))[:1600])
    checks.append(name); print('PASS '+name,flush=True)
def sql(query):
    return subprocess.check_output(['mariadb','--no-defaults','--socket='+sock,'-uroot','-NB'],
        input="SET SESSION sql_mode=''; USE audit; "+query,text=True).strip()
class NoRedirect(urllib.request.HTTPRedirectHandler):
    def redirect_request(self,*args,**kwargs): return None
def client():
    return urllib.request.build_opener(NoRedirect(),urllib.request.HTTPCookieProcessor(http.cookiejar.CookieJar()))
def request(c,path,data=None,mobile=False):
    headers={'Origin':base,'Referer':base+'/bbs/board.php?bo_table=free',
       'User-Agent':'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) Mobile' if mobile else 'Mozilla/5.0 Audit'}
    req=urllib.request.Request(base+path,urllib.parse.urlencode(data).encode() if data is not None else None,headers)
    try: res=c.open(req,timeout=15)
    except urllib.error.HTTPError as e: res=e
    return res.status,dict(res.headers),res.read().decode('utf-8',errors='replace')
def login(who):
    c=client(); request(c,'/bbs/login_check.php',{'mb_id':who,'mb_password':'account-pass'})
    check(json.loads(request(c,'/audit_fixture.php')[2])['member']==who,'로그인 '+who)
    return c
def view(c,ident,mobile=False): return request(c,f'/bbs/board.php?bo_table=free&wr_id={ident}',mobile=mobile)
def token(c): return json.loads(request(c,'/bbs/write_token.php',{'bo_table':'free'})[2])['token']
def save(c,**kw):
    request(c,'/audit_fixture.php?prepare=1')
    p=dict(bo_table='free',w='',wr_subject='test subject',wr_content='test body',secret='',
        html='',mail='',wr_name='guest',wr_password='guest-pass',wr_email='',wr_homepage='',captcha_key='12345')
    p.update(kw);p['token']=token(c)
    return request(c,'/bbs/write_update.php',p)
def comment(c,ident,**kw):
    p=dict(bo_table='free',wr_id=ident,w='c',comment_id=0,wr_content='test comment',wr_name='guest',
        wr_password='guest-pass',wr_secret='',captcha_key='12345')
    p.update(kw);p['token']=json.loads(request(c,'/bbs/ajax.comment_token.php')[2])['token']
    return request(c,'/bbs/write_comment_update.php',p)
def password(c,ident,mode='s',pwd='account-pass'):
    return request(c,'/bbs/password_check.php',dict(bo_table='free',wr_id=ident,w=mode,wr_password=pwd))
def snapshot():
    return sql("SELECT COUNT(*),SUM(wr_comment),SUM(LENGTH(wr_content)) FROM g5_write_free;"
      "SELECT bo_count_write,bo_count_comment FROM g5_board WHERE bo_table='free';"
      "SELECT COUNT(*) FROM g5_board_file;SELECT COUNT(*) FROM g5_point;SELECT COUNT(*) FROM g5_scrap;")
def denied_write(name,fn):
    before=snapshot(); res=fn()
    check(snapshot()==before and 'alert(' in res[2],name,res)
def auth(c): return json.loads(request(c,'/audit_fixture.php')[2])['auth']
try:
    shutil.copytree(root,site,ignore=shutil.ignore_patterns('.git','data','__pycache__'))
    (site/'data').mkdir();dbdir.mkdir()
    subprocess.run(['mariadb-install-db','--no-defaults','--datadir='+str(dbdir),
        '--auth-root-authentication-method=normal','--skip-test-db'],check=True,stdout=subprocess.DEVNULL,stderr=subprocess.PIPE)
    dbproc=subprocess.Popen(['mariadbd','--no-defaults','--datadir='+str(dbdir),'--socket='+sock,
        '--pid-file='+str(work/'mysql.pid'),'--skip-networking','--log-error='+str(work/'mysql.log')],stdout=subprocess.DEVNULL,stderr=subprocess.DEVNULL)
    for _ in range(100):
        if Path(sock).exists(): break
        if dbproc.poll() is not None: raise RuntimeError((work/'mysql.log').read_text())
        time.sleep(.1)
    subprocess.run(['mariadb','--no-defaults','--socket='+sock,'-uroot','-e','CREATE DATABASE audit'],check=True)
    post=dict(mysql_host='localhost',mysql_user='root',mysql_pass='',mysql_db='audit',table_prefix='g5_',
        admin_id='admin',admin_pass='account-pass',admin_name='관리자',admin_email='admin@example.invalid',g5_install=1,g5_shop_install=0,g5_shop_prefix='g5_shop_')
    encoded=base64.b64encode(json.dumps(post).encode()).decode()
    (site/'run_install.php').write_text("<?php $_POST=json_decode(base64_decode('"+encoded+"'),true); $_SERVER['SERVER_NAME']='localhost'; $_SERVER['SCRIPT_NAME']='/install/install_db.php'; $_SERVER['REQUEST_METHOD']='POST'; chdir(__DIR__.'/install'); require './install_db.php';")
    install=subprocess.run(['php','-d','mysqli.default_socket='+sock,str(site/'run_install.php')],capture_output=True,text=True)
    (work/'install.log').write_text(install.stdout+install.stderr)
    check(install.returncode==0 and (site/'data/dbconfig.php').exists(),'실제 설치 완료',install.stderr+install.stdout[-1000:])
    hash_code="define('_GNUBOARD_',true);require '"+str(site/'lib/pbkdf2.compat.php')+"';echo create_hash('guest-pass');"
    guesthash=subprocess.check_output(['php','-r',hash_code],text=True)
    account_hash=sql("SELECT mb_password FROM g5_member WHERE mb_id='admin'")
    sql("UPDATE g5_config SET cf_theme='',cf_email_use=0,cf_use_point=0,cf_delay_sec=0,cf_cert_use=1,cf_use_email_certify=0,cf_bbs_rewrite=0;"
        "UPDATE g5_board SET bo_use_secret=1,bo_read_level=1,bo_write_level=1,bo_reply_level=1,bo_comment_level=1,bo_write_point=0,bo_comment_point=0,bo_read_point=0,bo_download_point=0,bo_count_modify=0,bo_use_captcha=0,bo_use_cert='',bo_use_email=0 WHERE bo_table='free';")
    for who in ['owner','other','groupadmin','boardadmin']:
        sql(f"INSERT INTO g5_member SET mb_id='{who}',mb_password='{account_hash}',mb_name='{who}',mb_nick='{who}',mb_email='{who}@example.invalid',mb_level=2,mb_point=10000,mb_datetime=NOW(),mb_today_login=NOW(),mb_email_certify=NOW();")
    gr=sql("SELECT gr_id FROM g5_board WHERE bo_table='free'")
    sql(f"UPDATE g5_group SET gr_admin='groupadmin' WHERE gr_id='{gr}';UPDATE g5_board SET bo_admin='boardadmin' WHERE bo_table='free';")
    # id, thread, reply, parent, is_comment, author, secret, password, body
    rows=[(1,-1,'',1,0,'owner','secret','', 'ROOT_PRIVATE_68'),
          (2,-2,'',2,0,'owner','','','PUBLIC_68'),
          (3,-3,'',3,0,'','secret',guesthash,'GUEST_PRIVATE_68'),
          (4,-1,'A',4,0,'other','','','OTHER_REPLY_68'),
          (5,-1,'',1,1,'other','secret','','MEMBER_COMMENT_68'),
          (6,-1,'',1,1,'','secret',guesthash,'GUEST_COMMENT_68'),
          (7,-3,'A',7,0,'admin','secret',guesthash,'INHERITED_REPLY_68'),
          (8,-3,'B',8,0,'admin','secret',account_hash,'OTHER_PASSWORD_REPLY_68'),
          (9,-9,'',9,0,'other','','','OWN_PUBLIC_68'),
          (10,-1,'B',10,0,'admin','secret','','OWNER_ADMIN_REPLY_68'),
          (11,-2,'',2,1,'','secret',guesthash,'PUBLIC_PARENT_COMMENT_68')]
    for ident,num,reply,parent,isc,owner,secret,pwd,body in rows:
        sql(f"INSERT INTO g5_write_free SET wr_id={ident},wr_num={num},wr_reply='{reply}',wr_parent={parent},wr_is_comment={isc},wr_comment={ident if isc else 0},mb_id='{owner}',wr_option='{secret}',wr_password='{pwd}',wr_subject='subject {ident}',wr_content='{body}',wr_name='test',wr_datetime=NOW(),wr_last=NOW(),wr_ip='127.0.0.1',wr_link1='https://example.invalid/PRIVATE-LINK-68';")
    sql("UPDATE g5_board SET bo_count_write=8,bo_count_comment=3 WHERE bo_table='free';")
    files=site/'data/file/free';files.mkdir(parents=True,exist_ok=True)
    (files/'audit.txt').write_text('PRIVATE_ATTACHMENT_68')
    sql("UPDATE g5_write_free SET wr_file=1 WHERE wr_id IN (1,3);UPDATE g5_board SET bo_download_level=1 WHERE bo_table='free';")
    for ident in [1,3]:
        sql(f"INSERT INTO g5_board_file SET bo_table='free',wr_id={ident},bf_no=0,bf_source='audit.txt',bf_file='audit.txt',bf_filesize=21,bf_datetime=NOW();")
    (site/'audit_fixture.php').write_text('''<?php
include './common.php';
if (isset($_GET['prepare'])) {
    set_session('ss_datetime',0);
    set_session('ss_captcha_key', get_string_encrypt(md5(sha1($_SERVER['REMOTE_ADDR'])).'12345'));
    set_session('ss_captcha_count',0);
}
if (isset($_GET['legacy'])) { set_session('ss_secret_free_-1',true); set_session('ss_view_free_1',true); }
echo json_encode(array('member'=>$member['mb_id'], 'auth'=>array_filter($_SESSION,function($k){return strpos($k,'ss_secret_v2_')===0;},ARRAY_FILTER_USE_KEY)));
''')
    with socket.socket() as s: s.bind(('127.0.0.1',0));port=s.getsockname()[1]
    base=f'http://127.0.0.1:{port}'
    log=open(work/'http.log','w')
    web=subprocess.Popen(['php','-d','mysqli.default_socket='+sock,'-d','display_errors=0','-d','log_errors=1',
        '-S',f'127.0.0.1:{port}','-t',str(site)],stdout=log,stderr=log,cwd=site)
    for _ in range(100):
        try: request(client(),'/audit_fixture.php'); break
        except OSError: time.sleep(.05)
    owner=login('owner'); other=login('other'); admin=login('admin'); guest=client()
    request(guest,'/audit_fixture.php?prepare=1')
    check('ROOT_PRIVATE_68' not in view(other,1)[2],'타인 비밀 원글 초기 차단')
    for ident in [1,3,5]:
        denied_write('답글 직접 저장 차단 '+str(ident),lambda ident=ident:save(other,w='r',wr_id=ident,secret='secret'))
    denied_write('비밀 원글 회원 댓글 차단',lambda:comment(other,1))
    denied_write('비밀 원글 비회원 댓글 차단',lambda:comment(guest,1))
    denied_write('스크랩 댓글 우회 차단',lambda:request(other,'/bbs/scrap_popin_update.php',dict(bo_table='free',wr_id=1,wr_content='bypass')))
    for ident,pwd in [(5,'account-pass'),(6,'guest-pass'),(4,'account-pass')]:
        r=password(other,ident,pwd=pwd)
        check('alert(' in r[2] and not auth(other),'댓글/공개글 원글 인증 차단 '+str(ident),r)
    check('alert(' in password(other,1,'sc')[2],'원글을 댓글 모드로 인증 차단')
    check('alert(' in password(other,5,'sc')[2],'부모 접근 불가 시 댓글 인증 차단')
    sql("UPDATE g5_write_free SET wr_option='secret' WHERE wr_id=4")
    password(other,4)
    check(bool(auth(other)) and 'ROOT_PRIVATE_68' not in view(other,1)[2],'자기 답글 인증이 원글로 역전파되지 않음')
    r=request(other,'/bbs/link.php?bo_table=free&wr_id=1&no=1')
    check('PRIVATE-LINK-68' not in str(r),'비밀글 링크 직접 노출 차단')
    request(other,'/audit_fixture.php?legacy=1')
    check('ROOT_PRIVATE_68' not in view(other,1)[2],'구버전 비밀 인증 무효')
    check('PRIVATE-LINK-68' not in str(request(other,'/bbs/link.php?bo_table=free&wr_id=1&no=1')),'과거 열람 세션으로 링크 우회 불가')
    check('비밀글 인증' in request(other,'/bbs/download.php?bo_table=free&wr_id=1&no=0')[2],'과거 열람 세션으로 다운로드 우회 불가')
    check('GUEST_PRIVATE_68' not in view(guest,3)[2],'비회원 비밀글 초기 차단')
    password(guest,3,pwd='wrong')
    check(not auth(guest),'잘못된 비밀번호 인증 실패')
    password(guest,3,pwd='guest-pass')
    check('GUEST_PRIVATE_68' in view(guest,3)[2],'비회원 올바른 원글 인증')
    for c,ident in [(owner,1),(guest,3)]:
        body=view(c,ident)[2]
        href=html.unescape(re.search(r'href="([^"]*download.php[^"]*)"',body).group(1))
        r=request(c,href.removeprefix(base))
        check(r[2]=='PRIVATE_ATTACHMENT_68','정상 인증 후 첨부 다운로드 '+str(ident),r)
    check('INHERITED_REPLY_68' in view(guest,7)[2],'원글 인증으로 관리자 상속 답글 열람')
    check('OTHER_PASSWORD_REPLY_68' not in view(guest,8)[2],'다른 비밀번호 답글 인증 분리')
    denied_write('인증한 비회원의 비밀글 답변 제한 유지',lambda:save(guest,w='r',wr_id=3,secret='secret'))
    check('OWNER_ADMIN_REPLY_68' in view(owner,10)[2],'회원 원 작성자 관리자 답글 열람')
    for who in ['groupadmin','boardadmin']:
        c=login(who);check('ROOT_PRIVATE_68' in view(c,1)[2],who+' 비밀글 열람')
        before=int(sql('SELECT COUNT(*) FROM g5_write_free'))
        save(c,w='r',wr_id=1,secret='secret')
        check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,who+' 비밀 답변 저장')
    for c,ident,label in [(owner,1,'본인 비밀 답변'),(admin,3,'최고관리자 비회원 비밀 답변'),(other,2,'공개글 답변')]:
        before=int(sql('SELECT COUNT(*) FROM g5_write_free'))
        r=save(c,w='r',wr_id=ident,secret='secret' if ident!=2 else '')
        check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,label,r)
    for c,ident,label in [(owner,1,'정상 회원 댓글'),(guest,3,'인증 비회원 댓글'),(other,2,'공개글 댓글')]:
        request(c,'/audit_fixture.php?prepare=1')
        before=int(sql('SELECT COUNT(*) FROM g5_write_free'));r=comment(c,ident)
        check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,label,r)
    before=int(sql('SELECT COUNT(*) FROM g5_write_free'))
    request(other,'/bbs/scrap_popin_update.php',dict(bo_table='free',wr_id=2,wr_content='scrap normal'))
    check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,'정상 스크랩 댓글')
    password(guest,11,'sc','guest-pass')
    check('PUBLIC_PARENT_COMMENT_68' in view(guest,2)[2],'정상 비밀 댓글 인증')
    check('ROOT_PRIVATE_68' not in view(guest,1)[2],'정상 댓글 인증이 다른 원글에 영향 없음')
    # 정상 사용자의 신규 작성·수정·댓글 수정 경로를 실제 저장 결과로 검증한다.
    for c,label in [(other,'회원'),(client(),'비회원')]:
        for secret in ['', 'secret']:
            marker='NORMAL_'+label+('_SECRET' if secret else '_PUBLIC')
            r=save(c,secret=secret,wr_content=marker)
            ident=int(sql('SELECT MAX(wr_id) FROM g5_write_free'))
            check(sql(f'SELECT wr_content FROM g5_write_free WHERE wr_id={ident}')==marker and marker in view(c,ident)[2],label+' 신규 '+(secret or 'public')+' 작성·열람',r)
            r=request(c,'/bbs/write.php',dict(bo_table='free',w='u',wr_id=ident,wr_password='guest-pass'))
            check('name="wr_content"' in r[2],label+' '+(secret or 'public')+' 수정 화면 진입',r)
            r=save(c,w='u',wr_id=ident,secret=secret,wr_content=marker+'_EDITED',wr_password='')
            check(sql(f'SELECT wr_content FROM g5_write_free WHERE wr_id={ident}')==marker+'_EDITED' and marker+'_EDITED' in view(c,ident)[2],label+' '+(secret or 'public')+' 수정·재열람',r)
            request(c,'/audit_fixture.php?prepare=1')
            r=comment(c,ident,wr_content=marker+'_COMMENT')
            cid=int(sql('SELECT MAX(wr_id) FROM g5_write_free'))
            check(sql(f'SELECT wr_content FROM g5_write_free WHERE wr_id={cid}')==marker+'_COMMENT',label+' '+(secret or 'public')+' 본문 댓글 작성',r)
            request(c,'/audit_fixture.php?prepare=1')
            r=comment(c,ident,w='cu',comment_id=cid,wr_content=marker+'_COMMENT_EDITED')
            check(sql(f'SELECT wr_content FROM g5_write_free WHERE wr_id={cid}')==marker+'_COMMENT_EDITED',label+' '+(secret or 'public')+' 자기 댓글 수정',r)
    for mode in ['group','cert','adult','read']:
        sql(f"UPDATE g5_group SET gr_use_access={1 if mode=='group' else 0} WHERE gr_id='{gr}';UPDATE g5_board SET bo_use_cert='{mode if mode in ['cert','adult'] else ''}',bo_read_level={3 if mode=='read' else 1} WHERE bo_table='free';")
        if mode!='read':
            denied_write(mode+' 신규 글 직접 저장 차단',lambda:save(other))
            denied_write(mode+' 답글 직접 저장 차단',lambda:save(other,w='r',wr_id=2))
        denied_write(mode+' 댓글 직접 저장 차단',lambda:comment(other,2))
        denied_write(mode+' 스크랩 댓글 차단',lambda:request(other,'/bbs/scrap_popin_update.php',dict(bo_table='free',wr_id=9,wr_content='bypass')))
        check('PRIVATE-LINK-68' not in str(request(other,'/bbs/link.php?bo_table=free&wr_id=2&no=1')),mode+' 링크 접근 차단')
    sql(f"UPDATE g5_group SET gr_use_access=1 WHERE gr_id='{gr}';INSERT INTO g5_group_member SET gr_id='{gr}',mb_id='other',gm_datetime=NOW();UPDATE g5_member SET mb_certify='hp',mb_adult=1 WHERE mb_id='other';UPDATE g5_board SET bo_use_cert='adult',bo_read_level=1 WHERE bo_table='free';")
    before=int(sql('SELECT COUNT(*) FROM g5_write_free'));save(other)
    check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,'그룹 가입·성인인증 정상 저장')
    check('PUBLIC_68' in view(other,2)[2],'그룹 가입·성인인증 정상 열람')
    before=int(sql('SELECT COUNT(*) FROM g5_write_free'));r=save(other,w='r',wr_id=2)
    check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,'그룹 가입·성인인증 정상 답글',r)
    request(other,'/audit_fixture.php?prepare=1')
    before=int(sql('SELECT COUNT(*) FROM g5_write_free'));r=comment(other,2)
    check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1,'그룹 가입·성인인증 정상 댓글',r)
    before=int(sql('SELECT COUNT(*) FROM g5_scrap'))
    r=request(other,'/bbs/scrap_popin_update.php',dict(bo_table='free',wr_id=9,wr_content='allowed group scrap'))
    check(int(sql('SELECT COUNT(*) FROM g5_scrap'))==before+1,'그룹 가입·성인인증 정상 스크랩',r)
    check('PRIVATE-LINK-68' in str(request(other,'/bbs/link.php?bo_table=free&wr_id=2&no=1')),'그룹 가입·성인인증 정상 링크')
    sql(f"UPDATE g5_group SET gr_use_access=0 WHERE gr_id='{gr}';UPDATE g5_board SET bo_use_cert='' WHERE bo_table='free';")
    check('PUBLIC_68' in view(other,2,True)[2],'모바일 공개글 정상 열람')
    check('ROOT_PRIVATE_68' not in view(other,1,True)[2],'모바일 타인 비밀글 차단')
    check('ROOT_PRIVATE_68' in view(owner,1,True)[2],'모바일 작성자 비밀글 정상 열람')
    before=int(sql('SELECT COUNT(*) FROM g5_write_free'));save(guest,secret='secret')
    last=int(sql('SELECT MAX(wr_id) FROM g5_write_free'))
    check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1 and 'test body' in view(guest,last)[2],'비회원 신규 비밀글 저장 후 자기 글 열람')
    # 직접 링크 정상 사용 및 공개→비밀 전환 시 과거 열람만으로 접근하지 못하는지 검사.
    check('PRIVATE-LINK-68' in str(request(other,'/bbs/link.php?bo_table=free&wr_id=2&no=1')),'공개 링크 정상 이동')
    sql("UPDATE g5_write_free SET wr_option='secret' WHERE wr_id=2")
    check('PRIVATE-LINK-68' not in str(request(other,'/bbs/link.php?bo_table=free&wr_id=2&no=1')),'공개→비밀 전환 후 과거 열람 무효')
    check('PRIVATE-LINK-68' in str(request(owner,'/bbs/link.php?bo_table=free&wr_id=2&no=1')),'비밀 링크 원 작성자 정상 이동')
    # 답글 수정 및 비밀 전환으로 스레드 인증을 얻을 수 없어야 한다.
    request(other,'/bbs/write.php?bo_table=free&w=u&wr_id=4')
    r=save(other,w='u',wr_id=4,secret='secret',wr_content='EDITED_OWN_REPLY_68',wr_password='')
    check(sql('SELECT wr_content FROM g5_write_free WHERE wr_id=4')=='EDITED_OWN_REPLY_68','본인 답글 수정 정상',r)
    check('ROOT_PRIVATE_68' not in view(other,1)[2],'답글 수정의 자동 인증은 원글로 전파되지 않음')
    denied_write('타인 비밀글 수정 차단',lambda:save(other,w='u',wr_id=1,secret='secret'))
    denied_write('댓글을 일반 글 수정으로 전달 차단',lambda:save(other,w='u',wr_id=5,secret='secret'))
    denied_write('다른 부모 댓글 수정 차단',lambda:comment(other,9,w='cu',comment_id=5))
    denied_write('일반 글을 댓글 수정으로 전달 차단',lambda:comment(other,9,w='cu',comment_id=9))
    sql("UPDATE g5_board SET bo_use_secret=2 WHERE bo_table='free'")
    denied_write('강제 비밀글 게시판의 타인 답글 차단',lambda:save(other,w='r',wr_id=1))
    before=int(sql('SELECT COUNT(*) FROM g5_write_free'));save(other)
    check(int(sql('SELECT COUNT(*) FROM g5_write_free'))==before+1 and sql('SELECT wr_option FROM g5_write_free ORDER BY wr_id DESC LIMIT 1')=='secret','강제 비밀글 신규 저장 정상')
    sql("UPDATE g5_board SET bo_use_secret=1,bo_reply_level=3 WHERE bo_table='free'")
    denied_write('답변 레벨 부족 차단',lambda:save(other,w='r',wr_id=9))
    sql("UPDATE g5_board SET bo_reply_level=1 WHERE bo_table='free'")
    # 기존 MySQL PASSWORD 해시도 객체별 인증으로 마이그레이션 없이 동작한다.
    sql("UPDATE g5_write_free SET wr_password=PASSWORD('legacy-pass') WHERE wr_id=3;UPDATE g5_write_free SET wr_password=PASSWORD('legacy-pass') WHERE wr_id=11;")
    check('GUEST_PRIVATE_68' not in view(guest,3)[2],'비밀번호 변경 후 기존 비밀 인증 무효')
    legacy=client();password(legacy,3,pwd='legacy-pass')
    check('GUEST_PRIVATE_68' in view(legacy,3)[2],'구버전 비밀번호 원글 인증 호환')
    sql("UPDATE g5_write_free SET wr_option='' WHERE wr_id=2")
    password(legacy,11,'sc','legacy-pass')
    check('PUBLIC_PARENT_COMMENT_68' in view(legacy,2)[2],'구버전 비밀번호 댓글 인증 호환')
    old_member_auth=client();password(old_member_auth,1,pwd='account-pass')
    check('ROOT_PRIVATE_68' in view(old_member_auth,1)[2],'회원 비밀번호 대체 인증 정상')
    sql("UPDATE g5_member SET mb_password=PASSWORD('legacy-account') WHERE mb_id='owner'")
    check('ROOT_PRIVATE_68' not in view(old_member_auth,1)[2],'회원 비밀번호 변경 후 기존 대체 인증 무효')
    legacy_member=client();password(legacy_member,1,pwd='legacy-account')
    check('ROOT_PRIVATE_68' in view(legacy_member,1)[2],'구버전 회원 비밀번호 대체 인증 호환')
    check('OWNER_ADMIN_REPLY_68' in view(legacy_member,10)[2],'회원 원글 비밀번호 인증 후 관리자 답글 열람')
    print('HTTP TOTAL',len(checks),'ARTIFACTS',work,flush=True)
finally:
    if web:
        web.terminate();web.wait(timeout=10);log.close()
    if dbproc:
        dbproc.terminate();dbproc.wait(timeout=20)
    (work/'results.json').write_text(json.dumps(checks,ensure_ascii=False,indent=2))
    print('ARTIFACTS',work,flush=True)
    if (work/'http.log').exists():
        errors=[line for line in (work/'http.log').read_text().splitlines() if re.search(r'PHP (Warning|Fatal|Deprecated|Notice|Parse)',line)]
        if errors: raise AssertionError('\n'.join(errors[:20]))
