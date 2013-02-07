<?
$sub_menu = "400300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

if (!trim($it_id))
	alert("복사할 상품코드가 없습니다.");

$row = sql_fetch(" select count(*) as cnt from $g4[shop_item_table] where it_id = '$new_it_id' ");
if ($row[cnt])
    alert('이미 존재하는 상품코드 입니다.');

$sql = " select * from $g4[shop_item_table] where it_id = '$it_id' limit 1 ";
$cp = sql_fetch($sql);


// 상품테이블의 필드가 추가되어도 수정하지 않도록 필드명을 추출하여 insert 퀴리를 생성한다. (상품코드만 새로운것으로 대체)
$sql_common = "";
$fields = mysql_list_fields(G4_MYSQL_DB, $g4[shop_item_table]);
$columns = mysql_num_fields($fields);
for ($i = 0; $i < $columns; $i++) {
  $fld = mysql_field_name($fields, $i);
  if ($fld != 'it_id') {
      $sql_common .= " , $fld = '".addslashes($cp[$fld])."' ";
  }
}

$sql = " insert $g4[shop_item_table]
			set it_id = '$new_it_id'
                $sql_common ";
sql_query($sql);

// 선택옵션정보 copy
$opt_sql = " insert ignore into {$g4['shop_option_table']} ( opt_id, it_id, opt_amount, opt_qty, opt_notice, opt_use )
                select opt_id, '$new_it_id', opt_amount, opt_qty, opt_notice, opt_use
                    from {$g4['shop_option_table']}
                    where it_id = '$it_id'
                    order by opt_no asc ";
sql_query($opt_sql);
/*
$opt_sql = " select * from `{$g4['shop_option_table']}` where it_id = '$it_id' order by opt_no asc ";
$opt_result = sql_query($opt_sql);
for($j = 0; $opt_row = sql_fetch_array($opt_result); $j++) {
    $new_opt_id = str_replace($it_id.'-', $new_it_id.'-', $opt_row['opt_id']);
    $ins_sql = " insert into `{$g4['shop_option_table']}`
                    set opt_id      = '$new_opt_id',
                        it_id       = '$new_it_id',
                        opt_amount  = '{$opt_row['opt_amount']}',
                        opt_qty     = '{$opt_row['opt_qty']}',
                        opt_notice  = '{$opt_row['opt_notice']}',
                        opt_use     = '{$opt_row['opt_use']}' ";
    sql_query($ins_sql);
}
*/

// 추가옵션정보 copy
$sp_sql = " insert ignore into {$g4['shop_supplement_table']} ( sp_id, it_id, sp_amount, sp_qty, sp_notice, sp_use )
                select sp_id, '$new_it_id', sp_amount, sp_qty, sp_notice, sp_use
                from {$g4['shop_supplement_table']}
                where it_id = '$it_id'
                order by sp_no asc ";
sql_query($sp_sql);
/*
$sp_sql = " select * from `{$g4['shop_supplement_table']}` where it_id = '$it_id' order by sp_no asc ";
$sp_result = sql_query($sp_sql);
for($j = 0; $sp_row = sql_fetch_array($sp_result); $j++) {
    $new_sp_id = str_replace($it_id.'-', $new_it_id.'-', $sp_row['sp_id']);
    $ins_sql = " insert into `{$g4['shop_supplement_table']}`
                    set sp_id       = '$new_sp_id',
                        it_id       = '$new_it_id',
                        sp_amount   = '{$sp_row['sp_amount']}',
                        sp_qty      = '{$sp_row['sp_qty']}',
                        sp_notice   = '{$sp_row['sp_notice']}',
                        sp_use      = '{$sp_row['sp_use']}' ";
    sql_query($ins_sql);
}
*/

// 상품요약정보 copy
$ii_sql = " insert ignore into {$g4['shop_item_info_table']} ( it_id, ii_gubun, ii_article, ii_title, ii_value )
                select '$new_it_id', ii_gubun, ii_article, ii_title, ii_value
                from {$g4['shop_item_info_table']}
                where it_id = '$it_id'
                order by ii_id asc ";
sql_query($ii_sql);

// html 에디터로 첨부된 이미지 파일 복사
$sql = " select it_explan from {$g4['shop_item_table']} where it_id = '$it_id' ";
$it = sql_fetch($sql);

if($it['it_explan']) {
    // img 태그의 src 중 data/editor 가 포함된 것만 추출
    preg_match_all("/<img[^>]*src=[\'\"]?([^>\'\"]+data\/editor[^>\'\"]+)[\'\"]?[^>]*>/", $it['it_explan'], $matchs);

    // 파일의 경로를 얻어 복사
    for($i=0; $i<count($matchs[1]); $i++) {
        $imgurl = parse_url($matchs[1][$i]);

        $srcfile = $_SERVER['DOCUMENT_ROOT'].$imgurl['path'];
        $dstfile = preg_replace("/\.([^\.]+)$/", "_copy.\\1", $srcfile);

        if(file_exists($srcfile)) {
            copy($srcfile, $dstfile);

            $newfile = preg_replace("/\.([^\.]+)$/", "_copy.\\1", $matchs[1][$i]);
            $it['it_explan'] = str_replace($matchs[1][$i], $newfile, $it['it_explan']);
        }
    }

    $sql = " update {$g4['shop_item_table']} set it_explan = '{$it['it_explan']}' where it_id = '$new_it_id' ";
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
        @mkdir($dest_dir, 0707);
        @chmod($dest_dir, 0707);
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
            @chmod($dest_file, 0606);
        }
    }
}

// 파일복사
$data_path = G4_DATA_PATH.'/item';
copy_directory($data_path.'/'.$it_id, $data_path.'/'.$new_it_id);

//$qstr = "$ca_id=$ca_id&$qstr";
$qstr = "$ca_id=$ca_id&sfl=$sfl&sca=$sca&page=$page&stx=".urlencode($stx)."&save_stx=".urlencode($save_stx);

goto_url("itemlist.php?$qstr");
?>