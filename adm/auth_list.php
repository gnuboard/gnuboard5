<?
$sub_menu = "100200";
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$token = get_token();

$sql_common = " from {$g4['auth_table']} a left join {$g4['member_table']} b on (a.mb_id=b.mb_id) ";

$sql_search = " where (1) ";
if ($stx) {
    $sql_search .= " and ( ";
    switch ($sfl) {
        default :
            $sql_search .= " ({$sfl} like '%{$stx}%') ";
            break;
    }
    $sql_search .= " ) ";
}

if (!$sst) {
    $sst  = "a.mb_id, au_menu";
    $sod = "";
}
$sql_order = " order by $sst $sod ";

$sql = " select count(*) as cnt
            {$sql_common}
            {$sql_search}
            {$sql_order} ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") $page = 1; // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

$sql = " select *
            {$sql_common}
            {$sql_search}
            {$sql_order}
            limit {$from_record}, {$rows} ";
$result = sql_query($sql);

$listall = '';
if ($sfl || $stx || $sod) // 검색 혹은 정렬일 때만 처음 버튼을 보여줌 : 지운아빠 2012-10-31
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">처음으로</a>';

$g4['title'] = "관리권한설정";
include_once('./admin.head.php');

$colspan = 5;
?>

<script src="<?=$g4['path']?>/js/sideview.js"></script>
<script>
var list_update_php = '';
var list_delete_php = 'auth_list_delete.php';
</script>

<form id="fsearch" name="fsearch" method="get">
<fieldset>
    <legend>관리권한 검색</legend>
    <div>
        <span><?=$listall?></span>
        설정된 관리권한 <?=number_format($total_count)?>건
    </div>
    <input type="hidden" id="sfl" name="sfl" value="a.mb_id">
    <label for="stx">회원아이디</label>
    <input type="text" id="stx" name="stx" required value="<?=$stx?>">
    <input type="submit" id="fsearch_submit" value="검색">
</fieldset>
</form>

<form id="fauthlist" name="fauthlist" method="post">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">
<table>
<caption>
관리권한 현황
<p>
    여러개의 권한설정을 한번에 삭제하실 때는 권한 체크기능을 이용하세요.
</p>
</caption>
<thead>
<tr>
    <th scope="col" id="th_mb_id"><?=subject_sort_link('a.mb_id')?>회원아이디</a></th>
    <th scope="col" id="th_mb_nick"><?=subject_sort_link('mb_nick')?>별명</a></th>
    <th scope="col" id="th_au_menu">메뉴</th>
    <th scope="col" id="th_au_auth">권한</th>
    <th scope="col" id="th_chkall"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재목록 전체선택" onclick="check_all(this.form)"></th>
</tr>
</thead>
<tbody>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $mb_nick = get_sideview($row['mb_id'], $row['mb_nick'], $row['mb_email'], $row['mb_homepage']);

    // 메뉴번호가 바뀌는 경우에 현재 없는 저장된 메뉴는 삭제함
    if (!isset($auth_menu[$row['au_menu']]))
    {
        sql_query(" delete from {$g4['auth_table']} where au_menu = '{$row['au_menu']}' ");
        continue;
    }

    $list = $i%2;
    ?>
    <tr>
        <td headers="th_mb_id"><a href="?sfl=a.mb_id&amp;stx=<?=$row['mb_id']?>"><?=$row['mb_id']?></a></td>
        <td headers="th_mb_nick">
            <input type="hidden" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>">
            <?=$mb_nick?>
        </td>
        <td headers="th_au_menu">
            <input type="hidden" name="au_menu[<?=$i?>]" value="<?=$row['au_menu']?>">
            <?=$row['au_menu']?>
            <?=$auth_menu[$row['au_menu']]?>
        </td>
        <td headers="th_au_auth"><?=$row['au_auth']?></td>
        <td headers="th_chkall">
            <input type="checkbox" id="chk" name="chk[]" value="<?=$i?>" title="권한체크">
        </td>
    </tr>
    <?
}

if ($i==0)
    echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
?>
</tbody>
</table>

<?
$pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
?>

<div class="btn_list"><input type="button" value="선택삭제" onclick="btn_check(this.form, 'delete')"></div>

<div class="paginate">
    <?=$pagelist?>
</div>

<?
if ($stx)
    echo '<script>document.fsearch.sfl.value = "'.$sfl.'";</script>'.PHP_EOL;

if (strstr($sfl, 'mb_id'))
    $mb_id = $stx;
else
    $mb_id = '';
?>
</form>

<form id="fauthlist2" name="fauthlist2" method="post" onsubmit="return fauthlist2_submit(this);" autocomplete="off">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">

<fieldset>
    <legend>관리권한 추가</legend>
    <p>아래 양식에서 회원에게 관리권한을 부여하실 수 있습니다. <strong>r</strong>은 <strong>읽기</strong>권한, <strong>w</strong>는 <strong>입력</strong> 혹은 <strong>수정</strong>권한, <strong>d</strong>는 <strong>삭제</strong>권한입니다.</p>
    <div>
        <label for="mb_id">회원아이디</label>
        <input type="text" id="mb_id" name="mb_id" required value='<?=$mb_id?>'>
    </div>
    <div>
        <label for="au_menu">접근가능메뉴</label>
        <select id="au_menu" name="au_menu" required>
            <option value=''>선택하세요
            <?
            foreach($auth_menu as $key=>$value)
            {
                if (!(substr($key, -3) == '000' || $key == '-' || !$key))
                    echo '<option value="'.$key.'">'.$key.' '.$value;
            }
            ?>
        </select>
        <input type="checkbox" id="r" name="r" value="r" checked>
        <label for="r">r</label>
        <input type="checkbox" id="w" name="w" value="w">
        <label for="w">w</label>
        <input type="checkbox" id="d" name="d" value="d">
        <label for="d">d</label>
    </div>
    <div>
        <label for="admin_password">관리자 패스워드</label>
        <input type="password" id="admin_password" name="admin_password" required>
    </div>
</fieldset>
<div class="btn_confirm">
    <input type="submit" value="추가">
</div>
</form>

<script>
function fauthlist2_submit(f)
{
    f.action = "./auth_update.php";
    return true;
}
</script>

<?
include_once ('./admin.tail.php');
?>
