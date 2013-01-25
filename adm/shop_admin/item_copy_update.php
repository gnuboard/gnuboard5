<?
$sub_menu = "400300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

if (!trim($it_id))
	alert("복사할 상품코드가 없습니다.");

$row = sql_fetch(" select count(*) as cnt from $g4[yc4_item_table] where it_id = '$new_it_id' ");
if ($row[cnt])
    alert('이미 존재하는 상품코드 입니다.');

$sql = " select * from $g4[yc4_item_table] where it_id = '$it_id' limit 1 ";
$cp = sql_fetch($sql);


// 상품테이블의 필드가 추가되어도 수정하지 않도록 필드명을 추출하여 insert 퀴리를 생성한다. (상품코드만 새로운것으로 대체)
$sql_common = "";
$fields = mysql_list_fields($mysql_db, $g4[yc4_item_table]);
$columns = mysql_num_fields($fields);
for ($i = 0; $i < $columns; $i++) {
  $fld = mysql_field_name($fields, $i);
  if ($fld != 'it_id') {
      $sql_common .= " , $fld = '".addslashes($cp[$fld])."' ";
  }
}

$sql = " insert $g4[yc4_item_table]
			set it_id = '$new_it_id'
                $sql_common ";
sql_query($sql);

// 선택옵션정보 copy
$opt_sql = " select * from `{$g4['yc4_option_table']}` where it_id = '$it_id' order by opt_no asc ";
$opt_result = sql_query($opt_sql);
for($j = 0; $opt_row = sql_fetch_array($opt_result); $j++) {
    $new_opt_id = str_replace($it_id.'-', $new_it_id.'-', $opt_row['opt_id']);
    $ins_sql = " insert into `{$g4['yc4_option_table']}`
                    set opt_id      = '$new_opt_id',
                        it_id       = '$new_it_id',
                        opt_amount  = '{$opt_row['opt_amount']}',
                        opt_qty     = '{$opt_row['opt_qty']}',
                        opt_notice  = '{$opt_row['opt_notice']}',
                        opt_use     = '{$opt_row['opt_use']}' ";
    sql_query($ins_sql);
}

// 추가옵션정보 copy
$sp_sql = " select * from `{$g4['yc4_supplement_table']}` where it_id = '$it_id' order by sp_no asc ";
$sp_result = sql_query($sp_sql);
for($j = 0; $sp_row = sql_fetch_array($sp_result); $j++) {
    $new_sp_id = str_replace($it_id.'-', $new_it_id.'-', $sp_row['sp_id']);
    $ins_sql = " insert into `{$g4['yc4_supplement_table']}`
                    set sp_id       = '$new_sp_id',
                        it_id       = '$new_it_id',
                        sp_amount   = '{$sp_row['sp_amount']}',
                        sp_qty      = '{$sp_row['sp_qty']}',
                        sp_notice   = '{$sp_row['sp_notice']}',
                        sp_use      = '{$sp_row['sp_use']}' ";
    sql_query($ins_sql);
}

$img_path = "$g4[path]/data/item/";

for($i=1; $i<6; $i++) {
	$limg = $it_id."_l".$i;
		if(is_file($img_path.$limg))
			copy($img_path.$limg,$img_path.$new_it_id."_l".$i);

}

$simg = $it_id."_s";
if(is_file($img_path.$simg))
    copy($img_path.$simg,$img_path.$new_it_id."_s");

$mimg = $it_id."_m";
if(is_file($img_path.$mimg))
    copy($img_path.$mimg,$img_path.$new_it_id."_m");

//$qstr = "$ca_id=$ca_id&$qstr";
$qstr = "$ca_id=$ca_id&sfl=$sfl&sca=$sca&page=$page&stx=".urlencode($stx)."&save_stx=".urlencode($save_stx);

goto_url("itemlist.php?$qstr");
?>