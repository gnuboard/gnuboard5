<?
$sub_menu = "200300";
include_once("./_common.php");

if (!$config[cf_email_use])
    alert("환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.");

auth_check($auth[$sub_menu], "r");

$sql = "select * from $g4[mail_table] where ma_id = '$ma_id' ";
$ma = sql_fetch($sql);
if (!$ma[ma_id])
    alert("보내실 내용을 선택하여 주십시오.");

// 전체회원수
$sql = "select COUNT(*) as cnt from $g4[member_table] ";
$row = sql_fetch($sql);
$tot_cnt = $row[cnt];

// 탈퇴대기회원수
$sql = "select COUNT(*) as cnt from $g4[member_table] where mb_leave_date <> '' ";
$row = sql_fetch($sql);
$finish_cnt = $row[cnt];

$last_option = explode("||", $ma[ma_last_option]);
for ($i=0; $i<count($last_option); $i++) {
    $option = explode("=", $last_option[$i]);
    // 동적변수
    $var = $option[0];
    $$var = $option[1];
}

if (!isset($mb_id1)) $mb_id1 = 1;
if (!isset($mb_level_from)) $mb_level_from = 1;
if (!isset($mb_level_to)) $mb_level_to = 10;
if (!isset($mb_mailling)) $mb_mailling = 1;
if (!isset($mb_sex)) $mb_sex = 1;
if (!isset($mb_area)) $mb_area = 1;

$g4[title] = "회원메일발송";
include_once("./admin.head.php");
?>


<table width=700 align=center>
<tr>
    <td class='right'>전체회원수 : <?=number_format($tot_cnt)?> 명 , 탈퇴대기회원수 : <?=number_format($finish_cnt)?> 명 , <b>정상회원수 : <?=number_format($tot_cnt - $finish_cnt)?> 명</b></td>
</tr>
<tr>
    <td>
        <table cellpadding=0 cellspacing=0 width=100%>
        <form name=frmsendmailselectform method=post action="./mail_select_list.php" autocomplete="off">
        <input type=hidden name=ma_id value='<? echo $ma_id ?>'>
        <colgroup width=20% class='col1 pad1 bold right'>
        <colgroup width=80% class='col2 pad2'>
        <tr>
            <td></td>
            
        </tr>
        <tr><td colspan='2' class='line1'></td></tr>
        <tr class='ht'>
            <td>회원 ID</td>
            <td>
                <input type=radio name='mb_id1' value='1' onclick="mb_id1_click(1);" <?=$mb_id1?"checked":"";?>> 전체
                <input type=radio name='mb_id1' value='0' onclick="mb_id1_click(0);" <?=!$mb_id1?"checked":"";?>> 구간
                <br>
                <input type=text class=ed id=mb_id1_from name=mb_id1_from value="<?=$mb_id1_from?>"> 에서 
                <input type=text class=ed id=mb_id1_to name=mb_id1_to value="<?=$mb_id1_to?>"> 까지

                <script type="text/javascript">
                function mb_id1_click(num)
                {
                    if (num == 1) {
                        document.getElementById('mb_id1_from').disabled = true;
                        document.getElementById('mb_id1_from').style.backgroundColor = '#EEEEEE';
                        document.getElementById('mb_id1_to').disabled = true;
                        document.getElementById('mb_id1_to').style.backgroundColor = '#EEEEEE';
                    } else {
                        document.getElementById('mb_id1_from').disabled = false;
                        document.getElementById('mb_id1_from').style.backgroundColor = '#FFFFFF';
                        document.getElementById('mb_id1_to').disabled = false;
                        document.getElementById('mb_id1_to').style.backgroundColor = '#FFFFFF';
                    }
                }
                document.onLoad=mb_id1_click(<?=(int)$mb_id1?>);
                </script>
            </td>
        </tr>
        <tr class='ht'>
            <td>생일</td>
            <td>
                <input type=text name='mb_birth_from' size=4 maxlength=4 class=ed value="<?=$mb_birth_from?>"> 부터 
                <input type=text name='mb_birth_to' size=4 maxlength=4 class=ed value="<?=$mb_birth_to?>"> 까지 (예 : 5월5일 인 경우, 0505 와 같이 입력 , 둘다 입력해야함)</td>
        </tr>
        <tr class='ht'>
            <td>E-mail에</td>
            <td><input type=text name='mb_email' class=ed value="<?=$mb_email?>"> 단어 포함 (예 : @sir.co.kr)</td>
        </tr>
        <tr class='ht'>
            <td>성별</td>
            <td>
                <select id=mb_sex name=mb_sex>
                    <option value=''>전체
                    <option value='F'>여자
                    <option value='M'>남자
                </select>
                <script type="text/javascript"> document.getElementById('mb_sex').value = "<?=$mb_sex?>"; </script>
            </td>
        </tr>
        <tr class='ht'>
            <td>지역</td>
            <td>
                <select id=mb_area name=mb_area>
                    <option value=''>전체
                    <option value='서울'>서울
                    <option value='부산'>부산
                    <option value='대구'>대구
                    <option value='인천'>인천
                    <option value='광주'>광주
                    <option value='대전'>대전
                    <option value='울산'>울산
                    <option value='강원'>강원
                    <option value='경기'>경기
                    <option value='경남'>경남
                    <option value='경북'>경북
                    <option value='전남'>전남
                    <option value='전북'>전북
                    <option value='제주'>제주
                    <option value='충남'>충남
                    <option value='충북'>충북
                </select>
                <script type="text/javascript"> document.getElementById('mb_area').value = "<?=$mb_area?>"; </script>
            </td>
        </tr>
        <tr class='ht'>
            <td>메일링</td>
            <td>
                <select id=mb_mailling name=mb_mailling>
                    <option value='1'>수신동의한 회원만
                    <option value=''>전체
                </select>
                <script type="text/javascript"> document.getElementById('mb_mailling').value = "<?=$mb_mailling?>"; </script>
            </td>
        </tr>
        <tr class='ht'>
            <td>권한</td>
            <td>
                <select id=mb_level_from name=mb_level_from>
                <? for ($i=1; $i<=10; $i++) { ?>
                    <option value='<? echo $i ?>'><? echo $i ?>
                <? } ?>
                </select> 에서 
                <select id=mb_level_to name=mb_level_to>
                <? for ($i=1; $i<=10; $i++) { ?>
                    <option value='<? echo $i ?>'><? echo $i ?>
                <? } ?>
                </select> 까지
                <script type="text/javascript"> document.getElementById('mb_level_from').value = "<?=$mb_level_from?>"; </script>
                <script type="text/javascript"> document.getElementById('mb_level_to').value = "<?=$mb_level_to?>"; </script>
            </td>
        </tr>
        <tr class='ht'>
            <td>게시판그룹회원</td>
            <td>
                <select id=gr_id name=gr_id>
                <option value=''>전체
                <?
                $sql = " select gr_id, gr_subject from $g4[group_table] order by gr_subject ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)
                {
                    echo "<option value='$row[gr_id]'>$row[gr_subject]";
                }
                ?>
                </select>
                <script type="text/javascript"> document.getElementById('gr_id').value = "<?=$gr_id?>"; </script>
            </td>
        </tr>
        <tr><td colspan='2' class='line2'></td></tr>
        </table>

        <p align=center>
            <input type=submit class=btn1 value='  확  인  '>&nbsp;
            <input type=button class=btn1 value='  목  록  ' onclick="document.location.href='./mail_list.php';">
        </form>
    </td>
</tr></table>


<?
include_once("./admin.tail.php");
?>
