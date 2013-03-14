<?
set_time_limit(0);

include_once("../config.php");
include_once("../shop.config.php");

$simg_width = $simg_height = 120;
$mimg_width = $mimg_height = 250;

// 파일이 존재한다면 설치할 수 없다.
if (file_exists("../dbconfig.php")) {
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";    
    echo <<<HEREDOC
    <script language="JavaScript">
    alert("설치하실 수 없습니다.");
    location.href="../";
    </script>
HEREDOC;
    exit;
}

$gmnow = gmdate("D, d M Y H:i:s") . " GMT";
header("Expires: 0"); // rfc2616 - Section 14.21
header("Last-Modified: " . $gmnow);
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: pre-check=0, post-check=0, max-age=0"); // HTTP/1.1
header("Pragma: no-cache"); // HTTP/1.0

$mysql_host  = $_POST[mysql_host];
$mysql_user  = $_POST[mysql_user];
$mysql_pass  = $_POST[mysql_pass];
$mysql_db    = $_POST[mysql_db];
$admin_id    = $_POST[admin_id];
$admin_pass  = $_POST[admin_pass];
$admin_name  = $_POST[admin_name];
$admin_email = $_POST[admin_email];

if (strtolower($g4[charset]) == 'utf-8') @mysql_query("set names utf8"); 
else if (strtolower($g4[charset]) == 'euc-kr') @mysql_query("set names euckr"); 
$dblink = @mysql_connect($mysql_host, $mysql_user, $mysql_pass);
if (!$dblink) {
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";    
    echo "<script language='JavaScript'>alert('MySQL Host, User, Password 를 확인해 주십시오.');history.back();</script>"; 
    exit;
}

if (strtolower($g4[charset]) == 'utf-8') @mysql_query("set names utf8"); 
else if (strtolower($g4[charset]) == 'euc-kr') @mysql_query("set names euckr"); 
$select_db = @mysql_select_db($mysql_db, $dblink);
if (!$select_db) {
    echo "<meta http-equiv='content-type' content='text/html; charset=$g4[charset]'>";    
    echo "<script language='JavaScript'>alert('MySQL DB 를 확인해 주십시오.');history.back();</script>"; 
    exit;
}
?>
<html>
<head>
<meta http-equiv="content-type" content="text/html; charset=<?=$g4['charset']?>">
<title>영카트4 설치 (3/3) - DB</title>
<style type="text/css">
.body {
    font-family: 굴림;
	font-size: 12px;
}
.box {
	background-color: #FCFCFC;
    color:#B19265;
    font-family:굴림;
	font-size: 12px;
}
.nobox {
	background-color: #FCFCFC;
    border-style:none;
    font-family:굴림;
    font-size: 12px;
}
</style>
</head>

<body background="img/all_bg.gif" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<div align="center">
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <p>&nbsp;</p>
  <table width="587" border="0" cellspacing="0" cellpadding="0">
    <form name=frminstall2>
    <tr> 
        <td colspan="3"><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="587" height="22">
            <param name="movie" value="img/top.swf">
            <param name="quality" value="high">
            <embed src="img/top.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="587" height="22"></embed></object></td>
    </tr>
    <tr> 
      <td width="3"><img src="img/box_left.gif" width="3" height="340"></td>
      <td width="581" valign="top" bgcolor="#FCFCFC"><table width="581" border="0" cellspacing="0" cellpadding="0">
          <tr> 
            <td><img src="img/box_title.gif" width="581" height="56"></td>
          </tr>
        </table>
        <br>
        <table width="541" border="0" align="center" cellpadding="0" cellspacing="0" class="body">
          <tr> 
            <td style="cursor:default;">설치를 시작합니다. <font color="#CC0000">설치중 작업을 중단하지 마십시오. </font></td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td><div align="left">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <input name="status_bar" type="text" class="box" size="76" readonly style="cursor:default;"></div></td>
          </tr>
          <tr> 
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td><table width="350" border="0" align="center" cellpadding="5" cellspacing="0" class="body">
                <tr> 
                  <td width="50"> </td>
                  <td width="300"><input type=text name=job1 class=nobox size=80 readonly style="cursor:default;"></td>
                </tr>
                <tr> 
                  <td width="50"> </td>
                  <td width="300"><input type=text name=job2 class=nobox size=80 readonly style="cursor:default;"></td>
                </tr>
                <tr> 
                  <td width="50"> </td>
                  <td width="300"><input type=text name=job3 class=nobox size=80 readonly style="cursor:default;"></td>
                </tr>
                <tr> 
                  <td width="50"> 
                    <div align="center"></div></td>
                  <td width="300"><input type=text name=job4 class=nobox size=80 readonly style="cursor:default;"></td>
                </tr>
              </table></td>
          </tr>
          <tr>
            <td>&nbsp;</td>
          </tr>
          <tr> 
            <td><input type=text name=job5 class=nobox size=90 readonly style="cursor:default;"></td>
          </tr>
        </table>
        <table width="562" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr>
            <td height=30><img src="img/box_line.gif" width="562" height="2"></td>
          </tr>
        </table>
        <table width="551" border="0" align="center" cellpadding="0" cellspacing="0">
          <tr> 
            <td align="right"> 
              <input type="button" name="btn_next" disabled value="메인화면" onclick="location.href='../';">
            </td>
          </tr>
        </table></td>
      <td width="3"><img src="img/box_right.gif" width="3" height="340"></td>
    </tr>
    <tr> 
      <td colspan="3"><img src="img/box_bottom.gif" width="587" height="3"></td>
    </tr>
    </form>
  </table>
</div>
<?
flush(); usleep(50000);


$sql = " desc $g4[config_table] ";
$result = @mysql_query($sql);
// 그누보드4 재설치에 체크하셨거나 그누보드4가 설치되어 있지 않다면
if ($install_g4 || !$result) 
{
    // 테이블 생성 ------------------------------------
    // 그누보드4 테이블 생성
    $file = implode("", file("./sql_gnuboard4.sql"));
    eval("\$file = \"$file\";");

    $f = explode(";", $file);
    for ($i=0; $i<count($f); $i++) {
        if (trim($f[$i]) == "") continue;
        mysql_query($f[$i]) or die(mysql_error());
    }


    // config 테이블 설정
    $read_point = -1;
    $write_point = 5;
    $comment_point = 1;
    $download_point = -20;

    $sql = " insert into $g4[config_table]
                set cf_title = '쇼핑몰',
                    cf_admin = '$admin_id',
                    cf_use_point = '1',
                    cf_use_norobot = '1',
                    cf_use_copy_log = '1',
                    cf_login_point = '100',
                    cf_cut_name = '10',
                    cf_nick_modify = '60',
                    cf_new_skin = 'basic',
                    cf_new_rows = '15',
                    cf_search_skin = 'basic',
                    cf_connect_skin = 'basic',
                    cf_read_point = '$read_point',
                    cf_write_point = '$write_point',
                    cf_comment_point = '$comment_point',
                    cf_download_point = '$download_point',
                    cf_search_bgcolor = 'YELLOW',
                    cf_search_color = 'RED',
                    cf_write_pages = '10',
                    cf_link_target = '_blank',
                    cf_delay_sec = '30',
                    cf_filter = '18아,18놈,18새끼,18년,18뇬,18노,18것,18넘,개년,개놈,개뇬,개새,개색끼,개세끼,개세이,개쉐이,개쉑,개쉽,개시키,개자식,개좆,게색기,게색끼,광뇬,뇬,눈깔,뉘미럴,니귀미,니기미,니미,도촬,되질래,뒈져라,뒈진다,디져라,디진다,디질래,병쉰,병신,뻐큐,뻑큐,뽁큐,삐리넷,새꺄,쉬발,쉬밸,쉬팔,쉽알,스패킹,스팽,시벌,시부랄,시부럴,시부리,시불,시브랄,시팍,시팔,시펄,실밸,십8,십쌔,십창,싶알,쌉년,썅놈,쌔끼,쌩쑈,썅,써벌,썩을년,쎄꺄,쎄엑,쓰바,쓰발,쓰벌,쓰팔,씨8,씨댕,씨바,씨발,씨뱅,씨봉알,씨부랄,씨부럴,씨부렁,씨부리,씨불,씨브랄,씨빠,씨빨,씨뽀랄,씨팍,씨팔,씨펄,씹,아가리,아갈이,엄창,접년,잡놈,재랄,저주글,조까,조빠,조쟁이,조지냐,조진다,조질래,존나,존니,좀물,좁년,좃,좆,좇,쥐랄,쥐롤,쥬디,지랄,지럴,지롤,지미랄,쫍빱,凸,퍽큐,뻑큐,빠큐,ㅅㅂㄹㅁ',
                    cf_possible_ip = '',
                    cf_intercept_ip = '',
                    cf_member_skin = 'shop_member',
                    cf_register_level = '2',
                    cf_register_point = '1000',
                    cf_icon_level = '2',
                    cf_leave_day = '30',
                    cf_search_part = '10000',
                    cf_email_use = '1',
                    cf_prohibit_id = 'admin,administrator,관리자,운영자,어드민,주인장,webmaster,웹마스터,sysop,시삽,시샵,manager,매니저,메니저,root,루트,su,guest,방문객',
                    cf_prohibit_email = '',
                    cf_new_del = '30',
                    cf_memo_del = '180',
                    cf_visit_del = '180',
                    cf_popular_del = '180',
                    cf_use_member_icon = '2',
                    cf_member_icon_size = '5000',
                    cf_member_icon_width = '22',
                    cf_member_icon_height = '22',
                    cf_login_minutes = '10',
                    cf_image_extension = 'gif|jpg|jpeg|png',
                    cf_flash_extension = 'swf',
                    cf_movie_extension = 'asx|asf|wmv|wma|mpg|mpeg|mov|avi|mp3',
                    cf_formmail_is_member = '1',
                    cf_page_rows = '15',
                    cf_stipulation = '해당 홈페이지에 맞는 회원가입약관을 입력합니다.' 
                    ";
    @mysql_query($sql) or die(mysql_error() . "<p>" . $sql);
}


// 영카트4 테이블 생성
$file = implode("", file("./sql_youngcart4.sql"));
eval("\$file = \"$file\";");

$f = explode(";", $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == "") continue;
    mysql_query($f[$i]) or die(mysql_error());
}
// 테이블 생성 ------------------------------------

echo "<script>document.frminstall2.job1.value='전체 테이블 생성중';</script>";
flush(); usleep(50000); 

for ($i=0; $i<45; $i++)
{
    echo "<script language='JavaScript'>document.frminstall2.status_bar.value += '■';</script>\n";
    flush();
    usleep(500); 
}

echo "<script>document.frminstall2.job1.value='전체 테이블 생성 완료';</script>";
flush(); usleep(50000); 


// 게시판 그룹 생성
@mysql_query(" insert into $g4[group_table] set gr_id = 'shop', gr_subject = '쇼핑몰' ");

// 게시판 생성
$tmp_bo_table   = array ("qa", "free", "notice");
$tmp_bo_subject = array ("질문답변", "자유게시판", "공지사항");
for ($i=0; $i<count($tmp_bo_table); $i++)
{
    $sql = " insert into $g4[board_table] 
                set bo_table = '$tmp_bo_table[$i]', 
                    gr_id = 'shop', 
                    bo_subject = '$tmp_bo_subject[$i]',
                    bo_count_delete = '1',
                    bo_count_modify = '1',
                    bo_gallery_cols = '4',
                    bo_table_width = '97',
                    bo_page_rows = '15',
                    bo_subject_len = '60',
                    bo_new = '24',
                    bo_hot = '100',
                    bo_image_width = '600',
                    bo_upload_count = '2',
                    bo_upload_size = '1024768',
                    bo_reply_order = '1',
                    bo_use_search = '1',
                    bo_skin = 'basic',
                    bo_disable_tags = 'script|iframe',
                    bo_include_head = '../head.php',
                    bo_include_tail = '../tail.php'
                    ";
    @mysql_query($sql);

    // 게시판 테이블 생성
    $file = file("../$g4[admin]/sql_write.sql");
    $sql = implode($file, "\n");

    $create_table = $g4[write_prefix] . $tmp_bo_table[$i];

    // sql_board.sql 파일의 테이블명을 변환
    $source = array("/__TABLE_NAME__/", "/;/");
    $target = array($create_table, "");
    $sql = preg_replace($source, $target, $sql);
    @mysql_query($sql);
}


// 운영자 회원가입
$sql = " insert into $g4[member_table]
            set mb_id = '$admin_id',
                mb_password = PASSWORD('$admin_pass'),
                mb_name = '$admin_name',
                mb_nick = '$admin_name',
                mb_email = '$admin_email',
                mb_jumin = PASSWORD('1111111111118'),
                mb_level = '10',
                mb_mailling = '1',
                mb_open = '1',
                mb_email_certify = '$g4[time_ymdhis]',
                mb_datetime = '$g4[time_ymdhis]',
                mb_ip = '$_SERVER[REMOTE_ADDR]' 
                ";
@mysql_query($sql);


// 내용관리 생성
@mysql_query(" insert into $g4[yc4_content_table] set co_id = 'company', co_html = '1', co_subject = '회사소개', co_content= '<p align=center><b>회사소개에 대한 내용을 입력하십시오.</b>' ") or die(mysql_error() . "<p>" . $sql);
@mysql_query(" insert into $g4[yc4_content_table] set co_id = 'privacy', co_html = '1', co_subject = '개인정보 취급방침', co_content= '<p align=center><b>개인정보 취급방침에 대한 내용을 입력하십시오.' ") or die(mysql_error() . "<p>" . $sql);
@mysql_query(" insert into $g4[yc4_content_table] set co_id = 'provision', co_html = '1', co_subject = '서비스 이용약관', co_content= '<p align=center><b>서비스 이용약관에 대한 내용을 입력하십시오.' ") or die(mysql_error() . "<p>" . $sql);

// 온라인견적
@mysql_query(" insert into $g4[yc4_onlinecalc_table] set oc_id = '1', oc_subject = '온라인견적' ") or die(mysql_error() . "<p>" . $sql);

// FAQ Master
@mysql_query(" insert into $g4[yc4_faq_master_table] set fm_id = '1', fm_subject = '자주하시는 질문' ") or die(mysql_error() . "<p>" . $sql);

// default 설정 (쇼핑몰 설정)
$sql = " insert into $g4[yc4_default_table]
            set de_admin_company_name = '회사명',
                de_admin_company_saupja_no = '123-45-67890',
                de_admin_company_owner = '대표자명',
                de_admin_company_tel = '02-123-4567',
                de_admin_company_fax = '02-123-4568',
                de_admin_tongsin_no = '제 OO구 - 123호',
                de_admin_buga_no = '12345호',
                de_admin_company_zip = '123-456',
                de_admin_company_addr = 'OO도 OO시 OO구 OO동 123-45',
                de_admin_info_name = '정보책임자명',
                de_admin_info_email = '정보책임자 E-mail',
                de_type1_list_use = '1',
                de_type1_list_skin = 'maintype10.inc.php',
                de_type1_list_mod = '3',
                de_type1_list_row = '2',
                de_type1_img_width = '$simg_width',
                de_type1_img_height = '$simg_height',
                de_type2_list_use = '1',
                de_type2_list_skin = 'maintype20.inc.php',
                de_type2_list_mod = '3',
                de_type2_list_row = '2',
                de_type2_img_width = '$simg_width',
                de_type2_img_height = '$simg_height',
                de_type3_list_use = '1',
                de_type3_list_skin = 'maintype30.inc.php',
                de_type3_list_mod = '1',
                de_type3_list_row = '3',
                de_type3_img_width = '$simg_width',
                de_type3_img_height = '$simg_height',
                de_type4_list_use = '1',
                de_type4_list_skin = 'maintype40.inc.php',
                de_type4_list_mod = '3',
                de_type4_list_row = '1',
                de_type4_img_width = '$simg_width',
                de_type4_img_height = '$simg_height',
                de_type5_list_use = '1',
                de_type5_list_skin = 'maintype50.inc.php',
                de_type5_list_mod = '3',
                de_type5_list_row = '1',
                de_type5_img_width = '$simg_width',
                de_type5_img_height = '$simg_height',
                de_bank_use = '1',
                de_bank_account = 'OO은행 12345-67-89012 예금주명',
                de_vbank_use = '0',
                de_iche_use = '0',
                de_card_use = '0',
                de_card_max_amount = '1000',
                de_point_settle = '10000',
                de_point_per = '5',
                de_card_point = '0',
                de_point_days = '7',
                de_card_pg = 'kcp',
                de_kcp_mid = '',
                de_send_cost_case = '상한',
                de_send_cost_limit = '20000;30000;40000',
                de_send_cost_list = '4000;3000;2000',
                de_hope_date_use = '0',
                de_hope_date_after = '3',
                de_baesong_content = '<b>배송 내용을 입력하십시오.</b>',
                de_change_content = '<b>교환/반품 내용을 입력하십시오.</b>',
                de_rel_list_mod = '4',
                de_rel_img_width = '$simg_width',
                de_rel_img_height = '$simg_height',
                de_simg_width = '$simg_width',
                de_simg_height = '$simg_height',
                de_mimg_width = '$mimg_width',
                de_mimg_height = '$mimg_height',
                de_item_ps_use = '1',
                de_level_sell = '1',
                de_code_dup_use = '1',
                de_sms_cont1 = '{이름}님의 회원가입을 축하드립니다.\nID:{회원아이디}\n{회사명}',
                de_sms_cont2 = '{이름}님께서 주문하셨습니다.\n{주문번호}\n{주문금액}원\n{회사명}',
                de_sms_cont3 = '{이름}님 입금 감사합니다.\n{입금액}원\n주문번호:\n{주문번호}\n{회사명}',
                de_sms_cont4 = '{이름}님 배송합니다.\n택배:{택배회사}\n운송장번호:\n{운송장번호}\n{회사명}'
                ";
mysql_query($sql) or die(mysql_error() . "<p>" . $sql);


echo "<script>document.frminstall2.job2.value='DB설정 완료';</script>";
flush(); usleep(50000); 
//-------------------------------------------------------------------------------------------------

// DB 설정 파일 생성
$file = "../dbconfig.php";
$f = @fopen($file, "w");

fwrite($f, "<?\n");
fwrite($f, "\$mysql_host = '$mysql_host';\n");
fwrite($f, "\$mysql_user = '$mysql_user';\n");
fwrite($f, "\$mysql_password = '$mysql_pass';\n");
fwrite($f, "\$mysql_db = '$mysql_db';\n");
fwrite($f, "?>");

fclose($f);
@chmod($file, 0666);
echo "<script>document.frminstall2.job3.value='DB설정 파일 생성 완료';</script>";

flush(); usleep(50000); 


// 디렉토리 생성
$dir_arr = array ("../extend",
                  "../data",
                  "../data/file",
                  "../data/log",
                  "../data/member",
                  "../data/session",
                  "../data/$g4[editor]",
                  "../data/$g4[cheditor4]",
                  "../data/banner",
                  "../data/category",
                  "../data/common",
                  "../data/content",
                  "../data/event",
                  "../data/faq",
                  "../data/item",
                  "../data/onlinecalc"
                  );
for ($i=0; $i<count($dir_arr); $i++) 
{
    @mkdir($dir_arr[$i], 0777);
    @chmod($dir_arr[$i], 0777);

    /*
    // 디렉토리에 있는 파일의 목록을 보이지 않게 한다.
    $file = $dir_arr[$i] . "/index.php";
    $f = @fopen($file, "w");
    @fwrite($f, "");
    @fclose($f);
    @chmod($file, 0666);
    */
}

// data 디렉토리 및 하위 디렉토리에서는 .htaccess .htpasswd .php .phtml .html .htm .inc .cgi .pl 파일을 실행할수 없게함.
$f = fopen("../data/.htaccess", "w");
$str = <<<EOD
<FilesMatch "\.(htaccess|htpasswd|[Pp][Hh][Pp]|[Pp]?[Hh][Tt][Mm][Ll]?|[Ii][Nn][Cc]|[Cc][Gg][Ii]|[Pp][Ll])">
Order allow,deny 
Deny from all
</FilesMatch>
EOD;
fwrite($f, $str);
fclose($f);

@rename("../install", "../install.bak");

@copy("../logo_img", "../data/common/logo_img");
@copy("../main_img", "../data/common/main_img");

@copy("../company_h", "../data/content/company_h");
@copy("../privacy_h", "../data/content/privacy_h");
@copy("../provision_h", "../data/content/provision_h");

// 삭제
@unlink("../logo_img");
@unlink("../main_img");
@unlink("../company_h");
@unlink("../privacy_h");
@unlink("../provision_h");
//-------------------------------------------------------------------------------------------------

echo "<script language='JavaScript'>document.frminstall2.status_bar.value += '■';</script>\n";
flush();
sleep(1);

echo "<script>document.frminstall2.job4.value='필요한 Table, File, 디렉토리 생성을 모두 완료 하였습니다.';</script>";
echo "<script>document.frminstall2.job5.value='* 메인화면에서 운영자 로그인을 한 후 운영자 화면으로 이동하여 환경설정을 변경해 주십시오.';</script>";
flush(); usleep(50000); 
?>

<script>document.frminstall2.btn_next.disabled = false;</script>
<script>document.frminstall2.btn_next.focus();</script>
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                            <img src="<?="http://sir.co.kr/manage/youngcart4/yc4_install.php?host={$_SERVER['HTTP_HOST']}&script_name={$_SERVER['SCRIPT_NAME']}&ip={$_SERVER['SERVER_ADDR']}"?>" width="0" height="0">
</body>
</html>