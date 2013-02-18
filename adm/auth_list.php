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
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌 : 지운아빠 2012-10-31
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';

$g4['title'] = "관리권한설정";
include_once('./admin.head.php');

$colspan = 5;
?>

<form id="fsearch" name="fsearch" method="get">
<input type="hidden" id="sfl" name="sfl" value="a.mb_id">
<fieldset>
    <legend>관리권한 검색</legend>
    <span>
        <?=$listall?>
        설정된 관리권한 <?=number_format($total_count)?>건
    </span>
    <input type="text" id="stx" name="stx" class="required frm_input" required value="<?=$stx?>" title="회원아이디(필수)">
    <input type="submit" id="fsearch_submit" class="btn_submit" value="검색">
</fieldset>
</form>

<section class="cbox">
    <h2>설정된 관리권한 내역</h2>
    <p>권한 <strong>r</strong>은 읽기권한, <strong>w</strong>는 쓰기권한, <strong>d</strong>는 삭제권한입니다.</p>

    <form id="fauthlist" name="fauthlist" method="post" action="./auth_list_delete.php">
    <input type="hidden" name="sst" value="<?=$sst?>">
    <input type="hidden" name="sod" value="<?=$sod?>">
    <input type="hidden" name="sfl" value="<?=$sfl?>">
    <input type="hidden" name="stx" value="<?=$stx?>">
    <input type="hidden" name="page" value="<?=$page?>">
    <input type="hidden" name="token" value="<?=$token?>">
    <table>
    <thead>
    <tr>
        <th scope="col"><input type="checkbox" id="chkall" name="chkall" value="1" title="현재 페이지 권한설정 내역 전체선택" onclick="check_all(this.form)"></th>
        <th scope="col"><?=subject_sort_link('a.mb_id')?>회원아이디</a></th>
        <th scope="col"><?=subject_sort_link('mb_nick')?>별명</a></th>
        <th scope="col">메뉴</th>
        <th scope="col">권한</th>
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
            <td class="td_chk">
                <input type="hidden" name="au_menu[<?=$i?>]" value="<?=$row['au_menu']?>">
                <input type="hidden" name="mb_id[<?=$i?>]" value="<?=$row['mb_id']?>">
                <input type="checkbox" id="chk_<?=$i?>" name="chk[]" value="<?=$i?>" title="<?=$row['mb_nick']?>님의 권한체크">
            </td>
            <td class="td_mbid"><a href="?sfl=a.mb_id&amp;stx=<?=$row['mb_id']?>"><?=$row['mb_id']?></a></td>
            <td class="td_auth_mbnick"><?=$mb_nick?></td>
            <td class="td_menu">
                <?=$row['au_menu']?>
                <?=$auth_menu[$row['au_menu']]?>
            </td>
            <td class="td_auth"><?=$row['au_auth']?></td>
        </tr>
        <?
    }

    if ($i==0)
        echo '<tr><td colspan="'.$colspan.'" class="empty_table">자료가 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <button onclick="btn_check(this.form, 'delete')">선택삭제</button>
    </div>

    <?
    $pagelist = get_paging($config['cf_write_pages'], $page, $total_page, $_SERVER['PHP_SELF'].'?'.$qstr.'&amp;page=');
    echo $pagelist;
    ?>

    <?
    //if (isset($stx))
    //    echo '<script>document.fsearch.sfl.value = "'.$sfl.'";</script>'.PHP_EOL;

    if (strstr($sfl, 'mb_id'))
        $mb_id = $stx;
    else
        $mb_id = '';
    ?>
    </form>
</section>

<form id="fauthlist2" name="fauthlist2" method="post" action="./auth_update.php" autocomplete="off">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">

<section id="add_admin" class="cbox">
    <h2>관리권한 추가</h2>
    <p>다음 양식에서 회원에게 관리권한을 부여하실 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">회원아이디<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="mb_id" name="mb_id" class="required frm_input" required value="<?=$mb_id?>" title="회원아이디"></td>
    </tr>
    <tr>
        <th scope="row"><label for="au_menu">접근가능메뉴<strong class="sound_only">필수</strong></label></th>
        <td>
            <select id="au_menu" name="au_menu" required title="접근가능메뉴">
                <option value=''>선택하세요
                <?
                foreach($auth_menu as $key=>$value)
                {
                    if (!(substr($key, -3) == '000' || $key == '-' || !$key))
                        echo '<option value="'.$key.'">'.$key.' '.$value;
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">권한지정</th>
        <td>
            <input type="checkbox" id="r" name="r" value="r" checked>
            <label for="r">r (읽기)</label>
            <input type="checkbox" id="w" name="w" value="w">
            <label for="w">w (쓰기)</label>
            <input type="checkbox" id="d" name="d" value="d">
            <label for="d">d (삭제)</label>
        </td>
    </tr>
    </tbody>
    </table>

    <fieldset id="admin_confirm">
        <legend>XSS 혹은 CSRF 방지</legend>
        <p>관리자 권한을 탈취당하는 경우를 대비하여 패스워드를 다시 한번 확인합니다.</p>
        <label for="admin_password">관리자 패스워드</label>
        <input type="password" id="admin_password" name="admin_password" class="required frm_input" required>
    </fieldset>

    <div class="btn_confirm">
        <input type="submit" class="btn_submit" value="완료">
    </div>
</section>

</form>

<script>
$(function() {
    $('#fauthlist').submit(function() {
        if (!is_checked("chk[]")) {
            alert("선택삭제 하실 항목을 하나 이상 선택하세요.");
            return false;
        }

        return true;
    });
});
</script>

<?
include_once ('./admin.tail.php');
?>
