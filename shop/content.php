<?php
include_once('./_common.php');

// 내용
$sql = " select * from {$g5['g5_shop_content_table']} where co_id = '$co_id' ";
$co = sql_fetch($sql);
if (!$co['co_id'])
    alert('등록된 내용이 없습니다.');

$g5['title'] = $co['co_subject'];

if ($co['co_include_head'])
    @include_once($co['co_include_head']);
else
    include_once('./_head.php');

$str = conv_content($co['co_content'], $co['co_html']);

// $src 를 $dst 로 변환
unset($src);
unset($dst);
$src[] = "/{{쇼핑몰명}}|{{홈페이지제목}}/";
//$dst[] = $default[de_subject];
$dst[] = $config['cf_title'];
$src[] = "/{{회사명}}|{{상호}}/";
$dst[] = $default['de_admin_company_name'];
$src[] = "/{{대표자명}}/";
$dst[] = $default['de_admin_company_owner'];
$src[] = "/{{사업자등록번호}}/";
$dst[] = $default['de_admin_company_saupja_no'];
$src[] = "/{{대표전화번호}}/";
$dst[] = $default['de_admin_company_tel'];
$src[] = "/{{팩스번호}}/";
$dst[] = $default['de_admin_company_fax'];
$src[] = "/{{통신판매업신고번호}}/";
$dst[] = $default['de_admin_company_tongsin_no'];
$src[] = "/{{사업장우편번호}}/";
$dst[] = $default['de_admin_company_zip'];
$src[] = "/{{사업장주소}}/";
$dst[] = $default['de_admin_company_addr'];
$src[] = "/{{운영자명}}|{{관리자명}}/";
$dst[] = $default['de_admin_name'];
$src[] = "/{{운영자e-mail}}|{{관리자e-mail}}/i";
$dst[] = $default['de_admin_email'];
$src[] = "/{{정보관리책임자명}}/";
$dst[] = $default['de_admin_info_name'];
$src[] = "/{{정보관리책임자e-mail}}|{{정보책임자e-mail}}/i";
$dst[] = $default['de_admin_info_email'];

$str = preg_replace($src, $dst, $str);

if ($is_admin)
    echo '<div class="socc_admin"><a href="'.G5_ADMIN_URL.'/shop_admin/contentform.php?w=u&amp;co_id='.$co_id.'" class="btn_admin">내용 수정</a></div>';
?>

<!-- 등록내용 시작 { -->
<?php
$himg = G5_DATA_PATH.'/content/'.$co_id.'_h';
if (file_exists($himg)) // 상단 이미지
    echo '<div id="socc_himg" class="socc_img"><img src="'.G5_DATA_URL.'/content/'.$co_id.'_h" alt=""></div>';
?>

<article id="socc" class="socc_<?php echo $co_id; ?>">
    <header>
        <h1><?php echo $g5['title']; ?></h1>
    </header>

    <div id="socc_con">
        <?php echo $str; ?>
    </div>

</article>

<?php
$timg = G5_DATA_PATH.'/content/'.$co_id.'_t';
if (file_exists($timg)) // 하단 이미지
    echo '<div id="socc_timg" class="socc_img"><img src="'.G5_DATA_URL.'/content/'.$co_id.'_t" alt=""></div>';

if ($is_admin)
    echo '<div class="socc_admin"><a href="'.G5_ADMIN_URL.'/shop_admin/contentform.php?w=u&amp;co_id='.$co_id.'" class="btn_admin">내용 수정</a></div>';
?>
<!-- } 등록내용 끝 -->

<?php
if ($co['co_include_tail'])
    @include_once($co['co_include_tail']);
else
    include_once('./_tail.php');
?>
