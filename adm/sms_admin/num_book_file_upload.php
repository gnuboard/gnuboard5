<?php
$sub_menu = "900900";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

if (!$upload_bg_no)
    alert_after('그룹을 선택해주세요.');

$bg_no = $upload_bg_no;

if (!$_FILES['csv']['size']) 
    alert_after('파일을 선택해주세요.');

$file = $_FILES['csv']['tmp_name'];
$filename = $_FILES['csv']['name'];

$pos = strrpos($filename, '.');
$ext = strtolower(substr($filename, $pos, strlen($filename)));

switch ($ext) {
    case '.csv' :
        $data = file($file);
        $num_rows = count($data) + 1;
        $csv = array();
        foreach ($data as $item) 
        {
            $item = explode(',', $item);

            $item[1] = get_hp($item[1]);

            array_push($csv, $item);

            if (count($item) < 2) 
                alert_after('올바른 파일이 아닙니다.');
        }
        break;
    case '.xls' :
        include_once(G5_LIB_PATH.'/Excel/reader.php');
        $data = new Spreadsheet_Excel_Reader();

        // Set output Encoding.
        $data->setOutputEncoding('UTF-8');
        $data->read($file);
        $num_rows = $data->sheets[0]['numRows'];
        break;
    default :
        alert_after('xls파일과 csv파일만 허용합니다.');
}

$counter = 0;
$success = 0;
$failure = 0;
$inner_overlap = 0;
$overlap = 0;
$arr_hp = array();
$encode = array('ASCII','UTF-8','EUC-KR');

for ($i = 1; $i <= $num_rows; $i++) {
    $counter++;
    $j = 1;

    switch ($ext) {
        case '.csv' :
            $name = $csv[$i][0];
            $str_encode = @mb_detect_encoding($name, $encode);
            if( $str_encode == "EUC-KR" ){
                $name = iconv_utf8( $name );
            }
            $name = addslashes($name);
            $hp   = addslashes($csv[$i][1]);
            break;
        case '.xls' :
            $name = addslashes($data->sheets[0]['cells'][$i][$j++]);
            $str_encode = @mb_detect_encoding($name, $encode);
            if( $str_encode == "EUC-KR" ){
                $name = iconv_utf8( $name );
            }
            $hp   = addslashes(get_hp($data->sheets[0]['cells'][$i][$j++]));
            break;
    }
    if (!(strlen($name)&&$hp))
    {
        $failure++;

    } else {
        if (in_array($hp, $arr_hp))
        {
            $inner_overlap++;
        } else {

            array_push($arr_hp, $hp);

            $res = sql_fetch("select * from {$g5['sms5_book_table']} where bk_hp='$hp'");
            if ($res) 
            {
                $overlap++;
            } 
            else if (!$confirm && $hp) 
            {
                sql_query("insert into {$g5['sms5_book_table']} set bg_no='$bg_no', bk_name='$name', bk_hp='$hp', bk_receipt=1, bk_datetime='".G5_TIME_YMDHIS."'");
                sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count + 1, bg_nomember = bg_nomember + 1, bg_receipt = bg_receipt + 1 where bg_no='$bg_no'");
                $success++;
            }
        }
    }
    if ($inner_overlap > 0) $overlap += $inner_overlap;
}

unlink($_FILES['csv']['tmp_name']);

if ($success){
    $sql = "select count(*) as cnt from {$g5['sms5_book_table']} where bg_no='$bg_no'";
    $total = sql_fetch($sql);
    sql_query("update {$g5['sms5_book_group_table']} set bg_count = ".$total['cnt']." where bg_no='$bg_no'");
}

$result = $counter - $failure - $overlap;

echo "<script>
var info = parent.document.getElementById('upload_info');
var html = '';

html += \"<ul id=\\\"upload_result\\\"><li>총 건수 : ".number_format($counter)." 건</li>\";
html += \"<li class=\\\"sms5_txt_fail\\\">등록불가 ".number_format($failure)." 건</li>\";
html += \"<li>중복번호 ".number_format($overlap)." 건<div id=\\\"overlap\\\" class=\\\"local_desc01 local_desc\\\"></div></li>\";";
if ($result)
{
    if ($confirm) {
        echo "html += \"<li class=\\\"sms5_txt_success\\\">등록가능 ".number_format($result)." 건\";";
        echo "html += \"<br><button type=\\\"button\\\" id=\\\"btn_fileup\\\" class=\\\"btn_submit\\\" onclick=\\\"upload(1)\\\">등록하기</button>\";";
    }
    else
        echo "html += \"<br><span class=\\\"sms5_txt_success\\\">총 ".number_format($success)." 건의 휴대폰번호 등록을 완료하였습니다.</span>\";";
} 
else
    echo "html += \"<br><span class=\\\"sms5_txt_fail\\\">등록할 수 없습니다.</font>\";";
echo "html += \"</li></ul>\";";

echo "
parent.document.getElementById('upload_button').style.display = 'inline';
parent.document.getElementById('uploading').style.display = 'none';
parent.document.getElementById('register').style.display = 'none';

info.style.display = 'block';
info.innerHTML = html;

parent.document.getElementById('overlap').innerHTML = '<p><b>중복번호 목록</b><br>';";

for ($i=0; $i<count($arr_hp); $i++){
echo "parent.document.getElementById('overlap').innerHTML += '".$arr_hp[$i]."<br>';\n";
}
echo "parent.document.getElementById('overlap').innerHTML += '</p>';\n";
echo "</script>";


function alert_after($str) {
    echo "<script>
    parent.document.getElementById('upload_button').style.display = 'inline';
    parent.document.getElementById('uploading').style.display = 'none';
    parent.document.getElementById('register').style.display = 'none';
    parent.document.getElementById('upload_info').style.display = 'none';
    </script>";
    alert_just($str);
}
?>