<?
if (!defined("_GNUBOARD_")) exit;

/*
// 081022 : CSRF 방지를 위해 코드를 작성했으나 효과가 없어 주석처리 함
if (!get_session("ss_admin")) {
    set_session("ss_admin", true);
    goto_url(".");
}
*/

// 스킨경로를 얻는다
function get_skin_dir($skin, $len='')
{
    global $g4;

    $result_array = array();

    $dirname = "$g4[path]/skin/$skin/";
    $handle = opendir($dirname);
    while ($file = readdir($handle)) 
    {
        if($file == "."||$file == "..") continue;

        if (is_dir($dirname.$file)) $result_array[] = $file;
    }
    closedir($handle);
    sort($result_array);

    return $result_array;
}

// 회원 삭제
function member_delete($mb_id)
{
    global $config;
    global $g4;

    $sql = " select mb_name, mb_nick, mb_ip, mb_recommend, mb_memo, mb_level from $g4[member_table] where mb_id= '$mb_id' ";
    $mb = sql_fetch($sql);
    if ($mb[mb_recommend]) {
        $row = sql_fetch(" select count(*) as cnt from $g4[member_table] where mb_id = '".addslashes($mb[mb_recommend])."' ");
        if ($row[cnt])
            insert_point($mb[mb_recommend], $config[cf_recommend_point] * (-1), "{$mb_id}님의 회원자료 삭제로 인한 추천인 포인트 반환", '@member', $mb[mb_recommend], "{$mb_id} 추천인 삭제");
    }

    // 회원자료는 정보만 없앤 후 아이디는 보관하여 다른 사람이 사용하지 못하도록 함 : 061025
    if ($mb[mb_level] > 1) {
        $sql = " update $g4[member_table] 
                    set mb_jumin = '',
                        mb_password = '',
                        mb_level = '1',
                        mb_email = '',
                        mb_homepage = '',
                        mb_password_q = '',
                        mb_password_a = '',
                        mb_tel = '',
                        mb_hp = '',
                        mb_zip1 = '',
                        mb_zip2 = '',
                        mb_addr1 = '',
                        mb_addr2 = '',
                        mb_birth = '',
                        mb_sex = '',
                        mb_signature = '',
                        mb_memo = '".date("Ymd",$g4['server_time'])." 삭제함\n\n$mb[mb_memo]',
                        mb_leave_date = '".date("Ymd",$g4['server_time'])."' 
                  where mb_id = '$mb_id' ";
        //echo $sql; exit;
        sql_query($sql);
    }
    
    /*
    // 회원 자료 삭제
    sql_query(" delete from $g4[member_table] where mb_id = '$mb_id' ");

    // 삭제된 자료를 또 삭제하면 완전 삭제함
    if ($mb[mb_nick] != '[삭제됨]')
    {
        // 다른 사람이 이 회원아이디를 사용하지 못하도록 아이디만 생성해 놓습니다.
        // 게시판에서 회원아이디는 삭제하지 않기 때문입니다.
        sql_query(" insert into $g4[member_table] set mb_id = '$mb_id', mb_name='$mb[mb_name]', mb_nick='[삭제됨]', mb_ip='$mb[mb_ip]', mb_datetime = '$g4[time_ymdhis]' ");
    }
    
    // 포인트 테이블에서 삭제
    sql_query(" delete from $g4[point_table] where mb_id = '$mb_id' ");
    
    // 그룹접근가능 삭제
    sql_query(" delete from $g4[group_member_table] where mb_id = '$mb_id' ");
    
    // 쪽지 삭제
    sql_query(" delete from $g4[memo_table] where me_recv_mb_id = '$mb_id' or me_send_mb_id = '$mb_id' ");
    
    // 스크랩 삭제
    sql_query(" delete from $g4[scrap_table] where mb_id = '$mb_id' ");
    
    // 관리권한 삭제
    sql_query(" delete from $g4[auth_table] where mb_id = '$mb_id' ");

    // 그룹관리자인 경우 그룹관리자를 공백으로 
    sql_query(" update $g4[group_table] set gr_admin = '' where gr_admin = '$mb_id' ");

    // 게시판관리자인 경우 게시판관리자를 공백으로
    sql_query(" update $g4[board_table] set bo_admin = '' where bo_admin = '$mb_id' ");

    // 아이콘 삭제
    @unlink("$g4[path]/data/member/".substr($mb_id,0,2)."/$mb_id.gif");
    */
}


// 회원권한을 SELECT 형식으로 얻음
function get_member_level_select($name, $start_id=0, $end_id=10, $selected='', $event='')
{
    global $g4;

    $str = "<select name='$name' $event>";
    for ($i=$start_id; $i<=$end_id; $i++)
    {
        $str .= "<option value='$i'";
        if ($i == $selected) 
            $str .= " selected";
        $str .= ">$i</option>";
    }
    $str .= "</select>";
    return $str;
}


// 회원아이디을 SELECT 형식으로 얻음
function get_member_id_select($name, $level, $selected='', $event='')
{
    global $g4;

    $sql = " select mb_id from $g4[member_table] where mb_level >= '$level' ";
    $result = sql_query($sql);
    $str = "<select name='$name' $event><option value=''>선택안함";
    for ($i=0; $row=sql_fetch_array($result); $i++) 
    {
        $str .= "<option value='$row[mb_id]'";
        if ($row[mb_id] == $selected) $str .= " selected";
        $str .= ">$row[mb_id]</option>";
    }
    $str .= "</select>";
    return $str;
}

// 권한 검사
function auth_check($auth, $attr)
{
    global $is_admin;

    if ($is_admin == "super") return;

    if (!trim($auth))
        alert("이 메뉴에는 접근 권한이 없습니다.\\n\\n접근 권한은 최고관리자만 부여할 수 있습니다.");

    $attr = strtolower($attr);

    if (!strstr($auth, $attr)) {
        if ($attr == "r")
            alert("읽을 권한이 없습니다.");
        else if ($attr == "w")
            alert("입력, 추가, 생성, 수정 권한이 없습니다.");
        else if ($attr == "d")
            alert("삭제 권한이 없습니다.");
        else 
            alert("속성이 잘못 되었습니다.");
    }
}


// 텍스트에리어 늘리기, 줄이기
function textarea_size($fld) 
{
    global $g4;

    $size = 10;
    $s  = "<table cellpadding=2 cellspacing=0 border=0 width=100%><tr><td align=right>";
    $s .= "<span onclick=\"javascript:textarea_size(document.getElementById('$fld'), {$size})\"><img src='$g4[admin_path]/img/btn_up.gif' border=0 align=absmiddle></span> ";
    $s .= "<span onclick=\"javascript:textarea_size(document.getElementById('$fld'), ".$size*(-1).")\"><img src='$g4[admin_path]/img/btn_down.gif' border=0 align=absmiddle></span>";
    $s .= "&nbsp;&nbsp;</td></tr></table>";
    return $s;
}


// 작업아이콘 출력
function icon($act, $link="", $target="_parent")
{
    global $g4;

    $img = array("입력"=>"insert", "추가"=>"insert", "생성"=>"insert", "수정"=>"modify", "삭제"=>"delete", "이동"=>"move", "그룹"=>"move", "보기"=>"view", "미리보기"=>"view", "복사"=>"copy");
    $icon = "<img src='{$g4[admin_path]}/img/icon_{$img[$act]}.gif' border=0 align=absmiddle title='$act' width=22 height=21>";
    if ($link)
        //$s = "<a href=\"$link\" target=\"$target\">$icon</a>";
        $s = "<a href=\"$link\">$icon</a>";
    else
        $s = $icon;
    return $s;
}


// rm -rf 옵션 : exec(), system() 함수를 사용할 수 없는 서버 또는 win32용 대체
// www.php.net 참고 : pal at degerstrom dot com
function rm_rf($file) 
{
    if (file_exists($file)) {
        @chmod($file,0777);
        if (is_dir($file)) {
            $handle = opendir($file); 
            while($filename = readdir($handle)) {
                if ($filename != "." && $filename != "..") 
                    rm_rf("$file/$filename");
            }
            closedir($handle);
            rmdir($file);
        } else 
            unlink($file);
    }
}

function help($help="", $left=0, $top=0)
{
    global $g4;
    static $idx = 0;

    $idx++;

    $help = preg_replace("/\n/", "<br>", $help);
    
    $str  = "<img src='$g4[admin_path]/img/icon_help.gif' border=0 width=15 height=15 align=absmiddle onclick=\"help('help$idx', $left, $top);\" style='cursor:hand;'>";
    $str .= "<div id='help$idx' style='position:absolute; display:none; z-index:9999;'>";
    $str .= "<div id='csshelp1'><div id='csshelp2'><div id='csshelp3'>$help</div></div></div>";
    $str .= "</div>";

    return $str;
}

function subtitle($title, $more="") 
{
    global $g4;

    $s = "<table width=100% cellpadding=0 cellspacing=0><tr><td width=80% align=left><table border='0' cellpadding='0' cellspacing='1'><tr><td height='24'><img src='$g4[admin_path]/img/icon_title.gif' width=20 height=9> <font color='#525252'><b>$title</b></font> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td></tr></table><table width=100% cellpadding=0 cellspacing=0><tr><td height=1></td></tr></table></td><td width=20% align=right>";
    if ($more)
        $s .= "<a href='$more'><img src='$g4[admin_path]/img/icon_more.gif' width='43' height='11' border=0 align=absmiddle></a>";
    $s .= "</td></tr></table>\n";
    
    return $s;
}

// 출력순서
function order_select($fld, $sel="") 
{
    $s = "<select name='$fld'>";
    for ($i=1; $i<=100; $i++) {
        $s .= "<option value='$i' ";
        if ($sel) {
            if ($i == $sel) {
                $s .= "selected";
            }
        } else {
            if ($i == 50) {
                $s .= "selected";
            }
        }
        $s .= ">$i</option>";
    }
    $s .= "</select>\n";

    return $s;
}

// 접근 권한 검사
if (!$member['mb_id'])
{
    //alert("로그인 하십시오.", "$g4[bbs_path]/login.php?url=" . urlencode("$_SERVER[PHP_SELF]?w=$w&mb_id=$mb_id"));
    alert("로그인 하십시오.", "$g4[bbs_path]/login.php?url=" . urlencode("$_SERVER[PHP_SELF]?$_SERVER[QUERY_STRING]"));
}
else if ($is_admin != "super") 
{
    $auth = array();
    $sql = " select au_menu, au_auth from $g4[auth_table] where mb_id = '$member[mb_id]' ";
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++) 
    {
        $auth[$row[au_menu]] = $row[au_auth];
    }

    if (!$i)
    {
        alert("최고관리자 또는 관리권한이 있는 회원만 접근 가능합니다.", $g4[path]);
    }
}

// 관리자의 아이피, 브라우저와 다르다면 세션을 끊고 관리자에게 메일을 보낸다.
$admin_key = md5($member[mb_datetime] . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
if (get_session("ss_mb_key") !== $admin_key) {

    session_destroy();

    include_once("$g4[path]/lib/mailer.lib.php");
    // 메일 알림
    mailer($member['mb_nick'], $member['mb_email'], $member['mb_email'], "XSS 공격 알림", "{$_SERVER['REMOTE_ADDR']} 아이피로 XSS 공격이 있었습니다.\n\n관리자 권한을 탈취하려는 접근이므로 주의하시기 바랍니다.\n\n해당 아이피는 차단하시고 의심되는 게시물이 있는지 확인하시기 바랍니다.\n\n$g4[url]", 0);

    alert_close("정상적으로 로그인하여 접근하시기 바랍니다.");
}

@ksort($auth);

// 가변 메뉴
unset($auth_menu);
unset($menu);
unset($amenu);
$tmp = dir($g4['admin_path']);
while ($entry = $tmp->read()) 
{
    //if (!preg_match("/^admin.menu([0-9]{3}).php/", $entry, $m)) 
    //if (!preg_match("/^admin.menu([0-9]{3}).*\.php/", $entry, $m)) 
    if (!preg_match("/^admin.menu([0-9]{3}).*\.php$/", $entry, $m)) 
        continue;  // 파일명이 menu 으로 시작하지 않으면 무시한다. 

    $amenu[$m[1]] = $entry;
    include_once($g4['admin_path']."/".$entry);
}
@ksort($amenu);

$qstr = "";
if (isset($sst)) $qstr .= "&sst=$sst";
if (isset($sod)) $qstr .= "&sod=$sod";
if (isset($sfl)) $qstr .= "&sfl=$sfl";
if (isset($stx)) $qstr .= "&stx=$stx";
if (isset($page)) $qstr .= "&page=$page";
//$qstr = "sst=$sst&sod=$sod&sfl=$sfl&stx=$stx&page=$page";
?>