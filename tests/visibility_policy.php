<?php
// 실행: php tests/visibility_policy.php (DB 없이 게시판 권한 문맥·공개 정책 검증)
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
require dirname(__DIR__).'/lib/board_access.lib.php';
$member=array('mb_id'=>'','mb_level'=>1,'mb_certify'=>'','mb_adult'=>0,'mb_dupinfo'=>'');
$config=array('cf_admin'=>'super','cf_cert_use'=>1);
$is_admin='super'; // 현재 URL의 관리자 값이 대상 게시판으로 전파되면 안 된다.
$g5=array('group_member_table'=>'fixture_group_member');
$group=array('gr_use_access'=>0,'gr_admin'=>'group_owner');
$joined=false;
function get_group($id) { return $GLOBALS['group']; }
function sql_escape_string($s) { return addslashes($s); }
function sql_fetch($sql) { return $GLOBALS['joined'] ? array('gr_id'=>'g') : array(); }
$checks=0;
function check($ok,$name) {
 if (!$ok) throw new Exception($name);
 $GLOBALS['checks']++; echo 'PASS '.$name."\n";
}
$board=array('bo_table'=>'b','gr_id'=>'g','bo_admin'=>'board_owner','bo_list_level'=>1,'bo_read_level'=>1,'bo_use_cert'=>'');
check(g5_board_visible($board),'public list');
check(g5_board_visible($board,true),'public read');
check(g5_board_public($board),'public feed');
$group['gr_use_access']=1;
check(!g5_board_visible($board),'guest cannot inherit global super');
check(!g5_board_public($board),'restricted feed denied');
$member['mb_id']='other';$member['mb_level']=2;
check(!g5_board_visible($board),'nonmember group');
$joined=true;
check(g5_board_visible($board,true),'group member read');
$joined=false;
$member['mb_id']='group_owner';
check(g5_board_visible($board,true),'target group admin');
$member['mb_id']='unrelated_admin';$is_admin='group';
check(!g5_board_visible($board,true),'unrelated group admin denied');
$member['mb_id']='super';
check(g5_board_visible($board,true),'actual super');
check(!g5_board_public($board),'admin cannot publish private RSS');
$member['mb_id']='board_owner';
check(g5_board_visible($board),'board owner list');
check(!g5_board_visible($board,true),'board owner must join for read');
$group['gr_use_access']=0;$member['mb_id']='other';
$board['bo_list_level']=3;
check(!g5_board_visible($board),'list level enforced');
check(!g5_board_public($board),'list level feed denied');
$board['bo_list_level']=1;$board['bo_read_level']=3;
check(g5_board_visible($board),'list independent of read');
check(!g5_board_visible($board,true),'read level enforced');
check(!g5_board_public($board),'read level cache denied');
$board['bo_read_level']=1;$board['bo_use_cert']='cert';
check(!g5_board_visible($board),'cert required');
check(!g5_board_public($board),'cert never shared');
$member['mb_certify']='hp';
check(g5_board_visible($board),'cert allowed');
$member['mb_dupinfo']=str_repeat('d',64);
check(!g5_board_visible($board),'refresh required');
$member['mb_dupinfo']='';$board['bo_use_cert']='adult';
check(!g5_board_visible($board),'minor denied');
$member['mb_adult']=1;
check(g5_board_visible($board),'adult allowed');
$member['mb_id']='';$member['mb_certify']='';$member['mb_adult']=0;
check(!g5_board_visible($board),'guest adult denied');
$config['cf_cert_use']=0;
check(g5_board_visible($board),'board certification disabled policy preserved');
check(g5_board_public($board),'disabled board cert can be public');
check(!g5_board_visible(array()),'missing board denied');
echo 'TOTAL '.$checks."\n";
