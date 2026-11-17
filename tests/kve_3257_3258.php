<?php
// 실행: php tests/kve_3257_3258.php (DB 없이 KVE-2026-3257 SMS JS 출력 인코딩과 KVE-2026-3258 분류 소유자 검사를 검사)
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
function pos_of($s, $needle) { $p = strpos($s, $needle); return $p === false ? -1 : $p; }
function func_src($path, $name, $indent) {
    if (!preg_match('/^'.$indent.'function '.$name.'\(.*?^'.$indent.'\}/ms', src($path), $m)) throw new Exception('함수 찾기 실패: '.$name);
    return $m[0];
}

// ---------- KVE-2026-3257 JS 리터럴 인코딩 ----------
eval(func_src('lib/common.lib.php', 'get_js_safe_string', '    '));
$payloads = array(
    '";document.documentElement.dataset.g5SmsDirect=1;//\');document.documentElement.dataset.g5SmsClick=1;//',
    "a'b\"c\\d", "line1\nline2\r\nline3", '</script><script>alert(1)</script>', "u2028\xE2\x80\xA8u2029\xE2\x80\xA9",
    '&quot;&#39;&amp;', '홍길동 😀', '',
);
$bad = array();
foreach ($payloads as $i => $p) {
    $lit = get_js_safe_string($p);
    if (json_decode($lit) !== $p) $bad[] = $i.':roundtrip';
    if (preg_match('/[\'<>&\n\r]|\xE2\x80[\xA8\xA9]/', $lit)) $bad[] = $i.':raw';
    if (!preg_match('/^"(?:[^"\\\\]|\\\\.)*"$/s', $lit)) $bad[] = $i.':delim';
    $attr = htmlspecialchars($lit, ENT_QUOTES);
    if (strpbrk($attr, '"\'') !== false || html_entity_decode($attr, ENT_QUOTES, 'UTF-8') !== $lit) $bad[] = $i.':attr';
}
check(!$bad, '3257 get_js_safe_string 결과가 원문으로 복원되고 따옴표·태그·개행·U+2028/2029가 그대로 남지 않음 '.implode(',', $bad));

$w = src('adm/sms_admin/sms_write.php');
check(strpos($w, "\"<?php echo \$row['bk_name']") === false && strpos($w, "get_js_safe_string(\$row['bk_name'].' ('.\$row['bk_hp'].')')") !== false, '3257 개별 보내기 수신자 이름을 JS 리터럴로 출력');
check(strpos($w, 'add(\"$fo_content\")') === false && strpos($w, 'add(".get_js_safe_string($fo_content).")') !== false, '3257 문구 불러오기 JS 리터럴 출력');
check(strpos($w, 'add(\"$wr_message\")') === false && strpos($w, "value = '{\$row['wr_reply']}'") === false
    && strpos($w, 'add(".get_js_safe_string($wr_message).")') !== false, '3257 재전송 문구·회신번호 JS 리터럴 출력');
check(strpos($w, "new Option('\$str', '\$val')") === false && substr_count($w, 'new Option(".get_js_safe_string($str).", ".get_js_safe_string($val).")') === 2, '3257 재전송 수신자 목록 2곳 JS 리터럴 출력');
check(strpos($w, "if (isset(\$row['bk_no']) && \$row['bk_no'])") !== false, '3257 없는 bk_no는 목록에 넣지 않음');

$p = src('adm/sms_admin/ajax.sms_write_person.php');
check(strpos($p, "'<?php echo get_text(\$res['bk_name']) ?>'") === false
    && strpos($p, "htmlspecialchars(get_js_safe_string(\$res['bk_name']), ENT_QUOTES)") !== false
    && strpos($p, "htmlspecialchars(get_js_safe_string(\$res['bk_hp']), ENT_QUOTES)") !== false, '3257 수신자 추가 onclick 인자를 JS 리터럴+속성 인코딩');
$g = src('adm/sms_admin/ajax.sms_write_group.php');
check(strpos($g, "'<?php echo \$no_group['bg_name']?>'") === false && strpos($g, "'<?php echo \$group[\$i]['bg_name']?>'") === false
    && substr_count($g, 'htmlspecialchars(get_js_safe_string(') === 4, '3257 그룹 추가 onclick 인자 인코딩');
$bad = array();
foreach (glob($root.'/adm/sms_admin/*.php') as $f) {
    $c = src(substr($f, strlen($root) + 1));
    if (preg_match('/onclick="[^"]*\'<\?php echo (?!\(int\))/', $c)) $bad[] = basename($f);
}
check(!$bad, '3257 SMS 관리 화면에 따옴표로 감싼 PHP 값 onclick 출력 없음 '.implode(',', $bad));

// ---------- KVE-2026-3258 분류 소유자 검사 ----------
eval(func_src('adm/shop_admin/admin.shop.lib.php', 'shop_admin_category_owner_check', ''));
function sql_real_escape_string($s) { return addslashes($s); }
$CATS = array('aa' => 'manager', 'bb' => 'owner', 'cc' => '', 'dd' => 'Manager');
$QUERIES = array();
function sql_fetch($sql) {
    $GLOBALS['QUERIES'][] = $sql;
    if (preg_match("/where ca_id = '([^']*)'/", $sql, $m) && isset($GLOBALS['CATS'][$m[1]])) return array('ca_mb_id' => $GLOBALS['CATS'][$m[1]]);
    return null;
}
$g5 = array('g5_shop_category_table' => 'g5_shop_category');
$is_admin = ''; $member = array('mb_id' => 'manager');
check(shop_admin_category_owner_check('aa') === true, '3258 본인 분류 허용');
check(shop_admin_category_owner_check('bb') === false, '3258 다른 관리자 분류 거부');
check(shop_admin_category_owner_check('cc') === false && shop_admin_category_owner_check('zz') === false, '3258 관리 회원 없는 분류·없는 분류 거부');
check(shop_admin_category_owner_check('dd') === false, '3258 대소문자만 다른 회원아이디 거부');
check(shop_admin_category_owner_check("bb' or '1'='1") === false && strpos(end($QUERIES), "where ca_id = 'bbor11'") !== false, '3258 ca_id 영숫자 외 문자 제거');
check(shop_admin_category_owner_check('') === false && shop_admin_category_owner_check(array('aa')) === false, '3258 빈 값·배열 거부');
$member = array('mb_id' => ''); check(shop_admin_category_owner_check('cc') === false, '3258 비회원 거부');
$is_admin = 'super'; $member = array('mb_id' => 'admin'); $n = count($QUERIES);
check(shop_admin_category_owner_check('bb') === true && count($QUERIES) === $n, '3258 최고관리자는 DB 조회 없이 허용');

$f = src('adm/shop_admin/categoryform.php');
$add = pos_of($f, 'alert("최고관리자만 1단계 분류를 추가할 수 있습니다.");');
$add_chk = pos_of($f, 'if ($ca_id && !shop_admin_category_owner_check($ca_id))');
$upd = pos_of($f, 'else if ($w == "u")');
$upd_chk = strpos($f, 'if (!shop_admin_category_owner_check($ca_id))', $upd);
check($add >= 0 && $add < $add_chk && $add_chk < $upd && $upd_chk !== false, '3258 상세 폼 하위 분류 추가(부모)·수정(대상) 소유자 확인');

$u = src('adm/shop_admin/categoryformupdate.php');
$chk = pos_of($u, "if (\$is_admin != 'super') {\n    if (\$w == 'u') {");
check($chk >= 0 && $chk < pos_of($u, 'check_admin_token();') && $chk < pos_of($u, " insert {\$g5['g5_shop_category_table']}")
    && strpos($u, "shop_admin_category_owner_check(substr(\$ca_id, 0, -2))") !== false, '3258 단일 처리 앞단 소유자 확인(수정·하위 추가)');
$force = pos_of($u, "\$ca_mb_id = \$_POST['ca_mb_id'] = addslashes(\$member['mb_id']);");
check($force > pos_of($u, '$$key = $_POST[$key] = $value;') && $force < pos_of($u, '$sql_common = " ca_order'), '3258 위임 관리자 ca_mb_id 요청 값 무시');
check(preg_match("/where ca_id = '\\\$ca_id' \";\n    if \(\\\$is_admin != 'super'\)\n        \\\$sql .= \" and ca_mb_id = /", $u) === 1, '3258 단일 UPDATE 소유자 조건');
check(strpos($u, "if (\$w == 'd' && \$is_admin != 'super')") !== false, '3258 위임 관리자 삭제 금지 유지');

$l = src('adm/shop_admin/categorylistupdate.php');
$pre = pos_of($l, 'if (!shop_admin_category_owner_check($chk_ca_id))');
check($pre >= 0 && $pre < pos_of($l, " update {\$g5['g5_shop_category_table']}") && $pre < pos_of($l, '$str_ca_mb_id ='), '3258 일괄 수정 전 제출 분류 전체 소유자 확인');
check(strpos($l, "\$str_ca_mb_id = \$_POST['ca_mb_id'][\$i] = \$member['mb_id'];") !== false, '3258 일괄 수정 ca_mb_id 요청 값 무시');
check(strpos($l, "where ca_id = '\".sql_real_escape_string(\$posts['ca_id']).\"' \";\n    if (\$is_admin != 'super')\n        \$sql .= \" and ca_mb_id = ") !== false, '3258 일괄 UPDATE 소유자 조건');

echo 'TOTAL '.$checks."\n";
