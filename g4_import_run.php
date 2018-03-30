<?php
include_once('./_common.php');

ob_end_clean();

set_time_limit ( 0 );
ini_set('memory_limit', '50M');

if(empty($_POST))
    alert('올바른 방법으로 이용해 주십시오.', G5_URL);

if (!preg_match("/^http['s']?:\/\/".$_SERVER['HTTP_HOST']."/", $_SERVER['HTTP_REFERER'])){
    alert("제대로 된 접근이 아닌것 같습니다.", G5_URL);
}

// 토큰체크
check_write_token('g4_import');

if(get_session('tables_copied') == 'done')
    alert('DB 데이터 변환을 이미 실행하였습니다. 중복 실행시 오류가 발생할 수 있습니다.', G5_URL);

if($is_admin != 'super')
    alert('최고관리자로 로그인 후 실행해 주십시오.', G5_URL);

$g4_config_file = trim($_POST['file_path']);

if(!$g4_config_file)
    alert('config.php 파일의 경로를 입력해 주십시오.');

$g4_config_file = preg_replace('#/config.php$#i', '', $g4_config_file).'/config.php';

if(!is_file($g4_config_file))
    alert('입력하신 경로에 config.php 파일이 존재하지 않습니다.');

$is_euckr = false;

$g5['title'] = '그누보드4 DB 데이터 이전';
include_once(G5_PATH.'/head.php');
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

<style>
#g4_import_run {}
#g4_import_run ol {margin: 0;padding: 0 0 0 25px;border: 1px solid #E9E9E9;border-bottom: 0;background: #f5f8f9;list-style:none;zoom:1}
#g4_import_run li {padding:7px 10px;border-bottom:1px solid #e9e9e9}
#g4_import_run #run_msg {padding:30px 0;text-align:center}
#container_wr #aside{display:none}
</style>

<div id="g4_import_run">
    <ol>
<?php
flush();

// g4의 confing.php
require('./'.$g4_config_file);

if(preg_replace('/[^a-z]/', '', strtolower($g4['charset'])) == 'euckr')
    $is_euckr = true;

// member table 복사
$columns = sql_field_names($g5['member_table']);

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

echo '<li>member table 복사</li>'.PHP_EOL;
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
echo '<li>point table 복사</li>'.PHP_EOL;

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
echo '<li>login table 복사</li>'.PHP_EOL;

// visit table 복사
$sql = " select * from {$g4['visit_table']} ";
$result = sql_query($sql);

// g5_visit 테이블 초기화
sql_query(" delete from {$g5['visit_table']} ");

for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    /*
    $sql2 = " select count(*) as cnt from {$g5['visit_table']} where vi_ip = '{$row['vi_ip']}' and vi_date = '{$row['vi_date']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;
    */

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['visit_table']} SET $sql_common ");
}
echo '<li>visit table 복사</li>'.PHP_EOL;

// visit sum table 복사
$sql = " select * from {$g4['visit_sum_table']} ";
$result = sql_query($sql);

// g5_visit_sub 테이블 초기화
sql_query(" delete from {$g5['visit_sum_table']} ");

for($i=0; $row=sql_fetch_array($result); $i++) {
    if($is_euckr)
        $row = array_map('iconv_utf8', $row);

    // 중복체크
    /*
    $sql2 = " select count(*) as cnt from {$g5['visit_sum_table']} where vs_date = '{$row['vs_date']}' ";
    $row2 = sql_fetch($sql2);
    if($row2['cnt'])
        continue;
    */

    $comma = '';
    $sql_common = '';

    foreach($row as $key=>$val) {
        $sql_common .= $comma . " $key = '".addslashes($val)."' ";

        $comma = ',';
    }

    sql_query(" INSERT INTO {$g5['visit_sum_table']} SET $sql_common ");
}
echo '<li>visit sum table 복사</li>'.PHP_EOL;

// group table 복사
$columns = sql_field_names($g5['group_table']);

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

echo '<li>group table 복사</li>'.PHP_EOL;
unset($columns);
unset($fiels);

// board 복사
$columns = sql_field_names($g5['board_table']);

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

    // 모바일 스킨 디렉토리
    if( ! isset($row['bo_mobile_skin']) ){
        $row['bo_mobile_skin'] = 'basic';
    }

    // 모바일 제목 길이
    if( ! isset($row['bo_mobile_subject_len']) ){
        $row['bo_mobile_subject_len'] = '30';
    }
    
    // 모바일 페이지당 목록 수
    if( ! isset($row['bo_mobile_page_rows']) ){
        $row['bo_mobile_page_rows'] = '15';
    }

    // 갤러리 이미지 폭 ( 리스트 )
    if( ! isset($row['bo_gallery_width']) ){
        $row['bo_gallery_width'] = '174';
    }

    // 갤러리 이미지 높이 ( 리스트 )
    if( ! isset($row['bo_gallery_height']) ){
        $row['bo_gallery_height'] = '124';
    }

    // 모바일 갤러리 이미지 폭 ( 리스트 )
    if( ! isset($row['bo_mobile_gallery_width']) ){
        $row['bo_mobile_gallery_width'] = '125';
    }

    // 모바일 갤러리 이미지 높이 ( 리스트 )
    if( ! isset($row['bo_mobile_gallery_height']) ){
        $row['bo_mobile_gallery_height'] = '100';
    }

    foreach($row as $key=>$val) {
        if(!in_array($key, $columns))
            continue;
        
        if($key === 'bo_notice'){
            $val = str_replace("\n", ",", $val);

            if( substr($val, -1)  === ',' ){
                $val = substr($val, 0, -1);
            }
        }

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
        $columns2 = sql_field_names($create_table);

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

        echo '<li>'.str_replace(G5_TABLE_PREFIX.'write_', '', $create_table).' 게시글 복사</li>';
    }
}

unset($columns);
unset($fiels);

// 그외 테이블 복사
$tables = array('board_file', 'board_new', 'board_good', 'mail', 'memo', 'group_member', 'auth', 'popular', 'poll', 'poll_etc', 'scrap');

foreach($tables as $table) {
    $columns = sql_field_names($g5[$table.'_table']);

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

    echo '<li>'.$table.' table 복사</li>'.PHP_EOL;
}

unset($columns);
unset($fiels);

echo '</ol>'.PHP_EOL;

echo '<div id="run_msg">그누보드4 DB 데이터 이전 완료</div>'.PHP_EOL;

// 실행완료 세션에 기록
set_session('tables_copied', 'done');
?>
</div>

<?php
include_once(G5_PATH.'/tail.php');
?>