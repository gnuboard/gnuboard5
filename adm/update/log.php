<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '로그 기록';
include_once ('../admin.head.php');

$log_dir = G5_DATA_PATH."/update/log";

if(!is_dir($log_dir)) die("로그 디렉토리가 존재하지 않습니다.");

// echo $g5['update']->getLogTotalCount();
// exit;

$page = $_REQUEST['page'];

$list = $g5['update']->getLogList();
?>
<ul class="anchor"><li><a href="./">업데이트</a></li><li><a href="./rollback.php">복원</a></li><li><a href="./log.php">로그</a></li></ul>
<div>
    <table>
        <thead>
            <tr>
                <th>파일명</th>
                <th>상태</th>
                <th>날짜</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($list as $key => $var) { ?>
                <tr>
                    <td><a href="./log_detail.php?filename=<?php echo $var['filename']; ?>"><?php echo $var['filename']; ?></a></td>
                    <td><a><?php echo $var['status']; ?></a></td>
                    <td><a><?php echo $var['datetime']; ?></a></td>
                </tr>
            <?php } ?>
        </tbody>

    </table>

    <?php
    $pagelist = get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, $_SERVER['SCRIPT_NAME'].'?'.$qstr.'&amp;page=');
    echo $pagelist;
    ?>

</div>

<?php
include_once('../admin.tail.php');