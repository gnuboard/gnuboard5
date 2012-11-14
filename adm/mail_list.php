<?
$sub_menu = '200300';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$sql_common = " from {$g4['mail_table']} ";

// 테이블의 전체 레코드수만 얻음
$sql = " select COUNT(*) as cnt $sql_common ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$page = 1;

$sql = " select * $sql_common order by ma_id desc ";
$result = sql_query($sql);

$g4['title'] = '회원메일발송';
include_once('./admin.head.php');

$colspan = 6;
?>

<a href="./mail_form.php" id="mail_add">메일내용추가</a>

건수 : <?=$total_count ?>

<table>
<caption>
    등록된 메일내용 목록
    <p>
        <strong>테스트</strong>를 클릭하시면 등록된 최고관리자님에게 테스트메일 발송<br>
        <strong>보내기</strong>를 클릭하시면 해당 메일을 발송할 회원님 선택페이지로 이동
    </p>
</caption>
<thead>
<tr>
    <th scope="col">ID</th>
    <th scope="col">제목</th>
    <th scope="col">작성일시</th>
    <th scope="col">테스트</th>
    <th scope="col">보내기</th>
    <th scope="col">관리</th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++) {
    $s_mod = '<a href="./mail_form.php?w=u&ma_id='.$row['ma_id'].'">수정</a>';
    $s_del = '<a href="javascript:post_delete(\'mail_update.php\', '.$row['ma_id'].');">삭제</a>';
    $s_vie = '<a href="./mail_preview.php?ma_id='.$row['ma_id'].'" target="_blank">미리보기</a>';

    $num = number_format($total_count - ($page - 1) * $config['cf_page_rows'] - $i);
?>

<tr>
    <td><?=$num?></td>
    <td><?=$row['ma_subject']?></td>
    <td><?=$row['ma_time']?></td>
    <td><a href="./mail_test.php?ma_id=<?=$row[ma_id]?>">테스트</a></td>
    <td><a href="./mail_select_form.php?ma_id=<?=$row[ma_id]?>">보내기</a></td>
    <td><?=$s_mod?> <?=$s_del?> <?=$s_vie?></td>
</tr>

<?
}
if (!$i)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<script>
// POST 방식으로 삭제
function post_delete(action_url, val)
{
	var f = document.fpost;

	if(confirm('한번 삭제한 자료는 복구할 방법이 없습니다. 정말 삭제하시겠습니까?')) {
        f.ma_id.value = val;
		f.action = action_url;
		f.submit();
	}
}
</script>

<form id="fpost" id="fpost" name="fpost" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="w" value='d'>
<input type="hidden" name="ma_id">
</form>

<?
include_once ('./admin.tail.php');
?>