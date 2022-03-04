<?php
include_once("_common.php");

define("CHE_UPLOAD_IMG_CHECK", 1);  // �̹��� ������ ����� �Ҽ� �ִ��� ���θ� üũ�մϴ�. ( �ش� ������ �̹��� �������� üũ�մϴ�. 1�̸� ���, 0�̸� ��� ���� )

// ---------------------------------------------------------------------------

# �̹����� ����� ���丮�� ��ü ��θ� �����մϴ�.
# ���� ������(/)�� ������ �ʽ��ϴ�.
# ����: �� ����� ���� ������ ����, �бⰡ �����ϵ��� ������ �ֽʽÿ�.

# data/editor ���丮�� ���� ��찡 ������ �����Ƿ� ���丮�� �����ϴ� �ڵ带 �߰���. kagla 140305

// s3 사용시 에디터 저장 url 설정
$ym = date('ym', G5_SERVER_TIME);

if(isset($g5['s3'])) {
    $data_dir = $g5['s3']->getPath().'/'.G5_DATA_DIR.'/editor/'.$ym;
    $data_url = $g5['s3']->getUrl().'/'.G5_DATA_DIR.'/editor/'.$ym;
} else {

    @mkdir(G5_DATA_PATH.'/'.G5_EDITOR_DIR, G5_DIR_PERMISSION);
    @chmod(G5_DATA_PATH.'/'.G5_EDITOR_DIR, G5_DIR_PERMISSION);

    $data_dir = G5_DATA_PATH.'/'.G5_EDITOR_DIR.'/'.$ym;
    $data_url = G5_DATA_URL.'/'.G5_EDITOR_DIR.'/'.$ym;

    @mkdir(SAVE_DIR, G5_DIR_PERMISSION);
    @chmod(SAVE_DIR, G5_DIR_PERMISSION);
}

# ������ ������ 'SAVE_DIR'�� URL�� �����մϴ�.
# ���� ������(/)�� ������ �ʽ��ϴ�.
define("SAVE_DIR", $data_dir);
define("SAVE_URL", $data_url);

function che_get_user_id() {
    global $member;

    if(session_id() == '') {
        @session_start();
    }

    $add_str = (isset($member['mb_id']) && $member['mb_id']) ? $member['mb_id'] : '';
    return session_id().$add_str;
}

function che_get_file_passname(){
    $tmp_name = che_get_user_id().$_SERVER['REMOTE_ADDR'];
    $tmp_name = md5(sha1($tmp_name));
    return $tmp_name;
}

function che_generateRandomString($length = 4) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function che_replace_filename($filename){

    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    $random_str = che_generateRandomString(4);

    $passname = che_get_file_passname();
    
    $file_arr = explode('_', $filename);

    return $file_arr[0].'_'.$passname.'_'.$random_str.'.'.$ext;
}