<?php
include_once('./_common.php');
g5_require_board_read($board, $write);

include_once(G5_PATH.'/head.sub.php');

if (!$is_member)
{
    $href = './login.php?'.$qstr.'&amp;url='.urlencode(get_pretty_url($bo_table, $wr_id));
    $href = str_replace('&amp;', '&', $href);
    $js_replace = array('\\' => '\\\\', '"' => '\\"', "'" => '\\u0027', '/' => '\\/', "\r" => '\\r', "\n" => '\\n', "\t" => '\\t', '<' => '\\u003C', '>' => '\\u003E', '&' => '\\u0026', "\xE2\x80\xA8" => '\\u2028', "\xE2\x80\xA9" => '\\u2029');
    $js_href = function_exists('get_js_safe_string') ? get_js_safe_string($href) : '"'.strtr((string)$href, $js_replace).'"';
    echo '<script> alert(\'회원만 접근 가능합니다.\'); top.location.href = '.$js_href.'; </script>';
    exit;
}

// 게시글 존재하는지
if(! (isset($write['wr_id']) && $write['wr_id']))
    alert_close('스크랩하시려는 게시글이 존재하지 않습니다.');

// 스크랩 창에서 발급한 1회용 토큰을 가진 같은 출처의 POST만 허용한다. (KVE-2026-3202)
if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST')
    alert_close('올바른 방법으로 이용해 주십시오.');
check_request_origin();
$scrap_token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
if (!g5_check_form_token('scrap_'.$bo_table.'_'.(int)$wr_id, $member['mb_id'], $scrap_token))
    alert_close('올바른 방법으로 이용해 주십시오. 스크랩 창을 다시 열어 주십시오.');

$wr_content = isset($_POST['wr_content']) ? trim($_POST['wr_content']) : '';
$is_scrap_comment = $wr_content && ($member['mb_level'] >= $board['bo_comment_level']);

// 스크랩 댓글에도 연속 등록 제한을 적용한다. (기존 조건은 $w == 'c'일 때만 동작해 이 경로에 적용되지 않았다.)
if ($is_scrap_comment && !$is_admin && isset($_SESSION['ss_datetime']) && $_SESSION['ss_datetime'] >= (G5_SERVER_TIME - $config['cf_delay_sec']))
    alert_close('너무 빠른 시간내에 게시물을 연속해서 올릴 수 없습니다.');

// 같은 회원·게시물의 중복 확인과 스크랩 등록은 이름 잠금 안에서 한 번에 처리한다.
// 댓글·포인트는 스크랩 등록이 성공한 요청에서만 처리한다. (insert_point도 잠금을 쓰므로 잠금을 푼 뒤 처리)
$scrap_lock = 'g5_scrap_'.md5($member['mb_id'].'|'.$bo_table.'|'.$wr_id);
if (!g5_get_named_lock($scrap_lock))
    alert_close('잠시 후 다시 시도해 주십시오.');

$sql = " select count(*) as cnt from {$g5['scrap_table']}
            where mb_id = '{$member['mb_id']}'
            and bo_table = '$bo_table'
            and wr_id = '$wr_id' ";
$row = sql_fetch($sql);
if (isset($row['cnt']) && $row['cnt'])
{
    g5_release_named_lock($scrap_lock);
    echo '
    <script>
    if (confirm(\'이미 스크랩하신 글 입니다.'."\n\n".'지금 스크랩을 확인하시겠습니까?\'))
        document.location.href = \'./scrap.php\';
    else
        window.close();
    </script>
    <noscript>
    <p>이미 스크랩하신 글 입니다.</p>
    <a href="./scrap.php">스크랩 확인하기</a>
    <a href="'.get_pretty_url($bo_table, $wr_id).'">돌아가기</a>
    </noscript>';
    exit;
}

$sql = " insert into {$g5['scrap_table']} ( mb_id, bo_table, wr_id, ms_datetime ) values ( '{$member['mb_id']}', '$bo_table', '$wr_id', '".G5_TIME_YMDHIS."' ) ";
sql_query($sql);
$scrap_id = sql_insert_id();
g5_release_named_lock($scrap_lock);

if (!$scrap_id)
    alert_close('스크랩하지 못했습니다. 잠시 후 다시 시도해 주십시오.');

// 덧글이 넘어오고 코멘트를 쓸 권한이 있다면
if ($is_scrap_comment)
{
    $wr = get_write($write_table, $wr_id);
    // 원글이 존재한다면
    if (isset($wr['wr_id']) && $wr['wr_id'])
    {

        set_session('ss_datetime', G5_SERVER_TIME);

        $mb_id = $member['mb_id'];
        $wr_name = addslashes(clean_xss_tags($board['bo_use_name'] ? $member['mb_name'] : $member['mb_nick']));
        $wr_password = $member['mb_password'];
        $wr_email = addslashes($member['mb_email']);
        $wr_homepage = addslashes(clean_xss_tags($member['mb_homepage']));

        $sql = " select max(wr_comment) as max_comment from $write_table
                    where wr_parent = '$wr_id' and wr_is_comment = '1' ";
        $row = sql_fetch($sql);
        $row['max_comment'] += 1;

        $sql = " insert into $write_table
                    set ca_name = '{$wr['ca_name']}',
                         wr_option = '',
                         wr_num = '{$wr['wr_num']}',
                         wr_reply = '',
                         wr_parent = '$wr_id',
                         wr_is_comment = '1',
                         wr_comment = '{$row['max_comment']}',
                         wr_content = '$wr_content',
                         mb_id = '$mb_id',
                         wr_password = '$wr_password',
                         wr_name = '$wr_name',
                         wr_email = '$wr_email',
                         wr_homepage = '$wr_homepage',
                         wr_datetime = '".G5_TIME_YMDHIS."',
                         wr_ip = '{$_SERVER['REMOTE_ADDR']}' ";
        sql_query($sql);

        $comment_id = sql_insert_id();

        // 원글에 코멘트수 증가
        sql_query(" update $write_table set wr_comment = wr_comment + 1 where wr_id = '$wr_id' ");

        // 새글 INSERT
        sql_query(" insert into {$g5['board_new_table']} ( bo_table, wr_id, wr_parent, bn_datetime, mb_id ) values ( '$bo_table', '$comment_id', '$wr_id', '".G5_TIME_YMDHIS."', '{$member['mb_id']}' ) ");

        // 코멘트 1 증가
        sql_query(" update {$g5['board_table']}  set bo_count_comment = bo_count_comment + 1 where bo_table = '$bo_table' ");

        // 포인트 부여
        insert_point($member['mb_id'], $board['bo_comment_point'], "{$board['bo_subject']} {$wr_id}-{$comment_id} 댓글쓰기(스크랩)", $bo_table, $comment_id, '댓글');
    }
}

$sql = " update `{$g5['member_table']}` set mb_scrap_cnt = '".get_scrap_totals($member['mb_id'])."' where mb_id = '{$member['mb_id']}' ";
sql_query($sql);

delete_cache_latest($bo_table);
?>
<script>
    if (confirm("이 글을 스크랩 하였습니다.\n\n지금 스크랩을 확인하시겠습니까?"))
        document.location.href = "./scrap.php";
    else
        window.close();
</script>
<noscript>
<p>이 글을 스크랩 하였습니다.</p>
<a href="./scrap.php">스크랩 확인하기</a>
</noscript>
