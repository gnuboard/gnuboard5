<?php
$sub_menu = '300700';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], "w");

//dbconfig파일에 $g5['faq_table'] , $g5['faq_master_table'] 배열변수가 있는지 체크
if( !isset($g5['faq_table']) || !isset($g5['faq_master_table']) ){
    die('/data/dbconfig.php 파일에 <br ><strong>$g5[\'faq_table\'] = G5_TABLE_PREFIX.\'faq\';</strong><br ><strong>$g5[\'faq_master_table\'] = G5_TABLE_PREFIX.\'faq_master\';</strong><br > 를 추가해 주세요.');
}

//자주하시는 질문 마스터 테이블이 있는지 검사한다.
if(!sql_query(" DESCRIBE {$g5['faq_master_table']} ", false)) {
    if(sql_query(" DESCRIBE {$g5['g5_shop_faq_master_table']} ", false)) {
        sql_query(" ALTER TABLE {$g5['g5_shop_faq_master_table']} RENAME TO `{$g5['faq_master_table']}` ;", false);
    } else {
       $query_cp = sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['faq_master_table']}` (
                      `fm_id` int(11) NOT NULL AUTO_INCREMENT,
                      `fm_subject` varchar(255) NOT NULL DEFAULT '',
                      `fm_head_html` text NOT NULL,
                      `fm_tail_html` text NOT NULL,
                      `fm_order` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`fm_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
    }
    // FAQ Master
    sql_query(" insert into `{$g5['faq_master_table']}` set fm_id = '1', fm_subject = '자주하시는 질문' ", false);
}

//자주하시는 질문 테이블이 있는지 검사한다.
if(!sql_query(" DESCRIBE {$g5['faq_table']} ", false)) {
    if(sql_query(" DESCRIBE {$g5['g5_shop_faq_table']} ", false)) {
        sql_query(" ALTER TABLE {$g5['g5_shop_faq_table']} RENAME TO `{$g5['faq_table']}` ;", false);
    } else {
       $query_cp = sql_query(" CREATE TABLE IF NOT EXISTS `{$g5['faq_table']}` (
                      `fa_id` int(11) NOT NULL AUTO_INCREMENT,
                      `fm_id` int(11) NOT NULL DEFAULT '0',
                      `fa_subject` text NOT NULL,
                      `fa_content` text NOT NULL,
                      `fa_order` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`fa_id`),
                      KEY `fm_id` (`fm_id`)
                    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ", true);
    }
}

$sql = " select * from {$g5['faq_master_table']} where fm_id = '$fm_id' ";
$fm = sql_fetch($sql);

$html_title = 'FAQ '.$fm['fm_subject'];;
$g5['title'] = $html_title.' 관리';

if ($w == "u")
{
    $html_title .= " 수정";
    $readonly = " readonly";

    $sql = " select * from {$g5['faq_table']} where fa_id = '$fa_id' ";
    $fa = sql_fetch($sql);
    if (!$fa['fa_id']) alert("등록된 자료가 없습니다.");

    $fa['fa_subject'] = htmlspecialchars2($fa['fa_subject']);
    $fa['fa_content'] = htmlspecialchars2($fa['fa_content']);
}
else
    $html_title .= ' 항목 입력';

include_once (G5_ADMIN_PATH.'/admin.head.php');
?>

<form name="frmfaqform" action="./faqformupdate.php" onsubmit="return frmfaqform_check(this);" method="post">
<input type="hidden" name="w" value="<?php echo $w; ?>">
<input type="hidden" name="fm_id" value="<?php echo $fm_id; ?>">
<input type="hidden" name="fa_id" value="<?php echo $fa_id; ?>">

<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="fa_order">출력순서</label></th>
        <td>
            <?php echo help('숫자가 작을수록 FAQ 페이지에서 먼저 출력됩니다.'); ?>
            <input type="text" name="fa_order" value="<?php echo $fa['fa_order']; ?>" id="fa_order" class="frm_input" maxlength="10" size="10">
            <?php if ($w == 'u') { ?><a href="<?php echo G5_BBS_URL; ?>/faq.php?fm_id=<?php echo $fm_id; ?>" class="btn_frmline">내용보기</a><?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">질문</th>
        <td><?php echo editor_html('fa_subject', $fa['fa_subject']); ?></td>
    </tr>
    <tr>
        <th scope="row">답변</th>
        <td><?php echo editor_html('fa_content', $fa['fa_content']); ?></td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./faqlist.php?fm_id=<?php echo $fm_id; ?>">목록</a>
</div>

</form>

<script>
function frmfaqform_check(f)
{
    errmsg = "";
    errfld = "";

    //check_field(f.fa_subject, "제목을 입력하세요.");
    //check_field(f.fa_content, "내용을 입력하세요.");

    if (errmsg != "")
    {
        alert(errmsg);
        errfld.focus();
        return false;
    }

    <?php echo get_editor_js('fa_subject'); ?>
    <?php echo get_editor_js('fa_content'); ?>

    return true;
}

// document.getElementById('fa_order').focus(); 포커스 해제
</script>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
?>
