<?php
include_once('./_common.php');

// clean the output buffer
ob_end_clean();

$no = isset($_REQUEST['no']) ? (int) $_REQUEST['no'] : 0;

@include_once($board_skin_path.'/download.head.skin.php');

// 쿠키에 저장된 ID값과 넘어온 ID값을 비교하여 같지 않을 경우 오류 발생
// 다른곳에서 링크 거는것을 방지하기 위한 코드
if (!get_session('ss_view_'.$bo_table.'_'.$wr_id))
    alert('잘못된 접근입니다.');

// 다운로드 차감일 때 비회원은 다운로드 불가
if($board['bo_download_point'] < 0 && $is_guest)
    alert('다운로드 권한이 없습니다.\\n회원이시라면 로그인 후 이용해 보십시오.', G5_BBS_URL.'/login.php?wr_id='.$wr_id.'&amp;'.$qstr.'&amp;url='.urlencode(get_pretty_url($bo_table, $wr_id)));

$sql = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$no' ";
$file = sql_fetch($sql);
if (!$file['bf_file'])
    alert_close('파일 정보가 존재하지 않습니다.');

// JavaScript 불가일 때
$js = (isset($_GET['js'])) ? $_GET['js'] : '';
if($js != 'on' && $board['bo_download_point'] < 0) {
    $msg = $file['bf_source'].' 파일을 다운로드 하시면 포인트가 차감('.number_format($board['bo_download_point']).'점)됩니다.\\n포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.\\n그래도 다운로드 하시겠습니까?';
    $url1 = G5_BBS_URL.'/download.php?'.clean_query_string($_SERVER['QUERY_STRING'], false).'&js=on';
    $url2 = isset($_SERVER['HTTP_REFERER']) ? clean_xss_tags($_SERVER['HTTP_REFERER']) : '';
    
    if( $url2 && stripos($url2, $_SERVER['REQUEST_URI']) !== false ){
        $url2 = G5_BBS_URL.'/board.php?'.clean_query_string($_SERVER['QUERY_STRING'], false);
    }

    //$url1 = 확인link, $url2=취소link
    // 특정주소로 이동시키려면 $url3 이용
    confirm($msg, $url1, $url2);
}

if ($member['mb_level'] < $board['bo_download_level']) {
    $alert_msg = '다운로드 권한이 없습니다.';
    if ($member['mb_id'])
        alert($alert_msg);
    else
        alert($alert_msg.'\\n회원이시라면 로그인 후 이용해 보십시오.', G5_BBS_URL.'/login.php?wr_id='.$wr_id.'&amp;'.$qstr.'&amp;url='.urlencode(get_pretty_url($bo_table, $wr_id)));
}

$filepath = G5_DATA_PATH.'/file/'.$bo_table.'/'.$file['bf_file'];
$filepath = addslashes($filepath);
$file_exist_check = (!is_file($filepath) || !file_exists($filepath)) ? false : true;

if ( false === run_replace('download_file_exist_check', $file_exist_check, $file) ){
    alert('파일이 존재하지 않습니다.');
}

// 사용자 코드 실행
@include_once($board_skin_path.'/download.skin.php');

// 이미 다운로드 받은 파일인지를 검사한 후 게시물당 한번만 포인트를 차감하도록 수정
$ss_name = 'ss_down_'.$bo_table.'_'.$wr_id;
if (!get_session($ss_name))
{
    // 자신의 글이라면 통과
    // 관리자인 경우 통과
    if (($write['mb_id'] && $write['mb_id'] == $member['mb_id']) || $is_admin)
        ;
    else if ($board['bo_download_level'] >= 1) // 회원이상 다운로드가 가능하다면
    {
        // 다운로드 포인트가 음수이고 회원의 포인트가 0 이거나 작다면
        if ($member['mb_point'] + $board['bo_download_point'] < 0)
            alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 다운로드('.number_format($board['bo_download_point']).')가 불가합니다.\\n\\n포인트를 적립하신 후 다시 다운로드 해 주십시오.');

        // 게시물당 한번만 차감하도록 수정
        insert_point($member['mb_id'], $board['bo_download_point'], "{$board['bo_subject']} $wr_id 파일 다운로드", $bo_table, $wr_id, "다운로드");
    }

    set_session($ss_name, TRUE);
}

// 이미 다운로드 받은 파일인지를 검사한 후 다운로드 카운트 증가 ( SIR 그누위즈 님 코드 제안 )
$ss_name = 'ss_down_'.$bo_table.'_'.$wr_id.'_'.$no;
if (!get_session($ss_name))
{
    // 다운로드 카운트 증가
    $sql = " update {$g5['board_file_table']} set bf_download = bf_download + 1 where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$no' ";
    sql_query($sql);
    // 다운로드 카운트를 증가시키고 세션을 생성
    $_SESSION[$ss_name] = true;
}

$g5['title'] = '다운로드 &gt; '.conv_subject($write['wr_subject'], 255);

//파일명에 한글이 있는 경우
/*
if(preg_match("/[\xA1-\xFE][\xA1-\xFE]/", $file['bf_source'])){
    // 2015.09.02 날짜의 파이어폭스에서 인코딩된 문자 그대로 출력되는 문제가 발생됨, 2018.12.11 날짜의 파이어폭스에서는 해당 현상이 없으므로 해당 코드를 사용 안합니다.
    $original = iconv('utf-8', 'euc-kr', $file['bf_source']); // SIR 잉끼님 제안코드
} else {
    $original = urlencode($file['bf_source']);
}
*/

//$original = urlencode($file['bf_source']);
$original = rawurlencode($file['bf_source']);

@include_once($board_skin_path.'/download.tail.skin.php');

run_event('download_file_header', $file, $file_exist_check);

if(preg_match("/msie/i", $_SERVER['HTTP_USER_AGENT']) && preg_match("/5\.5/", $_SERVER['HTTP_USER_AGENT'])) {
    header("content-type: doesn/matter");
    header("content-length: ".filesize($filepath));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-transfer-encoding: binary");
} else if (preg_match("/Firefox/i", $_SERVER['HTTP_USER_AGENT'])){
    header("content-type: file/unknown");
    header("content-length: ".filesize($filepath));
    //header("content-disposition: attachment; filename=\"".basename($file['bf_source'])."\"");
    header("content-disposition: attachment; filename=\"".$original."\"");
    header("content-description: php generated data");
} else {
    header("content-type: file/unknown");
    header("content-length: ".filesize($filepath));
    header("content-disposition: attachment; filename=\"$original\"");
    header("content-description: php generated data");
}
header("pragma: no-cache");
header("expires: 0");
flush();

$fp = fopen($filepath, 'rb');

// 4.00 대체
// 서버부하를 줄이려면 print 나 echo 또는 while 문을 이용한 방법보다는 이방법이...
//if (!fpassthru($fp)) {
//    fclose($fp);
//}

$download_rate = 10;

while(!feof($fp)) {
    //echo fread($fp, 100*1024);
    /*
    echo fread($fp, 100*1024);
    flush();
    */

    print fread($fp, round($download_rate * 1024));
    flush();
    usleep(1000);
}
fclose ($fp);
flush();