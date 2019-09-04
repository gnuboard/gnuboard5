<?php
include_once('./_common.php');

$check_arrays = array('exe', 'keypath', 'memid', 'reserved1', 'reserved2', 'EndPointURL', 'logpath', 'option');

foreach($check_arrays as $key){
    if( isset($_REQUEST[$key]) && $_REQUEST[$key] ){
        die('bad request');
    }

    $$key = '';
}

// 금일 인증시도 회수 체크
certify_count_check($member['mb_id'], 'ipin');

// KISA 취약점 내용(KVE-2018-0291) hpcert1.php의 $cmd 함수에 대한 인자 값은 hpcert_config.php 파일에서 설정되나, 이를 다른 페이지에서 포함한 뒤 호출할 시 임의 값 설정 가능
// 이에 include_once 를 require 로 수정함
require('./ipin.config.php');

$option = "C";// Option

// 명령어
$cmd = "$exe $keypath $memid \"{$reserved1}\" \"{$reserved2}\" $EndPointURL $logpath $option";

// 실행
exec($cmd, $out, $ret);

if($ret == 127) {
    alert_close('모듈실행 파일이 존재하지 않습니다.\\n\\n'.basename($exe).' 파일이 '.G5_PLUGIN_DIR.'/'.G5_OKNAME_DIR.'/bin 안에 있어야 합니다.');
}

if($ret == 126) {
    alert_close('모듈실행 파일의 실행권한이 없습니다.\\n\\nchmod 755 '.basename($exe).' 과 같이 실행권한을 부여해 주십시오.');
}

if($ret == -1) {
    alert_close('모듈실행 파일의 실행권한이 없습니다.\\n\\ncmd.exe의 IUSER 실행권한이 있는지 확인하여 주십시오.');
}

$pubkey = "";
$sig = "";
$curtime = "";

$pubkey=$out[0];
$sig=$out[1];
$curtime=$out[2];

$g5['title'] = 'KCB 아이핀 본인확인';
include_once(G5_PATH.'/head.sub.php');
?>

<form name="kcbInForm" method="post" action="<?php echo $kcbForm_action; ?>">
  <input type="hidden" name="IDPCODE" value="<?php echo $idpCode; ?>" />
  <input type="hidden" name="IDPURL" value="<?php echo $idpUrl; ?>" />
  <input type="hidden" name="CPCODE" value="<?php echo $cpCode; ?>" />
  <input type="hidden" name="CPREQUESTNUM" value="<?php echo $curtime; ?>" />
  <input type="hidden" name="RETURNURL" value="<?php echo $returnUrl; ?>" />
  <input type="hidden" name="WEBPUBKEY" value="<?php echo $pubkey; ?>" />
  <input type="hidden" name="WEBSIGNATURE" value="<?php echo $sig; ?>" />
</form>

<script>
document.kcbInForm.submit();
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>