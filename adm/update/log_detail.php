<?php
$sub_menu = '100600';
include_once('./_common.php');

$g5['title'] = '로그 기록 상세보기';
include_once ('../admin.head.php');

$filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : null;
if($filename == null) alert("파일이름이 존재하지 않습니다.");

$log_dir = G5_DATA_PATH."/update/log";

if(!is_dir($log_dir)) die("로그 디렉토리가 존재하지 않습니다.");

$log_detail = $g5['update']->getLogDetail($filename);
if($log_detail == false) alert('파일의 내용을 읽어올 수 없습니다.');
?>
<div>
    <table>
        <colgroup>
            <col width="100px;">
            <col>
            <col width="100px;">
            <col>
        </colgroup>
        <tbody>
            <tr>
                <th>파일명</th>
                <td colspan="3"><?php echo $filename; ?></td>
            <tr>
            <tr>
                <th>타입</th>
                <td><?php echo $log_detail['status']; ?></td>
                <th>생성날짜</th>
                <td><?php echo $log_detail['datetime']; ?></td>
            </tr>
            <tr>
                <th>내용</th>
                <td colspan="3">
                    <p><?php echo nl2br($log_detail['content']); ?></p>
                </td>
            </tr>
        </tbody>

    </table>
    <?php echo htmlspecialchars_decode($content); ?>
</div>

<?php
    include_once('../admin.tail.php');
?>