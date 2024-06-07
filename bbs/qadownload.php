<?php
include_once('./_common.php');

// clean the output buffer
ob_end_clean();

$no = isset($_REQUEST['no']) ? (int) $_REQUEST['no'] : 0;

// 쿠키에 저장된 ID값과 넘어온 ID값을 비교하여 같지 않을 경우 오류 발생
// 다른곳에서 링크 거는것을 방지하기 위한 코드
if (!get_session('ss_qa_view_'.$qa_id))
    alert('잘못된 접근입니다.');

$sql = " select qa_subject, qa_file{$no}, qa_source{$no} from {$g5['qa_content_table']} where qa_id = '$qa_id' ";
$file = sql_fetch($sql);
if (!$file['qa_file'.$no])
    alert_close('파일 정보가 존재하지 않습니다.');

if($is_guest) {
    alert('다운로드 권한이 없습니다.\\n회원이시라면 로그인 후 이용해 보십시오.', G5_BBS_URL.'/login.php?url='.urlencode(G5_BBS_URL.'/qaview.php?qa_id='.$qa_id));
}

$filepath = G5_DATA_PATH.'/qa/'.$file['qa_file'.$no];
$filepath = addslashes($filepath);
$file_exist_check = (!is_file($filepath) || !file_exists($filepath)) ? false : true;

if ( false === run_replace('qa_download_file_exist_check', $file_exist_check, $file) ){
    alert('파일이 존재하지 않습니다.');
}

$g5['title'] = '다운로드 &gt; '.conv_subject($file['qa_subject'], 255);

run_event('qa_download_file_header', $file, $file_exist_check);

$original = urlencode($file['qa_source'.$no]);

header("content-type: file/unknown");
header("content-length: ".filesize($filepath));
header("content-disposition: attachment; filename=\"$original\"");
header("content-description: php generated data");
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