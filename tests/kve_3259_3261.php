<?php
// 실행: php tests/kve_3259_3261.php
// DB 없이 KVE-2026-3259(게시판그룹 소유자), 3260(게시판 소속 그룹 소유자), 3261(삭제 권한 d·GET 삭제 CSRF)의
// 검사 함수 동작과 처리 파일의 검사 위치·조건을 검사한다.
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
$checks = 0;
function check($ok, $name) {
    if (!$ok) throw new Exception('FAIL '.$name);
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return str_replace("\r\n", "\n", file_get_contents($GLOBALS['root'].'/'.$path)); }
function pos_of($s, $needle, $from = 0) { $p = strpos($s, $needle, $from); return $p === false ? -1 : $p; }
function func_src($path, $name, $indent) {
    if (!preg_match('/^'.$indent.'function '.$name.'\(.*?^'.$indent.'\}/ms', src($path), $m)) throw new Exception('함수 찾기 실패: '.$name);
    return $m[0];
}
// $a가 $b보다 앞에 있고 둘 다 존재
function before($s, $a, $b, $from = 0) { $x = pos_of($s, $a, $from); $y = pos_of($s, $b, $from); return $x >= 0 && $y >= 0 && $x < $y; }

// ---------- 검사 함수 ----------
foreach (array('admin_group_owner_check', 'admin_board_owner_check', 'admin_owned_board_group_sql') as $fn) eval(func_src('adm/admin.lib.php', $fn, ''));
eval(func_src('lib/common.lib.php', 'get_group_select', ''));
function sql_real_escape_string($s) { return addslashes($s); }
$GROUPS = array('own' => 'manager', 'oth' => 'owner', 'nob' => '', 'cas' => 'Manager');
$BOARDS = array('b_own' => 'own', 'b_oth' => 'oth', 'b_orphan' => 'gone', 'b_nob' => 'nob');
$QUERIES = array();
function sql_fetch($sql) {
    $GLOBALS['QUERIES'][] = $sql;
    if (preg_match("/from g5_group where gr_id = '([^']*)'/", $sql, $m)) return isset($GLOBALS['GROUPS'][$m[1]]) ? array('gr_admin' => $GLOBALS['GROUPS'][$m[1]]) : null;
    if (preg_match("/from g5_board where bo_table = '([^']*)'/", $sql, $m)) return isset($GLOBALS['BOARDS'][$m[1]]) ? array('gr_id' => $GLOBALS['BOARDS'][$m[1]]) : null;
    return null;
}
function sql_query($sql) { $GLOBALS['QUERIES'][] = $sql; return 'R'; }
function sql_fetch_array($r) { return false; }
function option_selected($v, $s, $t = '') { return ''; }
$g5 = array('group_table' => 'g5_group', 'board_table' => 'g5_board', 'member_table' => 'g5_member');
$is_admin = ''; $member = array('mb_id' => 'manager');

check(admin_group_owner_check('own') === true, '3259 본인 그룹 허용');
check(admin_group_owner_check('oth') === false, '3259 다른 관리자 그룹 거부');
check(admin_group_owner_check('nob') === false && admin_group_owner_check('zzz') === false, '3259 그룹 관리자 없는 그룹·없는 그룹 거부');
check(admin_group_owner_check('cas') === false, '3259 대소문자만 다른 회원아이디 거부');
check(admin_group_owner_check("oth' or '1'='1") === false && strpos(end($QUERIES), "where gr_id = 'othor11'") !== false, '3259 gr_id 영숫자·_ 외 문자 제거');
check(admin_group_owner_check('') === false && admin_group_owner_check(array('own')) === false && admin_group_owner_check(null) === false && admin_group_owner_check(1) === false, '3259 빈 값·배열·문자열 아닌 값 거부');
$member = array('mb_id' => ''); check(admin_group_owner_check('nob') === false, '3259 비회원 거부(빈 그룹 관리자와 일치시키지 않음)');
$member = array('mb_id' => 'manager');
check(admin_board_owner_check('b_own') === true, '3260 본인 그룹 게시판 허용');
check(admin_board_owner_check('b_oth') === false && admin_board_owner_check('b_nob') === false, '3260 타 그룹·관리자 없는 그룹 게시판 거부');
check(admin_board_owner_check('b_orphan') === false && admin_board_owner_check('nope') === false, '3260 그룹 없는 게시판·없는 게시판 거부');
check(admin_board_owner_check(array('b_own')) === false && admin_board_owner_check('') === false, '3260 배열·빈 값 거부');
$sql = admin_owned_board_group_sql('gr_id');
check(strpos($sql, " and gr_id in ( select gr_id from g5_group where gr_admin = 'manager' )") !== false, '3260 위임 관리자 UPDATE 소유 그룹 조건');
$member = array('mb_id' => ''); check(admin_owned_board_group_sql() === ' and 0 ', '3260 회원 없으면 UPDATE 조건이 거짓');
$member = array('mb_id' => "ma'n");
check(strpos(admin_owned_board_group_sql(), "gr_admin = 'ma\\'n'") !== false, '3260 UPDATE 조건 회원아이디 이스케이프');
$member = array('mb_id' => 'manager');
foreach (array('', 'group', 'board') as $role) {
    $is_admin = $role; $QUERIES = array(); get_group_select('gr_id');
    if (strpos($QUERIES[0], "where a.gr_admin = 'manager'") === false) throw new Exception('FAIL 그룹 선택 '.$role);
}
check(true, '3260 그룹 선택 목록: 최고관리자가 아니면 역할과 관계없이 본인 그룹만');
$is_admin = 'super'; $QUERIES = array(); get_group_select('gr_id');
check(strpos($QUERIES[0], 'where') === false, '3260 그룹 선택 목록: 최고관리자는 전체');
$n = count($QUERIES);
check(admin_group_owner_check('oth') === true && admin_board_owner_check('b_oth') === true && admin_owned_board_group_sql() === '' && count($QUERIES) === $n, '3259·3260 최고관리자는 DB 조회 없이 허용');

// ---------- KVE-2026-3259 처리 파일 ----------
$f = src('adm/boardgroup_form.php');
check(before($f, "} elseif (\$w == 'u') {\n    if (!admin_group_owner_check(\$gr_id)) {", "\$gr = sql_fetch(\" select * from {\$g5['group_table']} where gr_id = '\$gr_id' \");"), '3259 상세 폼: 조회 전에 소유자 확인');
check(strpos($f, "if (\$is_admin != 'super' && \$w == '') {\n    alert('최고관리자만 접근 가능합니다.');") !== false, '3259 그룹 생성 폼은 최고관리자 전용(기존)');
$u = src('adm/boardgroup_form_update.php');
check(strpos($u, "if (\$is_admin != 'super' && \$w == '') {\n    alert('최고관리자만 접근 가능합니다.');") !== false, '3259 그룹 생성 처리는 최고관리자 전용(기존)');
check(before($u, "if (\$w == 'u' && !admin_group_owner_check(\$gr_id)) {", '$sql_common = '), '3259 단일 수정: 처리 전 소유자 확인');
check(before($u, "\$posts[\$key] = isset(\$_POST[\$key])", "if (\$is_admin != 'super') {\n    \$posts['gr_admin'] = sql_real_escape_string(\$member['mb_id']);")
    && before($u, "\$posts['gr_admin'] = sql_real_escape_string(\$member['mb_id']);", '$sql_common = '), '3259 단일 수정: 위임 관리자 gr_admin 요청 값 무시');
check(strpos($u, "where gr_id = '{\$gr_id}' \";\n    if (\$is_admin != 'super') {\n        \$sql .= \" and gr_admin = '\" . sql_real_escape_string(\$member['mb_id']) . \"' \";\n    }\n    sql_query(\$sql);") !== false, '3259 단일 UPDATE 소유자 조건');
$l = src('adm/boardgroup_list_update.php');
$loop = pos_of($l, "for (\$i = 0; \$i < \$chk_count; \$i++) {\n    \$k              =");
check($loop > 0 && before($l, "if (!admin_group_owner_check(\$chk_gr_id)) {", "for (\$i = 0; \$i < \$chk_count; \$i++) {\n    \$k              ="), '3259 일괄 수정: 갱신 전에 제출 그룹 전체 소유자 확인');
check(strpos($l, "\$chk_gr_id = isset(\$post_group_id[\$k]) ? \$post_group_id[\$k] : '';") !== false, '3259 일괄 수정: 갱신과 같은 행 색인($k)으로 확인');
check(before($l, "if (\$is_admin != 'super') {\n            \$gr_admin = \$member['mb_id'];", '$sql = " update {$g5[\'group_table\']}')
    && strpos($l, "\$sql .= \" and gr_admin    = '\" . sql_real_escape_string(\$member['mb_id']) . \"' \";") !== false
    && strpos($l, "and gr_admin    = '\" . sql_real_escape_string(\$gr_admin)") === false, '3259 일괄 수정: gr_admin 요청 값 무시·현재 회원 기준 조건');
$del = pos_of($l, "} elseif (\$act_button === '선택삭제') {");
check($del > 0 && $del < $loop && before(substr($l, $del), "alert('게시판그룹 삭제는 최고관리자만 가능합니다.');", "auth_check_menu(\$auth, \$sub_menu, 'd');"), '3259·3261 그룹 선택삭제: 최고관리자 전용, d 권한 확인');
check(before($l, "} else {\n    alert('올바른 방법으로 이용해 주십시오.');", "for (\$i = 0; \$i < \$chk_count; \$i++) {\n    \$k              ="), '3259 그룹 일괄 처리: 알 수 없는 동작 거부');
check(strpos(src('adm/boardgroup_list.php'), "<?php if (\$is_admin == 'super') { ?>\n            <input type=\"submit\" name=\"act_button\" onclick=\"document.pressed=this.value\" value=\"선택삭제\"") !== false, '3259 그룹 목록: 선택삭제 버튼은 최고관리자만');
$m = src('adm/boardgroupmember_list.php');
check(before($m, "if (!admin_group_owner_check(\$gr['gr_id'])) {", '$sql_common = '), '3259 접근회원 목록: 그룹 소유자 확인');
$m = src('adm/boardgroupmember_update.php');
check(before($m, "if (!admin_group_owner_check(\$gr['gr_id'])) {", 'insert into {$g5[\'group_member_table\']}')
    && strpos($m, "set gr_id = '\" . sql_real_escape_string(\$gr['gr_id']) . \"'") !== false && strpos($m, "\$_POST['gr_id']") === false, '3259 접근회원 추가: 그룹 소유자 확인, 검증한 값으로 저장');
check(before($m, "if (!admin_group_owner_check((string) \$gm['gr_id'])) {", "foreach (\$delete_gm_ids as \$gm_id) {")
    && pos_of($m, 'delete from {$g5[\'group_member_table\']}') > pos_of($m, 'foreach ($delete_gm_ids as $gm_id) {'), '3259 접근회원 삭제: gm_id 행의 그룹을 모두 확인한 뒤 삭제');
$m = src('adm/boardgroupmember_form.php');
check(substr_count($m, "if (\$is_admin != 'super') {\n") === 2 && substr_count($m, "gr_admin = '{\$member['mb_id']}'") === 2, '3259 회원별 접근그룹 화면은 본인 그룹만 표시(기존)');

// ---------- KVE-2026-3260 처리 파일 ----------
$f = src('adm/board_form.php');
check(strpos($f, "if (\$member['mb_id'] != \$group['gr_admin'])") === false
    && before($f, "if (!admin_board_owner_check(\$board['bo_table'])) {\n        alert('게시판을 관리할 권한이 없습니다.');", "\$readonly = 'readonly';"), '3260 상세 폼: 역할 문자열 대신 DB 기준 소속 그룹 소유자 확인');
$u = src('adm/board_form_update.php');
$chk = pos_of($u, "if (\$w == 'u' && !admin_board_owner_check(\$bo_table)) {");
check($chk > 0 && $chk < pos_of($u, '@mkdir($board_path') && $chk < pos_of($u, " insert into {\$g5['board_table']}"), '3260 단일 수정: 대상 게시판 현재 그룹 확인(디렉터리 생성 전)');
check(before($u, "if (!admin_group_owner_check(\$gr_id)) {\n    alert('게시판그룹을 관리할 권한이 없습니다.');", '@mkdir($board_path'), '3260 등록·수정: 저장할 gr_id 소유자 확인');
check(strpos($u, "where bo_table = '{\$bo_table}' \";\n    \$sql .= admin_owned_board_group_sql('gr_id');\n    sql_query(\$sql);") !== false, '3260 단일 UPDATE 소유 그룹 조건');
check(strpos($u, "where gr_id = '\$gr_id' \" . admin_owned_board_group_sql('gr_id'));") !== false, '3260 그룹적용: 확인된 본인 그룹만');
check(strpos($u, "set bo_table = bo_table {\$all_fields} where (1) \" . admin_owned_board_group_sql('gr_id'));") !== false
    && strpos($u, "set bo_table = bo_table {\$all_fields} \");") === false, '3260 전체적용: 위임 관리자는 본인 그룹 게시판으로 한정');
$l = src('adm/board_list_update.php');
$pre = pos_of($l, "if (!admin_board_owner_check(\$chk_bo_table) || !admin_group_owner_check(\$chk_gr_id)) {");
check($pre > 0 && $pre < pos_of($l, " update {\$g5['board_table']}") && strpos($l, "b.gr_admin = '{\$member['mb_id']}'") === false, '3260 일괄 수정: 갱신 전에 모든 행의 대상 게시판·옮길 그룹 확인');
check(strpos($l, "\$post_board_table = (isset(\$_POST['board_table'][\$k]) && is_string(\$_POST['board_table'][\$k])) ? preg_replace('/[^a-z0-9_]/i', '', \$_POST['board_table'][\$k]) : '';") !== false
    && strpos($l, "\$post_gr_id = (isset(\$_POST['gr_id'][\$k]) && is_string(\$_POST['gr_id'][\$k])) ? preg_replace('/[^a-z0-9_]/i', '', \$_POST['gr_id'][\$k]) : '';") !== false, '3260 일괄 수정: 갱신 값 정규화');
check(strpos($l, "where bo_table            = '\" . sql_real_escape_string(\$post_board_table) . \"' \";\n        \$sql .= admin_owned_board_group_sql('gr_id');") !== false, '3260 일괄 UPDATE 소유 그룹 조건');
check(before($l, "if (\$is_admin != 'super') {\n        alert('게시판 삭제는 최고관리자만 가능합니다.');", "auth_check_menu(\$auth, \$sub_menu, 'd');"), '3260 게시판 삭제는 최고관리자 전용·d(기존)');
check(before(src('adm/board_copy.php'), "if (!admin_board_owner_check(\$bo_table)) {", '<form name="fboardcopy"'), '3260 복사 폼: 원본 소유자 확인');
$c = src('adm/board_copy_update.php');
check(before($c, "if (!admin_board_owner_check(\$bo_table) || !isset(\$board['bo_table']) || \$board['bo_table'] !== \$bo_table) {", '$sql = get_table_define('), '3260 복사 처리: 원본 소유자·원본 일치 확인(테이블 생성 전)');
check(before(src('adm/board_thumbnail_delete.php'), "if (!admin_board_owner_check(\$board['bo_table'])) {", "require_once './admin.head.php';"), '3260 썸네일 삭제: 소유자 확인');

// ---------- KVE-2026-3261 삭제 권한(d) ----------
$m = src('adm/member_list_update.php');
check(strpos($m, "auth_check_menu(\$auth, \$sub_menu, 'w');\n\ncheck_admin_token();") === false
    && before($m, "if (\$act_button === '선택수정') {\n    auth_check_menu(\$auth, \$sub_menu, 'w');\n} elseif (\$act_button === '선택삭제') {\n    auth_check_menu(\$auth, \$sub_menu, 'd');\n} else {\n    alert('올바른 방법으로 이용해 주십시오.');\n}", 'check_admin_token();'), '3261 회원 일괄 처리: 동작별 w/d 확인, 알 수 없는 동작 거부(토큰 확인 전)');
check(strpos($m, "if (\$_POST['act_button'] ==") === false && strpos($m, "} elseif (\$act_button === \"선택삭제\") {") !== false, '3261 회원 일괄 처리: 확인한 동작 값으로 분기');
check(strpos($m, "} elseif (is_admin(\$mb['mb_id']) == 'super') {") !== false && strpos($m, "\$msg .= \$mb['mb_id'] . ' : 로그인 중인 관리자는 삭제 할 수 없습니다.\\\\n';") !== false
    && strpos($m, "자신보다 권한이 높거나 같은 회원은 삭제할 수 없습니다.") !== false, '3261 회원 삭제: 본인·최고관리자·레벨 보호 유지');
check(strpos(src('adm/member_list.php'), "<?php if (!auth_check_menu(\$auth, \$sub_menu, 'd', true)) { ?>\n            <input type=\"submit\" name=\"act_button\" value=\"선택삭제\"") !== false, '3261 회원 목록: d 권한이 없으면 선택삭제 버튼 숨김');
$branch = array(
    'adm/poll_form_update.php' => array("} elseif (\$w == 'd') {\n    auth_check_menu(\$auth, \$sub_menu, 'd');", 'delete from {$g5[\'poll_table\']}'),
    'adm/mail_update.php' => array("} elseif (\$w == 'd') {\n    auth_check_menu(\$auth, \$sub_menu, 'd');", 'delete from {$g5[\'mail_table\']}'),
    'adm/shop_admin/sendcostupdate.php' => array("if(\$w == 'd') {\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'g5_shop_sendcost_table\']}'),
    'adm/sms_admin/form_update.php' => array("else if (\$w == 'd') // 삭제\n{\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'sms5_form_table\']}'),
    'adm/sms_admin/form_multi_update.php' => array("if(\$atype == \"del\"){\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'sms5_form_table\']}'),
    'adm/sms_admin/form_group_update.php' => array("if (\$w == 'de' || \$w == 'em' || \$w == 'no') {\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'sms5_form_group_table\']}'),
    'adm/sms_admin/num_book_update.php' => array("else if (\$w=='d') // 삭제\n{\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'sms5_book_table\']}'),
    'adm/sms_admin/num_book_multi_update.php' => array("if (\$atype == 'del') {\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'sms5_book_table\']}'),
    'adm/sms_admin/num_group_update.php' => array("if (\$w == 'de' || \$w == 'em') {\n    auth_check_menu(\$auth, \$sub_menu, \"d\");", 'delete from {$g5[\'sms5_book_group_table\']}'),
    'adm/popular_list.php' => array("if (isset(\$_POST['chk']) && is_array(\$_POST['chk'])) {\n    auth_check_menu(\$auth, \$sub_menu, 'd');", 'delete from {$g5[\'popular_table\']}'),
);
$bad = array();
foreach ($branch as $p => $x) if (!before(src($p), $x[0], $x[1])) $bad[] = $p;
check(!$bad, '3261 투표·메일·추가배송비·SMS5 6개·인기검색어 삭제 분기에서 삭제 전에 d 확인 '.implode(',', $bad));
$s = src('adm/sms_admin/form_group_update.php');
check(pos_of($s, "auth_check_menu(\$auth, \$sub_menu, \"d\");") < pos_of($s, "if (\$w == 'u') // 업데이트"), '3261 SMS 이모티콘 그룹: 비우기(em·no)도 분기 전에 d 확인');
check(strpos(src('adm/shop_admin/sendcostupdate.php'), "\$sc_id = isset(\$_POST['sc_id'][\$k]) ? (int) \$_POST['sc_id'][\$k] : 0;") !== false, '3261 추가배송비 삭제 색인 [$k] 일치');
check(strpos(src('adm/shop_admin/itemeventlistupdate.php'), "auth_check_menu(\$auth, \$sub_menu, \"w\");") !== false, '3261 이벤트 상품 연결(편집 동작)은 w 유지(문서에 기록)');

// ---------- KVE-2026-3261 GET 삭제 CSRF ----------
$c = src('js/common.js');
check(strpos($c, "function g5_post_href(href)") !== false && strpos($c, 'form.method = "post";') !== false && strpos($c, "function del_post(href)") !== false, '3261 공통 JS: 링크를 POST로 보내는 함수');
$s = src('bbs/scrap_delete.php');
check(before($s, "if (\$_SERVER['REQUEST_METHOD'] !== 'POST' || !g5_check_form_token('scrap_delete', \$member['mb_id'], \$scrap_delete_token, 3600))", 'delete from {$g5[\'scrap_table\']}')
    && strpos($s, "\$ms_id = isset(\$_POST['ms_id'])") !== false && strpos($s, '$_REQUEST') === false, '3261 스크랩 삭제: POST·1회용 토큰 확인 후 삭제');
$s = src('bbs/scrap.php');
check(strpos($s, "\$scrap_delete_token = g5_get_form_token('scrap_delete', \$member['mb_id']);") !== false && strpos($s, "'&amp;token='.\$scrap_delete_token;") !== false, '3261 스크랩 목록: 토큰 발급·삭제 링크에 포함');
$bad = array();
foreach (array('skin/member/basic', 'mobile/skin/member/basic', 'theme/basic/skin/member/basic', 'theme/basic/mobile/skin/member/basic') as $d) {
    $k = src($d.'/scrap.skin.php');
    if (strpos($k, 'onclick="del_post(this.href); return false;" class="scrap_del"') === false || strpos($k, 'onclick="del(this.href)') !== false) $bad[] = $d;
}
check(!$bad, '3261 스크랩 스킨 4곳: 삭제를 POST로 전송 '.implode(',', $bad));
$s = src('shop/wishupdate.php');
check(before($s, "if (\$_SERVER['REQUEST_METHOD'] !== 'POST' || !g5_check_form_token('wish_delete', \$member['mb_id'], \$wish_delete_token, 3600))", 'delete from {$g5[\'g5_shop_wish_table\']}')
    && strpos($s, "\$_GET['wi_id']") === false, '3261 위시리스트 삭제: POST·1회용 토큰 확인 후 삭제');
$bad = array();
foreach (array('shop/wishlist.php', 'mobile/shop/wishlist.php', 'theme/basic/shop/mypage.php') as $p) {
    $k = src($p);
    if (strpos($k, "&amp;token=<?php echo \$wish_delete_token; ?>\" onclick=\"return g5_post_href(this.href);\"") === false || strpos($k, "g5_get_form_token('wish_delete', \$member['mb_id'])") === false) $bad[] = $p;
}
check(!$bad, '3261 위시리스트 화면 3곳: 토큰 발급·POST 전송 '.implode(',', $bad));
$s = src('shop/orderaddress.php');
check(before($s, "if (\$_SERVER['REQUEST_METHOD'] !== 'POST' || !g5_check_form_token('orderaddress_delete', \$member['mb_id'], \$orderaddress_delete_token, 3600))", 'delete from {$g5[\'g5_shop_order_address_table\']}')
    && before($s, 'goto_url($_SERVER[\'SCRIPT_NAME\']);', "\$orderaddress_delete_token = g5_get_form_token('orderaddress_delete', \$member['mb_id']);")
    && before($s, "\$orderaddress_delete_token = g5_get_form_token(", "include_once(G5_MSHOP_PATH.'/orderaddress.php');"), '3261 배송지 삭제: POST·1회용 토큰 확인 후 삭제, 목록 화면(모바일 포함)에 토큰 발급');
$bad = array();
foreach (array('shop/orderaddress.php', 'mobile/shop/orderaddress.php') as $p) {
    $k = src($p);
    if (strpos($k, '&amp;token=<?php echo ') === false || strpos($k, "if (confirm(\"배송지 목록을 삭제하시겠습니까?\")) {\n            g5_post_href(this.href);\n        }\n        return false;") === false) $bad[] = $p;
}
check(!$bad, '3261 배송지 창 2곳: 삭제를 POST로 전송 '.implode(',', $bad));
$s = src('bbs/ajax.autosavedel.php');
check(before($s, "if (\$_SERVER['REQUEST_METHOD'] !== 'POST' || \$requested_with !== 'xmlhttprequest') die(\"-1\");", 'delete from {$g5[\'autosave_table\']}')
    && before($s, "check_request_origin();", 'delete from') && strpos($s, '$_REQUEST') === false, '3261 임시저장글 삭제: AJAX POST·출처 확인 후 삭제');
$s = src('js/autosave.js');
check(strpos($s, '$.get(g5_bbs_url+"/ajax.autosavedel.php"') === false && strpos($s, 'type: "POST",') !== false && strpos($s, 'headers: {"X-Requested-With": "XMLHttpRequest"},') !== false, '3261 임시저장 JS: 삭제를 POST로 전송');

echo 'TOTAL '.$checks."\n";
