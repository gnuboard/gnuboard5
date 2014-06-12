<?php
include_once('./_common.php');

ob_end_clean();

include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');

set_time_limit ( 0 );
ini_set('memory_limit', '50M');

$g5['title'] = '그누보드4 DB 데이터 이전';
include_once(G5_PATH.'/head.sub.php');

echo '<link rel="stylesheet" href="'.G5_URL.'/g4_import.css">';

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

<style>
#g4_import_run {}
#g4_import_run ol {margin: 0;padding: 0 0 0 25px;border: 1px solid #E9E9E9;border-bottom: 0;background: #f5f8f9;list-style:none;zoom:1}
#g4_import_run li {padding:7px 10px;border-bottom:1px solid #e9e9e9}
#g4_import_run #run_msg {padding:30px 0;text-align:center}
</style>

<!-- 상단 시작 { -->
<div id="hd">
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>

    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    <div id="hd_wrapper">

        <div id="logo">
            <a href="<?php echo G5_URL ?>"><img src="<?php echo G5_IMG_URL ?>/logo.jpg" alt="<?php echo $config['cf_title']; ?>"></a>
        </div>

        <fieldset id="hd_sch">
            <legend>사이트 내 전체검색</legend>
            <form name="fsearchbox" method="get" action="<?php echo G5_BBS_URL ?>/search.php" onsubmit="return fsearchbox_submit(this);">
            <input type="hidden" name="sfl" value="wr_subject||wr_content">
            <input type="hidden" name="sop" value="and">
            <label for="sch_stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
            <input type="text" name="stx" id="sch_stx" maxlength="20">
            <input type="submit" id="sch_submit" value="검색">
            </form>

            <script>
            function fsearchbox_submit(f)
            {
                if (f.stx.value.length < 2) {
                    alert("검색어는 두글자 이상 입력하십시오.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                // 검색에 많은 부하가 걸리는 경우 이 주석을 제거하세요.
                var cnt = 0;
                for (var i=0; i<f.stx.value.length; i++) {
                    if (f.stx.value.charAt(i) == ' ')
                        cnt++;
                }

                if (cnt > 1) {
                    alert("빠른 검색을 위하여 검색어에 공백은 한개만 입력할 수 있습니다.");
                    f.stx.select();
                    f.stx.focus();
                    return false;
                }

                return true;
            }
            </script>
        </fieldset>

        <ul id="tnb">
            <?php if ($is_member) {  ?>
            <?php if ($is_admin) {  ?>
            <li><a href="<?php echo G5_ADMIN_URL ?>"><b>관리자</b></a></li>
            <?php }  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/member_confirm.php?url=<?php echo G5_BBS_URL ?>/register_form.php">정보수정</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/logout.php">로그아웃</a></li>
            <?php } else {  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/register.php">회원가입</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/login.php"><b>로그인</b></a></li>
            <?php }  ?>
            <li><a href="<?php echo G5_BBS_URL ?>/qalist.php">1:1문의</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/current_connect.php">접속자 <?php echo connect(); // 현재 접속자수  ?></a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/new.php">새글</a></li>
        </ul>

        <div id="text_size">
            <!-- font_resize('엘리먼트id', '제거할 class', '추가할 class'); -->
            <button id="size_down" onclick="font_resize('container', 'ts_up ts_up2', '');"><img src="<?php echo G5_URL; ?>/img/ts01.gif" alt="기본"></button>
            <button id="size_def" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up');"><img src="<?php echo G5_URL; ?>/img/ts02.gif" alt="크게"></button>
            <button id="size_up" onclick="font_resize('container', 'ts_up ts_up2', 'ts_up2');"><img src="<?php echo G5_URL; ?>/img/ts03.gif" alt="더크게"></button>
        </div>
    </div>

    <hr>

    <nav id="gnb">
        <h2>메인메뉴</h2>
        <ul id="gnb_1dul">
            <li class="gnb_empty">메뉴는 표시하지 않습니다.</li>
        </ul>
    </nav>
</div>
<!-- } 상단 끝 -->

<hr>

<!-- 콘텐츠 시작 { -->
<div id="wrapper">
    <div id="aside">
        <?php echo outlogin('basic'); // 외부 로그인  ?>
    </div>
    <div id="container">
        <?php if ((!$bo_table || $w == 's' ) && !defined("_INDEX_")) { ?><div id="container_title"><?php echo $g5['title'] ?></div><?php } ?>

        <div id="g4_import_run">
            <ol>
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

        echo '<li>group table 복사</li>'.PHP_EOL;
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

                echo '<li>'.str_replace(G5_TABLE_PREFIX.'write_', '', $create_table).' 게시글 복사</li>';
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

    </div>
</div>

<!-- } 콘텐츠 끝 -->

<hr>

<!-- 하단 시작 { -->
<div id="ft">
    <div id="ft_catch"><img src="<?php echo G5_IMG_URL; ?>/ft.png" alt="<?php echo G5_VERSION ?>"></div>
    <div id="ft_copy">
        <p>
            Copyright &copy; <b>소유하신 도메인.</b> All rights reserved.<br>
            <a href="#">상단으로</a>
        </p>
    </div>
</div>

<script>
$(function() {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>