#!/usr/bin/env python3
"""실제 설치 코드의 이력 등록과 완료 차단을 격리 MySQL에서 검증한다.
실행: python3 tests/migration_install_flow.py /path/to/isolated/mysql.sock
root/빈 비밀번호인 격리 DB만 사용하며 임시 DB와 설치 디렉터리를 삭제한다.
"""
from pathlib import Path
import tempfile, subprocess, shutil, json, base64, sys, os
root = Path(__file__).resolve().parents[1]
socket = sys.argv[1]
def mysql(sql):
    return subprocess.check_output(['mysql','--no-defaults','--socket='+socket,'-uroot','-N'],input=sql,text=True).strip()
for case in ['plain','shop','retained','missing']:
    db='g5_install_check_'+str(os.getpid())+'_'+case
    mysql('CREATE DATABASE '+db)
    try:
        with tempfile.TemporaryDirectory(prefix='g5-real-install-') as directory:
            path=Path(directory)
            for item in root.iterdir():
                if item.name in ['data','.git','install','migrations']: continue
                (path/item.name).symlink_to(item,target_is_directory=item.is_dir())
            shutil.copytree(root/'install',path/'install')
            (path/'data').mkdir()
            if case=='missing': (path/'migrations').mkdir()
            else: (path/'migrations').symlink_to(root/'migrations',target_is_directory=True)
            if case=='retained': mysql('CREATE TABLE '+db+'.mall_cart (id int)')
            post=dict(mysql_host='localhost',mysql_user='root',mysql_pass='',mysql_db=db,table_prefix='custom_',admin_id='admin',admin_pass='test-only-pass',admin_name='관리자',admin_email='admin@example.com',g5_install=1,g5_shop_prefix='mall_',g5_shop_install=int(case=='shop'))
            encoded=base64.b64encode(json.dumps(post).encode()).decode()
            (path/'run.php').write_text("<?php $_POST=json_decode(base64_decode('"+encoded+"'),true); $_SERVER['SERVER_NAME']='localhost'; $_SERVER['SCRIPT_NAME']='/install/install_db.php'; $_SERVER['REQUEST_METHOD']='POST'; chdir(__DIR__.'/install'); require './install_db.php';")
            run=subprocess.run(['php','-d','mysqli.default_socket='+socket,str(path/'run.php')],text=True,capture_output=True)
            assert run.returncode==0,(case,run.stderr,run.stdout[-1500:])
            exists=(path/'data/dbconfig.php').exists()
            count=int(mysql('SELECT COUNT(*) FROM '+db+'.custom_migrations'))
            if case=='missing': assert not exists and '마이그레이션 파일을 찾을 수 없습니다' in run.stdout,(case,run.stdout[-1800:])
            else:
                assert exists and '설치가 완료되었습니다' in run.stdout,(case,run.stderr,run.stdout[-1800:])
                assert count==(0 if case=='retained' else len(list((root/'migrations').glob('*.sql')))),(case,count)
            print(case, 'PASS', 'history='+str(count), 'config='+str(exists))
    finally: mysql('DROP DATABASE '+db)
