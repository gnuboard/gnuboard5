<?php
// 실행: php tests/board_access.php (운영 DB 없이 권한 및 인증 범위 회귀 검사)
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
require dirname(__DIR__).'/lib/board_access.lib.php';
set_error_handler(function ($level, $message) { throw new Exception($message); });
$session = array();
function set_session($key, $value) { $GLOBALS['session'][$key] = $value; }
function get_session($key) { return $GLOBALS['session'][$key] ?? ''; }
function get_group($id) { return $GLOBALS['group']; }
function get_member($id) { return array('mb_password'=>$GLOBALS['author_hash']); }
function sql_escape_string($str) { return addslashes($str); }
function sql_fetch($sql) {
    if (strpos($sql, 'group_member') !== false) return $GLOBALS['joined'] ? array('gr_id'=>'test') : array();
    return $GLOBALS['root'];
}
function alert($msg) { throw new RuntimeException($msg); }
function check($ok, $name) {
    if (!$ok) throw new Exception('FAIL '.$name);
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function rejected($callback) {
    try { $callback(); return false; } catch (RuntimeException $e) { return true; }
}
$checks=0;
$author_hash='member-hash';
$g5=array('write_prefix'=>'g5_write_', 'group_member_table'=>'g5_group_member');
$config=array('cf_cert_use'=>1);
$group=array('gr_use_access'=>0);
$joined=false;
$is_admin='';
$member=array('mb_id'=>'other', 'mb_level'=>2, 'mb_certify'=>'', 'mb_adult'=>0, 'mb_dupinfo'=>'');
$board=array('bo_table'=>'test','gr_id'=>'test','bo_read_level'=>1,'bo_reply_level'=>2,'bo_use_cert'=>'');
$root=array('wr_id'=>1,'wr_parent'=>1,'wr_num'=>-1,'wr_reply'=>'','wr_is_comment'=>0,
    'mb_id'=>'owner','wr_option'=>'secret','wr_password'=>'hash-root','wr_datetime'=>'2026-11-02 00:00:00');
$reply=array_replace($root,array('wr_id'=>2,'wr_parent'=>2,'wr_reply'=>'A','mb_id'=>'other','wr_password'=>'hash-other'));
$comment=array_replace($root,array('wr_id'=>3,'wr_is_comment'=>1,'mb_id'=>'other','wr_password'=>'hash-comment'));
check(!g5_can_read_secret($board,$root),'타인 비밀 원글 차단');
set_session('ss_secret_test_-1',true);
check(!g5_can_read_secret($board,$root),'구버전 스레드 세션 무효');
g5_grant_secret_auth('test',$comment);
check(!g5_can_read_secret($board,$root),'댓글 인증은 원글로 전파되지 않음');
check(!g5_can_read_secret($board,$comment),'댓글을 원글로 취급하지 않음');
g5_grant_secret_auth('test',$reply);
check(!g5_can_read_secret($board,$root),'답글 인증은 원글로 전파되지 않음');
check(g5_has_secret_auth('test',$reply),'인증한 답글은 유효');
check(!g5_has_secret_auth('elsewhere',$reply),'게시판 간 인증 분리');
check(!g5_has_secret_auth('test',array_replace($reply,array('wr_password'=>'new'))),'비밀번호 변경 시 인증 무효');
check(!g5_has_secret_auth('test',array_replace($reply,array('wr_is_comment'=>1))),'유형 변경 시 인증 무효');
$member_post=array_replace($root,array('wr_password'=>''));
g5_grant_secret_auth('test',$member_post);
$author_hash='changed-member-hash';
check(!g5_has_secret_auth('test',$member_post),'회원 비밀번호 변경 시 대체 인증 무효');
$member['mb_id']='third';
check(!g5_has_secret_auth('test',$reply),'로그인 계정 변경 시 인증 무효');
$member['mb_id']='owner';
check(g5_can_read_secret($board,$root),'회원 원 작성자 열람');
check(g5_can_read_secret($board,$reply),'회원 원 작성자 답글 열람');
check(!rejected(function()use($board,$root){g5_require_board_reply($board,$root);}), '본인 비밀글 답변');
$member['mb_id']='other';
check(rejected(function()use($board,$root){g5_require_board_reply($board,$root);}), '타인 비밀글 답변 차단');
$root['mb_id']='';
$member['mb_id']='';
$reply['mb_id']='admin';
g5_grant_secret_auth('test',$root);
check(g5_can_read_secret($board,$root),'비회원 원글 비밀번호 인증');
check(!g5_can_read_secret($board,$reply),'원글 인증으로 다른 비밀번호 답글 열람 불가');
$reply['wr_password']=$root['wr_password'];
check(g5_can_read_secret($board,$reply),'원글 인증으로 비밀번호 상속 답글 열람');
check(rejected(function()use($board,$root){g5_require_board_reply($board,$root);}), '인증한 비회원도 비밀글 답변 불가');
foreach (array('super','group','board') as $is_admin) {
    check(g5_can_read_secret($board,$root),'관리자 열람 '.$is_admin);
    check(!rejected(function()use($board,$root){g5_require_board_reply($board,$root);}), '관리자 답변 '.$is_admin);
}
$is_admin='';
$session=array();
$root['wr_option']='';
g5_grant_secret_auth('test',$root);
$root['wr_option']='secret';
check(!g5_can_read_secret($board,$root),'공개 시 열람/저장은 비밀 전환 후 인증이 아님');
$member['mb_id']='other';
$group['gr_use_access']=1;
check(g5_board_access_error($board)!=='','그룹 비가입 차단');
$joined=true;
check(g5_board_access_error($board)==='','그룹 가입 허용');
$member['mb_id']='';
check(g5_board_access_error($board)!=='','그룹 비회원 차단');
$member['mb_id']='other'; $group['gr_use_access']=0;
$board['bo_read_level']=3;
check(g5_board_access_error($board,true)!=='','읽기 레벨 차단');
check(g5_board_access_error($board,false)==='','쓰기만 검사할 때 읽기 레벨 독립');
$board['bo_read_level']=1; $board['bo_use_cert']='cert';
check(g5_board_access_error($board)!=='','본인 미인증 차단');
$member['mb_certify']='hp';
check(g5_board_access_error($board)==='','본인 인증 허용');
$member['mb_dupinfo']=str_repeat('a',64);
check(g5_board_access_error($board)!=='','구 인증정보 갱신 필요');
$member['mb_dupinfo']=''; $board['bo_use_cert']='adult';
check(g5_board_access_error($board)!=='','성인 미인증 차단');
$member['mb_adult']=1;
check(g5_board_access_error($board)==='','성인 인증 허용');
check(rejected(function()use($board,$comment){g5_require_board_reply($board,$comment);}), '댓글 대상 답글 차단');
check(rejected(function()use($board){g5_require_board_read($board,array());}), '미존재 대상 차단');
echo 'TOTAL '.$checks."\n";
