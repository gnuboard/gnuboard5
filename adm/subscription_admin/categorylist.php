<?php
$sub_menu = '600200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$g5['title'] = '분류관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$where = " where ";
$sql_search = "";

$sfl = in_array($sfl, array('sc_name', 'sc_id', 'sc_mb_id')) ? $sfl : '';

if ($stx != "") {
    if ($sfl != "") {
        $sql_search .= " $where $sfl like '%$stx%' ";
        $where = " and ";
    }
    if (isset($save_stx) && $save_stx && ($save_stx != $stx))
        $page = 1;
}

$sql_common = " from {$g5['g5_subscription_category_table']} ";
if ($is_admin != 'super')
    $sql_search .= " $where sc_mb_id = '{$member['mb_id']}' ";
$sql_common .= $sql_search;


// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함

if (!$sst)
{
    $sst  = "sc_id";
    $sod = "asc";
}
$sql_order = "order by $sst $sod";

// 출력할 레코드를 얻음
$sql  = " select *
             $sql_common
             $sql_order
             limit $from_record, $rows ";
$result = sql_query($sql);

$listall = '<a href="'.$_SERVER['SCRIPT_NAME'].'" class="ov_listall">전체목록</a>';
?>

<div class="local_ov01 local_ov">
    <?php echo $listall; ?>
    <span class="btn_ov01"><span class="ov_txt">생성된  분류 수</span><span class="ov_num">  <?php echo number_format($total_count); ?>개</span></span>
</div>

<form name="flist" class="local_sch01 local_sch">
<input type="hidden" name="page" value="<?php echo $page; ?>">
<input type="hidden" name="save_stx" value="<?php echo $stx; ?>">

<label for="sfl" class="sound_only">검색대상</label>
<select name="sfl" id="sfl">
    <option value="sc_name"<?php echo get_selected($sfl, "sc_name", true); ?>>분류명</option>
    <option value="sc_id"<?php echo get_selected($sfl, "sc_id", true); ?>>분류코드</option>
    <option value="sc_mb_id"<?php echo get_selected($sfl, "sc_mb_id", true); ?>>회원아이디</option>
</select>

<label for="stx" class="sound_only">검색어<strong class="sound_only"> 필수</strong></label>
<input type="text" name="stx" value="<?php echo $stx; ?>" id="stx" required class="required frm_input">
<input type="submit" value="검색" class="btn_submit">

</form>

<form name="fcategorylist" method="post" action="./categorylistupdate.php" autocomplete="off">
<input type="hidden" name="sst" value="<?php echo $sst; ?>">
<input type="hidden" name="sod" value="<?php echo $sod; ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl; ?>">
<input type="hidden" name="stx" value="<?php echo $stx; ?>">
<input type="hidden" name="page" value="<?php echo $page; ?>">

<div id="sct" class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="2"><?php echo subject_sort_link("sc_id"); ?>분류코드</a></th>
        <th scope="col" id="sct_cate"><?php echo subject_sort_link("sc_name"); ?>분류명</a></th>
        <th scope="col" id="sct_amount">상품수</th>
        <th scope="col" id="sct_hpcert">본인인증</th>
        <th scope="col" id="sct_imgw">이미지 폭</th>
        <th scope="col" id="sct_imgcol">1행이미지수</th>
        <th scope="col" id="sct_mobileimg">모바일<br>1행이미지수</th>
        <th scope="col" id="sct_pcskin">PC스킨지정</th>
        <th scope="col" rowspan="2">관리</th>
    </tr>
    <tr>
        <th scope="col" id="sct_admin"><?php echo subject_sort_link("sc_mb_id"); ?>관리회원아이디</a></th>
        <th scope="col" id="sct_sell"><?php echo subject_sort_link("sc_use"); ?>판매가능</a></th>
        <th scope="col" id="sct_adultcert">성인인증</th>
        <th scope="col" id="sct_imgh">이미지 높이</th>
        <th scope="col" id="sct_imgrow">이미지 행수</th>
        <th scope="col" id="sct_mobilerow">모바일<br>이미지 행수</th>
        <th scope="col" id="sct_mskin">모바일스킨지정</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $s_add = $s_vie = $s_upd = $s_del = '';
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        $level = strlen($row['sc_id']) / 2 - 1;
        $p_sc_name = '';

        if ($level > 0) {
            $class = 'class="name_lbl"'; // 2단 이상 분류의 label 에 스타일 부여 - 지운아빠 2013-04-02
            // 상위단계의 분류명
            $p_sc_id = substr($row['sc_id'], 0, $level*2);
            $sql = " select sc_name from {$g5['g5_subscription_category_table']} where sc_id = '$p_sc_id' ";
            $temp = sql_fetch($sql);
            $p_sc_name = $temp['sc_name'].'의하위';
        } else {
            $class = '';
        }

        $s_level = '<div><label for="sc_name_'.$i.'" '.$class.'><span class="sound_only">'.$p_sc_name.''.($level+1).'단 분류</span></label></div>';
        $s_level_input_size = 25 - $level *2; // 하위 분류일 수록 입력칸 넓이 작아짐 - 지운아빠 2013-04-02

        if ($level+2 < 6) $s_add = '<a href="./categoryform.php?sc_id='.$row['sc_id'].'&amp;'.$qstr.'" class="btn btn_03">추가</a> '; // 분류는 5단계까지만 가능
        else $s_add = '';
        $s_upd = '<a href="./categoryform.php?w=u&amp;sc_id='.$row['sc_id'].'&amp;'.$qstr.'" class="btn btn_02"><span class="sound_only">'.get_text($row['sc_name']).' </span>수정</a> ';

        if ($is_admin == 'super')
            $s_del = '<a href="./categoryformupdate.php?w=d&amp;sc_id='.$row['sc_id'].'&amp;'.$qstr.'" onclick="return delete_confirm(this);" class="btn btn_02"><span class="sound_only">'.get_text($row['sc_name']).' </span>삭제</a> ';

        // 해당 분류에 속한 상품의 수
        $sql1 = " select COUNT(*) as cnt from {$g5['g5_subscription_item_table']}
                      where sc_id = '{$row['sc_id']}'
                      or sc_id2 = '{$row['sc_id']}'
                      or sc_id3 = '{$row['sc_id']}' ";
        $row1 = sql_fetch($sql1);

        // 스킨 Path
        if(!$row['sc_skin_dir'])
            $g5_subscription_skin_path = G5_SUBSCRIPTION_SKIN_PATH;
        else {
            if(preg_match('#^theme/(.+)$#', $row['sc_skin_dir'], $match))
                $g5_subscription_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1];
            else
                $g5_subscription_skin_path  = G5_PATH.'/'.G5_SKIN_DIR.'/shop/'.$row['sc_skin_dir'];
        }

        if(!$row['sc_mobile_skin_dir'])
            $g5_msubscription_skin_path = G5_MSUBSCRIPTION_SKIN_PATH;
        else {
            if(preg_match('#^theme/(.+)$#', $row['sc_mobile_skin_dir'], $match))
                $g5_msubscription_skin_path = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1];
            else
                $g5_msubscription_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$row['sc_mobile_skin_dir'];
        }

        $bg = 'bg'.($i%2);
    ?>
    <tr class="<?php echo $bg; ?>">
        <td class="td_code" rowspan="2">
            <input type="hidden" name="sc_id[<?php echo $i; ?>]" value="<?php echo $row['sc_id']; ?>">
            <a href="<?php echo subscription_category_url($row['sc_id']); ?>"><?php echo $row['sc_id']; ?></a>
        </td>
        <td headers="sct_cate" class="sct_name<?php echo $level; ?>"><?php echo $s_level; ?> <input type="text" name="sc_name[<?php echo $i; ?>]" value="<?php echo get_text($row['sc_name']); ?>" id="sc_name_<?php echo $i; ?>" required class="tbl_input full_input required"></td>
        <td headers="sct_amount" class="td_amount"><a href="./itemlist.php?sca=<?php echo $row['sc_id']; ?>"><?php echo $row1['cnt']; ?></a></td>
        <td headers="sct_hpcert" class="td_possible">
            <input type="checkbox" name="sc_cert_use[<?php echo $i; ?>]" value="1" id="sc_cert_use_yes<?php echo $i; ?>" <?php if($row['sc_cert_use']) echo 'checked="checked"'; ?>>
            <label for="sc_cert_use_yes<?php echo $i; ?>">사용</label>
        </td>
        <td headers="sct_imgw">
            <label for="sc_out_width<?php echo $i; ?>" class="sound_only">출력이미지 폭</label>
            <input type="text" name="sc_img_width[<?php echo $i; ?>]" value="<?php echo get_text($row['sc_img_width']); ?>" id="sc_out_width<?php echo $i; ?>" required class="required tbl_input" size="3" > <span class="sound_only">픽셀</span>
        </td>
        
        <td headers="sct_imgcol">
            <label for="sc_lineimg_num<?php echo $i; ?>" class="sound_only">1줄당 이미지 수</label>
            <input type="text" name="sc_list_mod[<?php echo $i; ?>]" size="3" value="<?php echo $row['sc_list_mod']; ?>" id="sc_lineimg_num<?php echo $i; ?>" required class="required tbl_input"> <span class="sound_only">개</span>
        </td>
        <td headers="sct_mobileimg">
            <label for="sc_mobileimg_num<?php echo $i; ?>" class="sound_only">모바일 1줄당 이미지 수</label>
            <input type="text" name="sc_mobile_list_mod[<?php echo $i; ?>]" size="3" value="<?php echo $row['sc_mobile_list_mod']; ?>" id="sc_mobileimg_num<?php echo $i; ?>" required class="required tbl_input"> <span class="sound_only">개</span>
        </td>
        <td headers="sct_pcskin" class="sct_pcskin">
            <label for="sc_skin_dir<?php echo $i; ?>" class="sound_only">PC스킨폴더</label>
            <?php echo get_skin_select('shop', 'sc_skin_dir'.$i, 'sc_skin_dir['.$i.']', $row['sc_skin_dir'], 'class="skin_dir"'); ?>
            <label for="sc_skin<?php echo $i; ?>" class="sound_only">PC스킨파일</label>
            <select id="sc_skin<?php echo $i; ?>" name="sc_skin[<?php echo $i; ?>]" required class="required">
                <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", $g5_subscription_skin_path, $row['sc_skin']); ?>
            </select>
        </td>
        <td class="td_mng td_mng_s" rowspan="2">
            <?php echo $s_add; ?>
            <?php echo $s_vie; ?>
            <?php echo $s_upd; ?>
            <?php echo $s_del; ?>
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>">
        <td headers="sct_admin">
            <?php if ($is_admin == 'super') {?>
            <label for="sc_mb_id<?php echo $i; ?>" class="sound_only">관리회원아이디</label>
            <input type="text" name="sc_mb_id[<?php echo $i; ?>]" value="<?php echo $row['sc_mb_id']; ?>" id="sc_mb_id<?php echo $i; ?>" class="tbl_input full_input" size="15" maxlength="20">
            <?php } else { ?>
            <input type="hidden" name="sc_mb_id[<?php echo $i; ?>]" value="<?php echo $row['sc_mb_id']; ?>">
            <?php echo $row['sc_mb_id']; ?>
            <?php } ?>
        </td>
        <td headers="sct_sell" class="td_possible">
            <input type="checkbox" name="sc_use[<?php echo $i; ?>]" value="1" id="sc_use<?php echo $i; ?>" <?php echo ($row['sc_use'] ? "checked" : ""); ?>>
            <label for="sc_use<?php echo $i; ?>">판매</label>
        </td>

        <td headers="sct_adultcert" class="td_possible">
            <input type="checkbox" name="sc_adult_use[<?php echo $i; ?>]" value="1" id="sc_adult_use_yes<?php echo $i; ?>" <?php if($row['sc_adult_use']) echo 'checked="checked"'; ?>>
            <label for="sc_adult_use_yes<?php echo $i; ?>">사용</label>
        </td>
        <td headers="sct_imgh">
            <label for="sc_img_height<?php echo $i; ?>" class="sound_only">출력이미지 높이</label>
            <input type="text" name="sc_img_height[<?php echo $i; ?>]" value="<?php echo $row['sc_img_height']; ?>" id="sc_img_height<?php echo $i; ?>" required class="required tbl_input" size="3" > <span class="sound_only">픽셀</span>
        </td>
        <td headers="sct_imgrow">
            <label for="sc_imgline_num<?php echo $i; ?>" class="sound_only">이미지 줄 수</label>
            <input type="text" name="sc_list_row[<?php echo $i; ?>]" value='<?php echo $row['sc_list_row']; ?>' id="sc_imgline_num<?php echo $i; ?>" required class="required tbl_input" size="3"> <span class="sound_only">줄</span>
        </td>
        <td headers="sct_mobilerow">
            <label for="sc_mobileimg_row<?php echo $i; ?>" class="sound_only">모바일 이미지 줄 수</label>
            <input type="text" name="sc_mobile_list_row[<?php echo $i; ?>]" value='<?php echo $row['sc_mobile_list_row']; ?>' id="sc_mobileimg_row<?php echo $i; ?>" required class="required tbl_input" size="3">
        </td>
        <td headers="sct_mskin"  class="sct_mskin">
            <label for="sc_mobile_skin_dir<?php echo $i; ?>" class="sound_only">모바일스킨폴더</label>
            <?php echo get_mobile_skin_select('shop', 'sc_mobile_skin_dir'.$i, 'sc_mobile_skin_dir['.$i.']', $row['sc_mobile_skin_dir'], 'class="skin_dir"'); ?>
            <label for="sc_mobile_skin<?php echo $i; ?>" class="sound_only">모바일스킨파일</label>
            <select id="sc_mobile_skin<?php echo $i; ?>" name="sc_mobile_skin[<?php echo $i; ?>]" required class="required">
                <?php echo get_list_skin_options("^list.[0-9]+\.skin\.php", $g5_msubscription_skin_path, $row['sc_mobile_skin']); ?>
            </select>
        </td>
    </tr>
    <?php }
    if ($i == 0) echo "<tr><td colspan=\"9\" class=\"empty_table\">자료가 한 건도 없습니다.</td></tr>\n";
    ?>
    </tbody>
    </table>
</div>

<div class="btn_fixed_top">
    <input type="submit" value="일괄수정" class="btn_02 btn">

    <?php if ($is_admin == 'super') {?>
    <a href="./categoryform.php" id="cate_add" class="btn btn_01">분류 추가</a>
    <?php } ?>
</div>

</form>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
$(function() {
    $("select.skin_dir").on("change", function() {
        var type = "";
        var dir = $(this).val();
        if(!dir)
            return false;

        var id = $(this).attr("id");
        var $sel = $(this).siblings("select");
        var sval = $sel.find("option:selected").val();

        if(id.search("mobile") > -1)
            type = "mobile";

        $sel.load(
            "./ajax.skinfile.php",
            { dir : dir, type : type, sval: sval }
        );
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');