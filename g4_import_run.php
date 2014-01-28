<?php
include_once('./_common.php');

set_time_limit ( 0 );
ini_set('memory_limit', '50M');

$g5['title'] = '그누보드4 DB 데이터 이전';
include_once(G5_PATH.'/_head.php');

if(empty($_POST))
    alert('올바른 방법으로 이용해 주십시오.', G5_URL);

if(get_session('tables_copied') == 'done')
    alert('DB 데이터 변환을 이미 실행하였습니다. 중복 실행시 오류가 발생할 수 있습니다.', G5_URL);

if($is_admin != 'super')
    alert('최고관리자로 로그인 후 실행해 주십시오.', G5_URL);

$g4_config_file = trim($_POST['file_path']);

if(!$g4_config_file)
    alert('config.php 파일의 경로를 입력해 주십시오.');

if(!is_file($g4_config_file))
    alert('입력하신 경로에 config.php 파일이 존재하지 않습니다.');

$is_euckr = false;
?>
<script>
// 새로고침 방지
function noRefresh()
{
    /* CTRL + N키 막음. */
    if ((event.keyCode == 78) && (event.ctrlKey == true))
    {
        event.keyCode = 0;
        return false;
    }
    /* F5 번키 막음. */
    if(event.keyCode == 116)
    {
        event.keyCode = 0;
        return false;
    }
}

document.onkeydown = noRefresh ;
</script>

<?php
flush();

// g4의 confing.php
require($g4_config_file);

if(preg_replace('/[^a-z]/', '', strtolower($g4['charset'])) == 'euckr')
    $is_euckr = true;

// member table 복사
$columns = array();
$fields = mysql_list_fields(G5_MYSQL_DB, $g5['member_table']);
$count = mysql_num_fields($fields);
for ($i = 0; $i < $count; $i++) {
    $fld = mysql_field_name($fields, $i);
    $columns[] = $fld;
}

$sql = " select * from {$g4['member_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    $sql2 = " select count(*) as cnt from {$g5['member_table']} where mb_id = '{$row['mb_id']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        if($key == 'mb_no')
            continue;

        if(!in_array($key, $columns))
            continue;

        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['member_table']} SET $sql_common ");
}

echo '<p>member table 복사</p>'.PHP_EOL;
unset($columns);
unset($fiels);

// point table 복사
$sql = " select * from {$g4['point_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        if($key == 'po_id')
            continue;

        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['point_table']} SET $sql_common ");
}
echo '<p>point table 복사</p>'.PHP_EOL;

// login table 복사
$sql = " select * from {$g4['login_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    $sql2 = " select count(*) as cnt from {$g5['login_table']} where lo_ip = '{$row['lo_ip']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['login_table']} SET $sql_common ");
}
echo '<p>login table 복사</p>'.PHP_EOL;

// visit table 복사
$sql = " select * from {$g4['visit_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    $sql2 = " select count(*) as cnt from {$g5['visit_table']} where vi_ip = '{$row['vi_ip']}' and vi_date = '{$row['vi_date']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['visit_table']} SET $sql_common ");
}
echo '<p>visit table 복사</p>'.PHP_EOL;

// visit sum table 복사
$sql = " select * from {$g4['visit_sum_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    $sql2 = " select count(*) as cnt from {$g5['visit_sum_table']} where vs_date = '{$row['vs_date']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['visit_sum_table']} SET $sql_common ");
}
echo '<p>visit sum table 복사</p>'.PHP_EOL;

// group table 복사
$columns = array();
$fields = mysql_list_fields(G5_MYSQL_DB, $g5['group_table']);
$count = mysql_num_fields($fields);
for ($i = 0; $i < $count; $i++) {
    $fld = mysql_field_name($fields, $i);
    $columns[] = $fld;
}

$sql = " select * from {$g4['group_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    $sql2 = " select count(*) as cnt from {$g5['group_table']} where gr_id = '{$row['gr_id']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        if(!in_array($key, $columns))
            continue;

        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['group_table']} SET $sql_common ");
}

echo '<p>group table 복사</p>'.PHP_EOL;
unset($columns);
unset($fiels);

// board 복사
$columns = array();
$fields = mysql_list_fields(G5_MYSQL_DB, $g5['board_table']);
$count = mysql_num_fields($fields);
for ($i = 0; $i < $count; $i++) {
    $fld = mysql_field_name($fields, $i);
    $columns[] = $fld;
}

$sql = " select * from {$g4['board_table']} ";
$result = sql_query($sql);
for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    $sql2 = " select count(*) as cnt from {$g5['board_table']} where bo_table = '{$row['bo_table']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        if(!in_array($key, $columns))
            continue;

        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['board_table']} SET $sql_common ");

    // 게시판 테이블 생성
    $bo_table = $row['bo_table'];
    $file = file(G5_ADMIN_PATH.'/sql_write.sql');
    $sql = implode($file, "\n");

    $create_table = $g5['write_prefix'] . $bo_table;

    $source = array('/__TABLE_NAME__/', '/;/');
    $target = array($create_table, '');
    $sql = preg_replace($source, $target, $sql);

    // 게시글 복사
    if(sql_query($sql, FALSE)) {
        $write_table = $g4['write_prefix'].$bo_table;
        $columns2 = array();
        $fields2 = mysql_list_fields(G5_MYSQL_DB, $create_table);
        $count2 = mysql_num_fields($fields2);
        for ($j = 0; $j < $count2; $j++) {
            $fld = mysql_field_name($fields2, $j);
            $columns2[] = $fld;
        }

        $sql3 = " select * from $write_table ";
        $result3 = sql_query($sql3);

        for($k=0; $row3=sql_fetch_array($result3); $k++) {
            if($is_euckr)
                $row3 = array_map('iconv_utf8', $row3);

            $comma3 = '';
            $sql_common3 = '';

            foreach($row3 as $key=>$val) {
                if(!in_array($key, $columns2))
                    continue;

                $sql_common3 .= $comma3 . " $key = '".addslashes($val)."' ";

                $comma3 = ',';
            }

            // 첨부파일개수
            $wr_id = $row3['wr_id'];
            $sql4 = " select count(*) as cnt from {$g4['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' ";
            $row4 = sql_fetch($sql4);

            $sql_common3 .= " , wr_file = '{$row4['cnt']}' ";

            sql_query(" INSERT INTO $create_table SET $sql_common3 ");
        }

        echo '<p>'.str_replace(G5_TABLE_PREFIX.'write_', '', $create_table).' 게시글 복사</p>';
    }
}

unset($columns);
unset($fiels);

// 그외 테이블 복사
$tables = array('board_file', 'board_new', 'board_good', 'mail', 'memo', 'group_member', 'auth', 'popular', 'poll', 'poll_etc', 'scrap');

foreach($tables as $table) {
    $columns = array();
    $fields = mysql_list_fields(G5_MYSQL_DB, $g5[$table.'_table']);
    $count = mysql_num_fields($fields);
    for ($i = 0; $i < $count; $i++) {
        $fld = mysql_field_name($fields, $i);
        $columns[] = $fld;
    }

    $src_table = $g4[$table.'_table'];
    $dst_table = $g5[$table.'_table'];
    $sql = " select * from $src_table ";
    $result = sql_query($sql);
    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($is_euckr)
            $row = array_map('iconv_utf8', $row);

        $comma = '';
        $sql_common = '';

        foreach($row as $key=>$val) {
            if(!in_array($key, $columns))
                continue;

            $sql_common .= $comma . " $key = '".addslashes($val)."' ";

            $comma = ',';
        }

        $result2 = sql_query(" INSERT INTO $dst_table SET $sql_common ", false);

        if(!$result2)
            continue;
    }

    echo '<p>'.$table.' table 복사</p>'.PHP_EOL;
}

unset($columns);
unset($fiels);

echo '<p>&nbsp;</p>'.PHP_EOL;
echo '<p><b>그누보드4 DB 데이터 이전 완료</b></p>'.PHP_EOL;

// 실행완료 세션에 기록
set_session('tables_copied', 'done');

include_once(G5_PATH.'/_tail.php');
?>