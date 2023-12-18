<?php
$sub_menu = '400300';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$ca_id = isset($_REQUEST['ca_id']) ? preg_replace('/[^0-9a-z]/i', '', $_REQUEST['ca_id']) : '';
$it_id = isset($_REQUEST['it_id']) ? safe_replace_regex($_REQUEST['it_id'], 'it_id') : '';

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

if (!trim($it_id))
	alert("복사할 상품코드가 없습니다.");

if(isset($_POST['new_it_id']) && preg_match('/[^A-Za-z0-9\-_]+/', $_POST['new_it_id']))
    alert("상품코드는 영문자, 숫자, -, _ 만 사용할 수 있습니다.");

$new_it_id = isset($_REQUEST['new_it_id']) ? preg_replace("/[^A-Za-z0-9\-_]/", "", $_REQUEST['new_it_id']) : '';

if( ! $new_it_id ) alert('상품코드를 입력해 주세요.');

$row = sql_fetch(" select count(*) as cnt from {$g5['g5_shop_item_table']} where it_id = '$new_it_id' ");
if (isset($row['cnt']) && $row['cnt'])
    alert('이미 존재하는 상품코드 입니다.');

$sql = " select * from {$g5['g5_shop_item_table']} where it_id = '$it_id' limit 1 ";
$cp = sql_fetch($sql);

// 상품테이블의 필드가 추가되어도 수정하지 않도록 필드명을 추출하여 insert 퀴리를 생성한다. (상품코드만 새로운것으로 대체)
$sql_common = "";
$fields = sql_field_names($g5['g5_shop_item_table']);
foreach($fields as $fld) {
    if ($fld == 'it_id' || $fld == 'it_sum_qty' || $fld == 'it_use_cnt' || $fld == 'it_use_avg')
        continue;

    $sql_common .= " , $fld = '".addslashes($cp[$fld])."' ";
}

$sql = " insert {$g5['g5_shop_item_table']}
			set it_id = '$new_it_id'
                $sql_common ";
sql_query($sql);

// 선택/추가 옵션 copy
$opt_sql = " insert ignore into {$g5['g5_shop_item_option_table']} ( io_id, io_type, it_id, io_price, io_stock_qty, io_noti_qty, io_use )
                select io_id, io_type, '$new_it_id', io_price, io_stock_qty, io_noti_qty, io_use
                    from {$g5['g5_shop_item_option_table']}
                    where it_id = '$it_id'
                    order by io_no asc ";
sql_query($opt_sql);

// html 에디터로 첨부된 이미지 파일 복사
$copied_editor_images = array();
if($cp['it_explan']) {
    $matchs = get_editor_image($cp['it_explan'], false);
    $count_matchs = (isset($matchs[1]) && is_array($matchs[1])) ? count($matchs[1]) : 0;

    // 파일의 경로를 얻어 복사
    for($i=0;$i<$count_matchs;$i++) {
        $p = parse_url($matchs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $src_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $src_path = $p['path'];

        $srcfile = G5_PATH.$src_path;
        $dstfile = preg_replace("/\.([^\.]+)$/", "_".$new_it_id.".\\1", $srcfile);

        if(is_file($srcfile)) {
            copy($srcfile, $dstfile);

            $newfile = preg_replace("/\.([^\.]+)$/", "_".$new_it_id.".\\1", $matchs[1][$i]);
            $cp['it_explan'] = str_replace($matchs[1][$i], $newfile, $cp['it_explan']);

            $copied_editor_images[] = array(
                'original' => $srcfile,
                'new' => $dstfile
            );
        }
        
    }

    $sql = " update {$g5['g5_shop_item_table']} set it_explan = '".addslashes($cp['it_explan'])."' where it_id = '$new_it_id' ";
    sql_query($sql);
}

if($cp['it_mobile_explan']) {
    $matchs = get_editor_image($cp['it_mobile_explan'], false);
    $count_matchs = (isset($matchs[1]) && is_array($matchs[1])) ? count($matchs[1]) : 0;

    // 파일의 경로를 얻어 복사
    for($i=0;$i<$count_matchs;$i++) {
        $p = parse_url($matchs[1][$i]);
        if(strpos($p['path'], "/data/") != 0)
            $src_path = preg_replace("/^\/.*\/data/", "/data", $p['path']);
        else
            $src_path = $p['path'];

        $srcfile = G5_PATH.$src_path;
        $dstfile = preg_replace("/\.([^\.]+)$/", "_".$new_it_id.".\\1", $srcfile);

        if(is_file($srcfile)) {
            copy($srcfile, $dstfile);

            $newfile = preg_replace("/\.([^\.]+)$/", "_".$new_it_id.".\\1", $matchs[1][$i]);
            $cp['it_mobile_explan'] = str_replace($matchs[1][$i], $newfile, $cp['it_mobile_explan']);

            $copied_editor_images[] = array(
                'original' => $srcfile,
                'new' => $dstfile
            );
        }
    }

    $sql = " update {$g5['g5_shop_item_table']} set it_mobile_explan = '".addslashes($cp['it_mobile_explan'])."' where it_id = '$new_it_id' ";
    sql_query($sql);
}

// 상품이미지 복사
function copy_directory($src_dir, $dest_dir)
{
    if($src_dir == $dest_dir)
        return false;

    if(!is_dir($src_dir))
        return false;

    if(!is_dir($dest_dir)) {
        @mkdir($dest_dir, G5_DIR_PERMISSION);
        @chmod($dest_dir, G5_DIR_PERMISSION);
    }

    $dir = opendir($src_dir);
    while (false !== ($filename = readdir($dir))) {
        if($filename == "." || $filename == "..")
            continue;

        $files[] = $filename;
    }

    for($i=0; $i<count($files); $i++) {
        $src_file = $src_dir.'/'.$files[$i];
        $dest_file = $dest_dir.'/'.$files[$i];
        if(is_file($src_file)) {
            copy($src_file, $dest_file);
            @chmod($dest_file, G5_FILE_PERMISSION);
        }
    }
}

// 파일복사
$copied_item_files = array();
$dest_path = G5_DATA_PATH.'/item/'.$new_it_id;
@mkdir($dest_path, G5_DIR_PERMISSION);
@chmod($dest_path, G5_DIR_PERMISSION);
$comma = '';
$sql_img = '';

for($i=1; $i<=10; $i++) {
    $file = G5_DATA_PATH.'/item/'.$cp['it_img'.$i];
    $new_img = '';

    if(is_file($file)) {
        $dstfile = $dest_path.'/'.basename($file);
        copy($file, $dstfile);
        @chmod($dstfile, G5_FILE_PERMISSION);
        $new_img = $new_it_id.'/'.basename($file);

        $copied_item_files[] = array(
            'original' => $file,
            'new' => $dstfile,
        );
    }

    $sql_img .= $comma." it_img{$i} = '$new_img' ";
    $comma = ',';
}

$sql = " update {$g5['g5_shop_item_table']}
            set $sql_img
            where it_id = '$new_it_id' ";
sql_query($sql);

if( function_exists('shop_seo_title_update') ) shop_seo_title_update($new_it_id, true);

/**
 * 아이템 복사 처리 후 Event Hook
 * @var string $it_id 원본 아이템 ID
 * @var string $new_it_id 복사한 새로운 아이템 ID
 * @var array $cp 복사한 아이템 정보
 * @var array $copied_item_files 복사한 파일 목록
 * @var array $copied_editor_images 복사한 에디터 이미지 목록
 */
run_event('shop_admin_itemcopy', array(
    'it_id' => (string) $it_id,
    'new_it_id' => (string) $new_it_id,
    'cp' => $cp,
    'copied_item_files' => $copied_item_files,
    'copied_editor_images' => $copied_editor_images
));

$qstr = "ca_id=$ca_id&amp;sfl=$sfl&amp;sca=$sca&amp;page=$page&amp;stx=".urlencode($stx);

goto_url("itemlist.php?$qstr");