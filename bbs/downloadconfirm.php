<?php
include_once('./_common.php');
include_once($g4['path'].'/head.sub.php');

$no = (int)$no;

// 쿠키에 저장된 ID값과 넘어온 ID값을 비교하여 같지 않을 경우 오류 발생
// 다른곳에서 링크 거는것을 방지하기 위한 코드
if (!get_session('ss_view_'.$bo_table.'_'.$wr_id))
    alert('잘못된 접근입니다.');

$sql = " select bf_source from {$g4['board_file_table']} where bo_table = '$bo_table' and wr_id = '$wr_id' and bf_no = '$no' ";
$file = sql_fetch($sql);

$board_href = 'bo_table='.$bo_table;
if($wr_id)
    $board_href .= '&amp;wr_id='.$wr_id;

if($qstr)
    $board_href .= $qstr;

if($board['bo_download_point'] < 0) {
    echo '
        <article>
        <header>
            <hgroup>
                <h1>파일다운로드</h1>
                <h2>아래 내용을 확인해 주세요.</h2>
            </hgroup>
        </header>
        <p>
            '.$file['bf_source'].' 파일을 다운로드 하시면 포인트가 차감('.number_format($board['bo_download_point']).'점)됩니다.<br />
            포인트는 게시물당 한번만 차감되며 다음에 다시 다운로드 하셔도 중복하여 차감하지 않습니다.<br />
            그래도 다운로드 하시겠습니까?
        </p>
        <a href="./download.php?'.$_SERVER['QUERY_STRING'].'">파일다운로드</a>
        <a href="./board.php?'.$board_href.'">다운로드안함</a>
    ';
} else {
    goto_url('./download.php?'.$_SERVER['QUERY_STRING']);
}

include_once($g4['path'].'/tail.sub.php');
?>