<?php
$sub_menu = '500500';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$bn_position = (isset($_GET['bn_position']) && in_array($_GET['bn_position'], array('메인', '왼쪽'))) ? $_GET['bn_position'] : '';
$bn_device = (isset($_GET['bn_device']) && in_array($_GET['bn_device'], array('pc', 'mobile'))) ? $_GET['bn_device'] : 'both';
$bn_time = (isset($_GET['bn_time']) && in_array($_GET['bn_time'], array('ing', 'end'))) ? $_GET['bn_time'] : '';

$where = ' where ';
$sql_search = '';

if ( $bn_position ){
    $sql_search .= " $where bn_position = '$bn_position' ";
    $where = ' and ';
    $qstr .= "&amp;bn_position=$bn_position";
}

if ( $bn_device && $bn_device !== 'both' ){
    $sql_search .= " $where bn_device = '$bn_device' ";
    $where = ' and ';
    $qstr .= "&amp;bn_device=$bn_device";
}

if ( $bn_time ){
    $sql_search .= ($bn_time === 'ing') ? " $where '".G5_TIME_YMDHIS."' between bn_begin_time and bn_end_time " : " $where bn_end_time < '".G5_TIME_YMDHIS."' ";
    $where = ' and ';
    $qstr .= "&amp;bn_time=$bn_time";
}

$g5['title'] = '배너관리';
include_once (G5_ADMIN_PATH.'/admin.head.php');

$sql_common = " from {$g5['g5_shop_banner_table']} ";
$sql_common .= $sql_search;

// 테이블의 전체 레코드수만 얻음
$sql = " select count(*) as cnt " . $sql_common;
$row = sql_fetch($sql);
$total_count = $row['cnt'];

$rows = $config['cf_page_rows'];
$total_page  = ceil($total_count / $rows);  // 전체 페이지 계산
if ($page < 1) { $page = 1; } // 페이지가 없으면 첫 페이지 (1 페이지)
$from_record = ($page - 1) * $rows; // 시작 열을 구함
?>

<div class="local_ov01 local_ov">
    <span class="btn_ov01"><span class="ov_txt"> <?php echo ($sql_search) ? '검색' : '등록'; ?>된 배너 </span><span class="ov_num"> <?php echo $total_count; ?>개</span></span>

    <form name="flist" class="local_sch01 local_sch">
    <input type="hidden" name="page" value="<?php echo $page; ?>">

    <label for="bn_position" class="sound_only">검색</label>
    <select name="bn_position" id="bn_position">
        <option value=""<?php echo get_selected($bn_position, '', true); ?>>위치 전체</option>
        <option value="메인"<?php echo get_selected($bn_position, '메인', true); ?>>메인</option>
        <option value="왼쪽"<?php echo get_selected($bn_position, '왼쪽', true); ?>>왼쪽</option>
    </select>

    <select name="bn_device" id="bn_device">
        <option value="both"<?php echo get_selected($bn_device, 'both', true); ?>>PC와 모바일</option>
        <option value="pc"<?php echo get_selected($bn_device, 'pc'); ?>>PC</option>
        <option value="mobile"<?php echo get_selected($bn_device, 'mobile'); ?>>모바일</option>
    </select>

    <select name="bn_time" id="bn_time">
        <option value=""<?php echo get_selected($bn_time, '', true); ?>>배너 시간 전체</option>
        <option value="ing"<?php echo get_selected($bn_time, 'ing'); ?>>진행중인 배너</option>
        <option value="end"<?php echo get_selected($bn_time, 'end'); ?>>종료된 배너</option>
    </select>

    <input type="submit" value="검색" class="btn_submit">

    </form>

</div>

<div class="btn_fixed_top">
    <a href="./bannerform.php" class="btn_01 btn">배너추가</a>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?> 목록</caption>
    <thead>
    <tr>
        <th scope="col" rowspan="2" id="th_id">ID</th>
        <th scope="col" id="th_dvc">접속기기</th>
        <th scope="col" id="th_loc">위치</th>
        <th scope="col" id="th_st">시작일시</th>
        <th scope="col" id="th_end">종료일시</th>
        <th scope="col" id="th_odr">출력순서</th>
        <th scope="col" id="th_hit">조회</th>
        <th scope="col" id="th_mng">관리</th>
    </tr>
    <tr>
        <th scope="col" colspan="7" id="th_img">이미지</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select * from {$g5['g5_shop_banner_table']} $sql_search
          order by bn_order, bn_id desc
          limit $from_record, $rows  ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        // 테두리 있는지
        $bn_border  = $row['bn_border'];
        // 새창 띄우기인지
        $bn_new_win = ($row['bn_new_win']) ? 'target="_blank"' : '';

        $bimg = G5_DATA_PATH.'/banner/'.$row['bn_id'];
        if(file_exists($bimg)) {
            $size = @getimagesize($bimg);
            if($size[0] && $size[0] > 800)
                $width = 800;
            else
                $width = $size[0];

            $bn_img = "";
           
            $bn_img .= '<img src="'.G5_DATA_URL.'/banner/'.$row['bn_id'].'?'.preg_replace('/[^0-9]/i', '', $row['bn_time']).'" width="'.$width.'" alt="'.get_text($row['bn_alt']).'">';
        }

        switch($row['bn_device']) {
            case 'pc':
                $bn_device = 'PC';
                break;
            case 'mobile':
                $bn_device = '모바일';
                break;
            default:
                $bn_device = 'PC와 모바일';
                break;
        }

        $bn_begin_time = substr($row['bn_begin_time'], 2, 14);
        $bn_end_time   = substr($row['bn_end_time'], 2, 14);

        $bg = 'bg'.($i%2);
    ?>

    <tr class="<?php echo $bg; ?>">
        <td headers="th_id" rowspan="2" class="td_num"><?php echo $row['bn_id']; ?></td>
        <td headers="th_dvc"><?php echo $bn_device; ?></td>
        <td headers="th_loc"><?php echo $row['bn_position']; ?></td>
        <td headers="th_st" class="td_datetime"><?php echo $bn_begin_time; ?></td>
        <td headers="th_end" class="td_datetime"><?php echo $bn_end_time; ?></td>
        <td headers="th_odr" class="td_num"><?php echo $row['bn_order']; ?></td>
        <td headers="th_hit" class="td_num"><?php echo $row['bn_hit']; ?></td>
        <td headers="th_mng" class="td_mng td_mns_m">
            <a href="./bannerform.php?w=u&amp;bn_id=<?php echo $row['bn_id']; ?>" class="btn btn_03">수정</a>
            <a href="./bannerformupdate.php?w=d&amp;bn_id=<?php echo $row['bn_id']; ?>" onclick="return delete_confirm(this);" class="btn btn_02">삭제</a>
        </td>
    </tr>
    <tr class="<?php echo $bg; ?>">
        <td headers="th_img" colspan="7" class="td_img_view sbn_img">
            <div class="sbn_image"><?php echo $bn_img; ?></div>
            <button type="button" class="sbn_img_view btn_frmline">이미지확인</button>
        </td>
    </tr>

    <?php
    }
    if ($i == 0) {
    echo '<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>';
    }
    ?>
    </tbody>
    </table>

</div>

<?php echo get_paging(G5_IS_MOBILE ? $config['cf_mobile_pages'] : $config['cf_write_pages'], $page, $total_page, "{$_SERVER['SCRIPT_NAME']}?$qstr&amp;page="); ?>

<script>
jQuery(function($) {
    $(".sbn_img_view").on("click", function() {
        $(this).closest(".td_img_view").find(".sbn_image").slideToggle();
    });
});
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');