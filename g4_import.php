<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');

$g5['title'] = '그누보드4 DB 데이터 이전';
include_once(G5_PATH.'/'.G5_THEME_DIR.'/basic/head.sub.php');

if(get_session('tables_copied') == 'done')
    alert('DB 데이터 변환을 이미 실행하였습니다. 중복 실행시 오류가 발생할 수 있습니다.', G5_URL);

if($is_admin != 'super')
    alert('최고관리자로 로그인 후 실행해 주십시오.', G5_URL);

include_once(G5_PATH.'/head.php');
?>

<style>
#g4_import p {padding:0 0 10px;line-height:1.8em}
#g4_import_frm {margin:20px 0 30px;padding:30px 0;border:1px solid #e9e9e9;background:#f5f8f9;text-align:center}
#g4_import_frm .frm_input {background-color:#fff !important}
#g4_import_frm .btn_submit {padding:0 10px;height:24px}
</style>

<div id="g4_import">
    <p>
        이 프로그램은 그누보드5 설치 후 바로 실행하셔야만 합니다.<br>
        만약 그누보드5 사이트를 운영 중에 이 프로그램을 실행하시면 DB 데이터가 망실되거나 데이터의 오류가 발생할 수 있습니다.<br>
        또한 중복해서 실행하실 경우에도 DB 데이터의 오류가 발생할 수 있으니 반드시 한번만 실행해 주십시오.
    </p>
    <p>프로그램을 실행하시려면 그누보드4의 config.php 파일 경로를 입력하신 후 확인을 클릭해 주십시오.</p>

    <form name="fimport" method="post" action="./g4_import_run.php" onsubmit="return fimport_submit(this);">
    <input type="hidden" name="token" value="" >
    <div id="g4_import_frm">
        <label for="file_path">config.php 파일 경로</label>
        <input type="text" name="file_path" id="file_path" required class="frm_input required">
        <input type="submit" value="확인" class="btn_submit">
    </div>
    </form>

    <p>
        경로는 그누보드5 설치 루트를 기준으로 그누보드4의 config.php 파일의 상대경로입니다.<br>
        예를 들어 그누보드4를 웹루트에 설치하셨고 그누보드5를 g5라는 하위 폴더에 설치하셨다면 입력하실 경로는 ../config.php 입니다.
    </p>

</div>

<script>
function fimport_submit(f)
{
    var token = get_write_token('g4_import');
    
    f.token.value = token;

    return confirm('그누보드4의 DB 데이터를 이전하시겠습니까?');
}
</script>

<?php
include_once(G5_PATH.'/tail.php');