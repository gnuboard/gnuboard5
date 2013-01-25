<?
include_once("./_common.php");

// 내용
$sql = " select * from $g4[yc4_content_table] where co_id = '$co_id' ";
$co = sql_fetch($sql);
if (!$co[co_id]) 
    alert("등록된 내용이 없습니다.");

$g4[title] = $co[co_subject];
//include_once("./_head.php");

if ($co[co_include_head]) 
    @include_once($co[co_include_head]);
else
    include_once("./_head.php");

$himg = "$g4[path]/data/content/{$co_id}_h";
if (file_exists($himg)) 
    echo "<img src='$himg' border=0><br>";

if ($is_admin) 
    echo "<p align=center><a href='./$g4[shop_admin_path]/contentform.php?w=u&co_id=$co_id'><img src='$g4[shop_img_path]/btn_admin_modify.gif' border=0></a></p>";

$str = conv_content($co[co_content], $co[co_html]);

// $src 를 $dst 로 변환
unset($src);
unset($dst);
$src[] = "/{{쇼핑몰명}}|{{홈페이지제목}}/";
//$dst[] = $default[de_subject];
$dst[] = $config[cf_title];
$src[] = "/{{회사명}}|{{상호}}/";
$dst[] = $default[de_admin_company_name];
$src[] = "/{{대표자명}}/";
$dst[] = $default[de_admin_company_owner];
$src[] = "/{{사업자등록번호}}/";
$dst[] = $default[de_admin_company_saupja_no];
$src[] = "/{{대표전화번호}}/";
$dst[] = $default[de_admin_company_tel];
$src[] = "/{{팩스번호}}/";
$dst[] = $default[de_admin_company_fax];
$src[] = "/{{통신판매업신고번호}}/";
$dst[] = $default[de_admin_company_tongsin_no];
$src[] = "/{{사업장우편번호}}/";
$dst[] = $default[de_admin_company_zip];
$src[] = "/{{사업장주소}}/";
$dst[] = $default[de_admin_company_addr];
$src[] = "/{{운영자명}}|{{관리자명}}/";
$dst[] = $default[de_admin_name];
$src[] = "/{{운영자e-mail}}|{{관리자e-mail}}/i";
$dst[] = $default[de_admin_email];
$src[] = "/{{정보관리책임자명}}/";
$dst[] = $default[de_admin_info_name];
$src[] = "/{{정보관리책임자e-mail}}|{{정보책임자e-mail}}/i";
$dst[] = $default[de_admin_info_email];

$str = preg_replace($src, $dst, $str);

echo $str;

$timg = "$g4[path]/data/content/{$co_id}_t";
if (file_exists($timg)) 
    echo "<br><img src='$timg' border=0><br>";

if ($co[co_include_tail]) 
    @include_once($co[co_include_tail]);
else
    include_once("./_tail.php");

//include_once("./_tail.php");
?>
