<?php
$sub_menu = '400200';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = '분류관리';
include_once (G4_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";
if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if ($save_stx != $stx)
        $page = 1;
}

$sql_common = " from {$g4['shop_category_table']} ";
if ($is_admin != 'super')
    $sql_common .= " $where ca_mb_id = '{$member['mb_id']}' ";
$sql_common .= $sql_search;


// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page == "") { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "ca_id";
    $sod = "asc";
}
$sql_order = "order by $sst $sod";

// 출력할 레코드를 얻음
$sql  = " select *
             $sql_common
             $sql_order
             limit $from_record, $rows ";
$result = sql_query($sql);

//$qstr = 'page='.$page.'&amp;sort1='.$sort1.'&amp;sort2='.$sort2;
$qstr = $qstr.'&amp;sca='.$sca.'&amp;page='.$page.'&amp;save_stx='.$stx;

$listall = '';
if ($sfl || $stx) // 검색렬일 때만 처음 버튼을 보여줌
    $listall = '<a href="'.$_SERVER['PHP_SELF'].'">전체목록</a>';
?>

<form name="flist">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<fieldset>
    <legend>분류 검색</legend>
    <span>
        <?php echo $listall; ?>
        생성된 분류 수 <?php echo number_format($total_count); ?>개
    </span>

    <label for="sfl" class="sound_only">검색대상</label>
    <select name="sfl" id="sfl">
        <option value="ca_name"<?php echo get_selected($_GET['sfl'], "ca_name", true); ?>>분류명</option>
        <option value="ca_id"<?php echo get_selected($_GET['sfl'], "ca_id", true); ?>>분류코드</option>
        <option value="ca_mb_id"<?php echo get_selected($_GET['sfl'], "ca_mb_id", true); ?>>회원아이디</option>
    </select>

    <label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
    <input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="required frm_input">
    <input type="submit" value="검색" class="btn_submit">
</fieldset>

</form>

<section class="cbox">
    <h2>생성된 분류 전체 목록</h2>
    <p>생성된 분류 확인, 추가 및 간략 수정을 할 수 있습니다.</p>

    <?php if ($is_admin == 'super') {?>
    <div class="btn_add sort_with">
        <a href="./categoryform.php" id="cate_add">분류 추가</a>
    </div>
    <?php } ?>

    <ul class="sort_odr">
        <li><?php echo subject_sort_link("ca_id"); ?>분류코드<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link("ca_name"); ?>분류명<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link("ca_mb_id"); ?>회원아이디<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link("ca_use"); ?>판매가능<span class="sound_only"> 순 정렬</span></a></li>
        <li><?php echo subject_sort_link("ca_stock_qty"); ?>기본재고<span class="sound_only"> 순 정렬</span></a></li>
    </ul>

    <form name="fcategorylist" method="post" action="./categorylistupdate.php" autocomplete="off">
    <input type="hidden" name="page"  value="<?php echo $page; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">

    <table class="frm_basic">
    <thead>
    <tr>
        <th scope="col" rowspan="2">분류<br>코드</th>
        <th scope="col" rowspan="2">분류명</th>
        <th scope="col">출력스킨</th>
        <th scope="col">출력이미지폭<br>(픽셀)</th>
        <th scope="col">출력이미지높이<br>(픽셀)</th>
        <th scope="col">1줄당<br>이미지 수</th>
        <th scope="col">이미지<br>줄 수</th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
        <th scope="col">회원아이디</th>
        <th scope="col">본인인증</th>
        <th scope="col">성인인증</th>
        <th scope="col">판매가능</th>
        <th scope="col">상품수</th>
    </tr>
    </thead>
    <tbody>
    <?php
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $level = strlen($row['ca_id']) / 2 - 1;
        $p_ca_name = '';

        if ($level > 0) {
            $class = 'class="cate_list_lbl"'; // 2단 이상 분류의 label 에 스타일 부여 - 지운아빠 2013-04-02
            // 상위단계의 분류명
            $p_ca_id = substr($row['ca_id'], 0, $level*2);
            $sql = " select ca_name from {$g4['shop_category_table']} where ca_id = '$p_ca_id' ";
            $temp = sql_fetch($sql);
            $p_ca_name = $temp['ca_name'].'의하위';
        } else {
            $class = '';
        }

        $s_level = '<label for="ca_name_'.$i.'" '.$class.'><span class="sound_only">'.$p_ca_name.''.($level+1).'단 분류</span></label>';
        $s_level_input_size = 25 - $level *2; // 하위 분류일 수록 입력칸 넓이 작아짐 - 지운아빠 2013-04-02

        if ($level+2 < 6) $s_add = '<a href="./categoryform.php?ca_id='.$row['ca_id'].'&amp;'.$qstr.'">추가</a> '; // 분류는 5단계까지만 가능
        else $s_add = '';
        $s_upd = '<a href="./categoryform.php?w=u&amp;ca_id='.$row['ca_id'].'&amp;'.$qstr.'"><span class="sound_only">'.get_text($row['ca_name']).' </span>수정</a> ';

        if ($is_admin == 'super')
            $s_del = '<a href="./categoryformupdate.php?w=d&amp;ca_id='.$row['ca_id'].'&amp;'.$qstr.'" onclick="return delete_confirm();"><span class="sound_only">'.get_text($row['ca_name']).' </span>삭제</a> ';

        // 해당 분류에 속한 상품의 갯수
        $sql1 = " select COUNT(*) as cnt from {$g4['shop_item_table']}
                      where ca_id = '{$row['ca_id']}'
                      or ca_id2 = '{$row['ca_id']}'
                      or ca_id3 = '{$row['ca_id']}' ";
        $row1 = sql_fetch($sql1);
    ?>
    <tr>
        <td class="td_code" rowspan="2">
            <input type="hidden" name="ca_id[<?php echo $i; ?>]" value="<?php echo $row['ca_id']; ?>">
            <a href="<?php echo G4_SHOP_URL; ?>/list.php?ca_id=<?php echo $row['ca_id']; ?>"><?php echo $row['ca_id']; ?></a>
        </td>
        <td class="td_scate" rowspan="2"><?php echo $s_level; ?> <input type="text" name="ca_name[<?php echo $i; ?>]" value="<?php echo get_text($row['ca_name']); ?>" id="ca_name_<?php echo $i; ?>" required class="frm_input required" size="<?php echo $s_level_input_size; ?>"></td>
        <td class="td_scate_admin">
            <label for="ca_skin<?php echo $i; ?>" class="sound_only">출력스킨</label>
            <select id="ca_skin<?php echo $i; ?>" name="ca_skin[<?php echo $i; ?>]">
                <?php echo get_list_skin_options("^list.[^\.]+\.skin\.php", G4_SHOP_SKIN_PATH, $row['ca_skin']); ?>
            </select>
        </td>
        <td class="td_output">
            <label for="ca_out_width<?php echo $i; ?>" class="sound_only">출력이미지 폭</label>
            <input type="text" name="ca_img_width[<?php echo $i; ?>]" value="<?php echo get_text($row['ca_img_width']); ?>" id="ca_out_width<?php echo $i; ?>" required class="required frm_input" size="3" > <span class="sound_only">픽셀</span>
        </td>
        <td class="td_output">
            <label for="ca_img_height<?php echo $i; ?>" class="sound_only">출력이미지 높이</label>
            <input type="text" name="ca_img_height[<?php echo $i; ?>]" value="<?php echo $row['ca_img_height']; ?>" id="ca_img_height<?php echo $i; ?>" required class="required frm_input" size="3" > <span class="sound_only">픽셀</span>
        </td>
        <td class="td_imgline">
            <label for="ca_lineimg_num<?php echo $i; ?>" class="sound_only">1줄당 이미지 수</label>
            <input type="text" name="ca_list_mod[<?php echo $i; ?>]" size="3" value="<?php echo $row['ca_list_mod']; ?>" id="ca_lineimg_num<?php echo $i; ?>" required class="required frm_input"> <span class="sound_only">개</span>
        </td>
        <td class="td_imgline">
            <label for="ca_imgline_num<?php echo $i; ?>" class="sound_only">이미지 줄 수</label>
            <input type="text" name="ca_list_row[<?php echo $i; ?>]" value='<?php echo $row['ca_list_row']; ?>' id="ca_imgline_num<?php echo $i; ?>" required class="required frm_input" size="3"> <span class="sound_only">줄</span>
        </td>
        <td class="td_mng" rowspan="2">
            <?php echo $s_add; ?>
            <?php echo $s_vie; ?>
            <?php echo $s_upd; ?>
            <?php echo $s_del; ?>
        </td>
    </tr>
    <tr>
        <td class="td_scate_admin">
            <?php if ($is_admin == 'super') {?>
            <label for="ca_mb_id<?php echo $i; ?>" class="sound_only">회원아이디</label>
            <input type="text" name="ca_mb_id[<?php echo $i; ?>]" value="<?php echo $row['ca_mb_id']; ?>" id="ca_mb_id<?php echo $i; ?>" class="frm_input" size="15" maxlength="20">
            <?php } else { ?>
            <input type="hidden" name="ca_mb_id[<?php echo $i; ?>]" value="<?php echo $row['ca_mb_id']; ?>">
            <?php echo $row['ca_mb_id']; ?>
            <?php } ?>
        </td>
        <td class="td_confirm">
            <input type="checkbox" name="ca_hp_cert_use[<?php echo $i; ?>]" value="1" id="ca_hp_cert_use_yes<?php echo $i; ?>" <?php if($row['ca_hp_cert_use']) echo 'checked="checked"'; ?>>
            <label for="ca_hp_cert_use_yes<?php echo $i; ?>">사용</label>
            
            <!-- <input type="radio" name="ca_hp_cert_use[<?php echo $i; ?>]" value="1" id="ca_hp_cert_use_yes<?php echo $i; ?>" <?php if($row['ca_hp_cert_use']) echo 'checked="checked"'; ?>>
            <label for="ca_hp_cert_use_yes<?php echo $i; ?>">사용함</label>
            <input type="radio" name="ca_hp_cert_use[<?php echo $i; ?>]" value="0" id="ca_hp_cert_use_no<?php echo $i; ?>" <?php if(!$row['ca_hp_cert_use']) echo 'checked="checked"'; ?>>
            <label for="ca_hp_cert_use_no<?php echo $i; ?>">사용안함</label> -->
        </td>
        <td class="td_confirm">
            <input type="checkbox" name="ca_adult_cert_use[<?php echo $i; ?>]" value="1" id="ca_adult_cert_use_yes<?php echo $i; ?>" <?php if($row['ca_adult_cert_use']) echo 'checked="checked"'; ?>>
            <label for="ca_adult_cert_use_yes<?php echo $i; ?>">사용</label>
            
            <!-- <input type="radio" name="ca_adult_cert_use[<?php echo $i; ?>]" value="1" id="ca_adult_cert_use_yes<?php echo $i; ?>" <?php if($row['ca_adult_cert_use']) echo 'checked="checked"'; ?>>
            <label for="ca_adult_cert_use_yes<?php echo $i; ?>">사용함</label>
            <input type="radio" name="ca_adult_cert_use[<?php echo $i; ?>]" value="0" id="ca_adult_cert_use_no<?php echo $i; ?>" <?php if(!$row['ca_adult_cert_use']) echo 'checked="checked"'; ?>>
            <label for="ca_adult_cert_use_no<?php echo $i; ?>">사용안함</label> -->
        </td>
        <td class="td_possible">
            <input type="checkbox" name="ca_use[<?php echo $i; ?>]" value="1" id="ca_use<?php echo $i; ?>" <?php echo ($row['ca_use'] ? "checked" : ""); ?>>
            <label for="ca_use<?php echo $i; ?>">판매</label>
        </td>
        <td class="td_amount"><a href="./itemlist.php?sca=<?php echo $row['ca_id']; ?>"><?php echo $row1['cnt']; ?></a></td>
    </tr>
    <?php }
    if ($i == 0) echo "<tr><td colspan=\"7\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>

    <div class="btn_list">
        <input type="submit" value="일괄수정">
    </div>

    </form>

    <?php echo get_paging(G4_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['PHP_SELF']}?$qstr&amp;page="); ?>

</section>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>
