<?php
include_once("../../../common.php");

function print_error($type, $msg) {
    if(strtolower($type) == "json") {
        $res = array();
        $res['uploaded'] = 0;
        $res['error']['message'] = $msg;
        echo json_encode($res);
    } else {
        echo "<script> alert('{$msg}'); </script>";
    }
    exit;
}

class EditorImage
{
    protected   $isUse      = false;
    protected   $tblName    = "";   // 테이블명
    protected   $delDay     = 1;    // 삭제 대기 일수

    function __construct() {
        if(USE_EDITOR_IMAGE) $this->isUse = true;

        global $g5;

        $this->tblName = !empty($g5['editor_image_table']) ? $g5['editor_image_table'] : G5_TABLE_PREFIX.'editor_image';

        $this->make_table();
    }

    // 테이블 생성
    function make_table() {
        /*
        $sql_chk = "select * from information_schema.tables where table_name = '{$this->tblName}'";
        $res_chk = sql_query($sql_chk);
        if(sql_num_rows($res_chk) < 1) {
            $sql = "
                CREATE TABLE `{$this->tblName}` (
                  `ei_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT '고유값',
                  `ei_gubun` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '사용구분',
                  `ei_gubun_id` INT(20) NOT NULL DEFAULT 0 COMMENT '사용구분번호',
                  `mb_id` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '회원아이디',
                  `ei_name_original` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '원본파일명',
                  `ei_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '업로드 파일명',
                  `ei_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '업로드 경로',
                  `ei_ext` VARCHAR(10) NOT NULL DEFAULT '' COMMENT '파일확장자',
                  `ei_size` INT(11) NOT NULL DEFAULT 0 COMMENT '파일용량',
                  `ei_width` INT(11) NOT NULL DEFAULT 0 COMMENT '이미지 너비',
                  `ei_height` INT(11) NOT NULL DEFAULT 0 COMMENT '이미지 높이',
                  `ei_ip` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '업로드 ip',
                  `ei_datetime` DATETIME NOT NULL COMMENT '업로드 일시',
                  PRIMARY KEY (`ei_id`),
                  INDEX `mb_id` (`mb_id` ASC),
                  INDEX `select` (`ei_gubun` ASC, `ei_gubun_id` ASC),
                  INDEX `ip` (`ei_ip` ASC))
                ENGINE = MyISAM
                DEFAULT CHARACTER SET = utf8
                COMMENT = '에디터 업로드 이미지 관리';
            ";
            sql_query($sql);
        }
        */

        $arr = array();
        $res = sql_query("desc g5_editor_image");
        while($row = sql_fetch_array($res)) {
            $arr[] = $row['Field'];
        }
        $alter = array();
        if(!in_array("ei_gubun_sub", $arr)) {
            $alter[] = " ADD COLUMN `ei_gubun_sub` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '사용구분-에디터ID' AFTER `ei_gubun` ";
        }
        if(!in_array("ei_gubun_path", $arr)) {
            $alter[] = " CHANGE COLUMN `ei_gubun` `ei_gubun` VARCHAR(20) NOT NULL DEFAULT '' COMMENT '사용구분-폼이름' ";
            $alter[] = " ADD COLUMN `ei_gubun_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT '사용구분-업로드경로' AFTER `ei_gubun_sub` ";
        }
        if(in_array("ei_gubun_id", $arr)) {
            $alter[] = " drop column `ei_gubun_id` ";
        }
        if(in_array("ei_token", $arr)) {
            $alter[] = " drop column `ei_token` ";
        }
        if(count($alter) > 0) {
            $sql = " ALTER TABLE `g5_editor_image` ".implode(",", $alter);
            sql_query($sql);
        }
    }

    // 이미지 경로 출력
    function img_url($path) {
        $_path  = preg_replace('/^\/.*\/'.G5_DATA_DIR.'/', '/'.G5_DATA_DIR, $path);
        $_url   = G5_URL.$_path;
        
        return $_url;
    }

    // 업로드 썸네일 생성
    function img_thumbnail($srcfile, $thumb_width=0, $thumb_height=0, $path=0) {
        $is_animated = false;   // animated GIF 체크
        if(is_file($srcfile)) {

            $filename = basename($srcfile);
            $filepath = dirname($srcfile);

            $size = @getimagesize($srcfile);

            if(function_exists('exif_read_data')) {
                // exif 정보를 기준으로 회전각도 구함
                $exif = @exif_read_data($srcfile);
                $degree = 0;
                if(!empty($exif['Orientation'])) {
                    switch($exif['Orientation']) {
                        case 8:
                            $degree = 90;
                            break;
                        case 3:
                            $degree = 180;
                            break;
                        case 6:
                            $degree = -90;
                            break;
                    }

                    // 회전각도를 구한다.
                    if($degree) {
                        // 세로사진의 경우 가로, 세로 값 바꿈
                        if($degree == 90 || $degree == -90) {
                            $tmp = $size;
                            $size[0] = $tmp[1];
                            $size[1] = $tmp[0];
                        }
                    }
                }
            }

            $width = $size[0];  // 너비
            $height = $size[1]; // 높이
            $fType = $size[2];  // 파일 종류

            // 원본 크기가 지정한 크기보다 크면 썸네일 생성진행
            if($width > $thumb_width || ($thumb_height > 0 && $height > $thumb_height)) {
                #echo $width.PHP_EOL;
                #echo $thumb_width.PHP_EOL;
                #echo $height.PHP_EOL;
                #echo $thumb_height.PHP_EOL;

                // 원본비율에 맞게 너비/높이 계산
                $temp_width = $thumb_width;
                $temp_height = round(($temp_width * $height) / $width);
                // 계산된 높이가 지정된 높이보다 높을경우
                if($thumb_height > 0 && $thumb_height < $temp_height) {
                    $temp_height = $thumb_height;
                    $temp_width = round(($temp_height * $width) / $height);
                }

                // 썸네일 생성
                if($fType == 1 && is_animated_gif($srcfile)) {  // animated GIF 인 경우
                    $is_animated = true;

                    $thumb_filename = preg_replace("/\.[^\.]+$/i", "", $filename);
                    $thumb_filename = "thumb-{$thumb_filename}_{$temp_width}x{$temp_height}.gif";

                    // 썸네일이 없으면 생성시작
                    if(!is_file($filepath."/".$thumb_filename)) {
                        $ani_src = @ImageCreateFromGif($srcfile);
                        $ani_img = @imagecreatetruecolor($temp_width, $temp_height);
                        @ImageColorAllocate($ani_img, 255, 255, 255);
                        @ImageCopyResampled($ani_img, $ani_src, 0, 0, 0, 0, $temp_width, $temp_height, ImageSX($ani_src),ImageSY($ani_src));

                        @ImageInterlace($ani_img); 
                        @ImageGif($ani_img, $filepath."/".$thumb_filename); 
                    }
                } else {    // 일반 이미지
                    $thumb_filename = thumbnail($filename, $filepath, $filepath, $temp_width, $temp_height, false);
                }
            }
            // 처리된 내용이 없으면 기존 파일 사용
            if(empty($thumb_filename)) {
                $thumb_filename = $filename;
            }
            

            switch($path) {
                case 1 :
                    $thumb_file = $filepath."/".$thumb_filename;
                    $thumb_file = str_replace(G5_DATA_PATH, G5_DATA_URL, $thumb_file);
                break;
                default:
                    $thumb_file = $thumb_filename;
                break;
            }
        }

        $res = array();
        $res['src'] = $thumb_file;
        $res['animated'] = $is_animated;

        return $res;
    }

    // 이미지 삭제
    function img_del($arr) {
        if(count($arr) < 1) return;
        // 선택된 번호들 나열
        $del_ei_id = implode("', '", $arr);
        // 파일별 경로 조회
        $sql_chk = "select ei_id, ei_path ";
        $sql_chk .= " from {$this->tblName} ";
        $sql_chk .= " where ei_id in ('{$del_ei_id}')";
        $res_chk = sql_query($sql_chk);
        $del_arr = array();
        while($row = sql_fetch_array($res_chk)) {
            $path = $row['ei_path'];
            // 조회된 경로의 파일 삭제진행
            unlink($path);
            // 썸네일 삭제
            $filename = preg_replace("/\.[^\.]+$/i", "", basename($path));
            $filepath = dirname($path);
            $files = glob($filepath.'/thumb-'.$filename.'*');
            if (is_array($files)) {
                foreach($files as $filename) unlink($filename);
            }

            $del_arr[] = $row['ei_id'];
        }
        // 파일이 삭제가 되면 DB내용 삭제
        $sql_del = "delete ";
        $sql_del .= " from {$this->tblName} ";
        $sql_del .= " where ei_id in ('".implode("','", $del_arr)."')";
        sql_query($sql_del);
    }

    // 업로드 이미지 테이블 저장
    function insert_data($file, $upload, $gubun, $gubun_sub, $gubun_path) {
        if(!$this->isUse) return;

        global $member;
        $mb_id  = $member['mb_id'];

        $oname  = $file['name'];
        $fsize  = $file['size'];
        $ip     = $_SERVER['REMOTE_ADDR'];
        $ftmp   = explode(".", $oname);
        $fext   = $ftmp[count($ftmp)-1];
        $fpath  = $upload;
        $utmp   = getimagesize($upload);
        $width  = $utmp[0];
        $height = $utmp[1];
        $uftmp  = explode("/", $upload);
        $fname  = $uftmp[count($uftmp)-1];

        $sql = "
            insert into {$this->tblName} set
                ei_gubun    = '{$gubun}',
                ei_gubun_sub = '{$gubun_sub}',
                ei_gubun_path = '{$gubun_path}',
                mb_id       = '{$mb_id}',
                ei_name_original = '{$oname}',
                ei_name     = '{$fname}',
                ei_path     = '{$fpath}',
                ei_ext      = '{$fext}',
                ei_size     = '{$fsize}',
                ei_width    = '{$width}',
                ei_height   = '{$height}',
                ei_ip       = '{$ip}',
                ei_datetime = now()
        ";
        $res = sql_query($sql);

        return $res;
    }
}

// 업로드 경로 세팅
$ym = date('ym', G5_SERVER_TIME);
$data_dir = G5_DATA_PATH.'/editor/'.$ym;
$data_url = G5_DATA_URL.'/editor/'.$ym;
@mkdir($data_dir, G5_DIR_PERMISSION);
@chmod($data_dir, G5_DIR_PERMISSION);
 
// 업로드 DIALOG 에서 전송된 값
$funcNum = $_GET['CKEditorFuncNum'] ;

if(function_exists('run_event')) run_event('ckeditor_photo_upload', $data_dir, $data_url);

// 업로드 대상 파일
$upFile = $_FILES['upload'];
if(empty($upFile['tmp_name'])) {
    $msg = "파일이 존재하지 않습니다.";
    print_error($responseType, $msg);
}

$fileInfo = pathinfo($upFile['name']);
$filename  = $fileInfo['filename'];
$extension = $fileInfo['extension'];
$extension = strtolower($extension);

if (!preg_match("/(jpe?g|gif|png|webp)$/i", $extension)) {
    $msg = "jpg / gif / png / webp 파일만 가능합니다.";
    print_error($responseType, $msg);
}
// jpeg 확장자 jpg로 통일되도록
if($extension == 'jpeg') $extension = 'jpg';

// 윈도우에서 한글파일명으로 업로드 되지 않는 오류 해결
$file_name = sprintf('%u', ip2long($_SERVER['REMOTE_ADDR'])).'_'.get_microtime().".".$extension;
$save_dir = sprintf('%s/%s', $data_dir, $file_name);

if (move_uploaded_file($upFile["tmp_name"], $save_dir)) {
    $ei = new EditorImage();
    $ins = $ei->insert_data($upFile, $save_dir, $_GET['editor_form_name'], $_GET['editor_id'], $_GET['editor_uri']);
    
    // 썸네일 생성
    $img_width = $is_mobile ? 320 : 730;
    $tmp_thumb = $ei->img_thumbnail($save_dir, $img_width);
    $img_thumb = $tmp_thumb['src'];
    $save_url = sprintf('%s/%s', $data_url, $img_thumb);

    // 성공 결과 출력
    if(strtolower($responseType) == "json") {
        $res = array();
        $res['fileName'] = $file_name;
        $res['url'] = $save_url;
        $res['uploaded'] = 1;
        $res['inserted'] = $ins;

        if($file_name != $img_thumb) {  // 이름이 다르면 지정사이즈를 초과하여 썸네일화된것으로 간주, 출력 사이즈 지정
            $res['width'] = "100%";
            $res['height'] = "auto";
        }
        echo json_encode($res);
    } else {
        echo "<script>window.parent.CKEDITOR.tools.callFunction({$funcNum}, '{$save_url}', '');</script>";
    }
    exit;
}

$msg = "업로드 실패";
print_error($responseType, $msg);
?>