<?php
include_once('./_common.php');

// 옵션명
$index = $idx + 2;
$sql = " select it_opt{$index}_subject as opt_subj from `{$g4['yc4_item_table']}` where it_id = '$it_id' ";
$row = sql_fetch($sql);
$opt_subj = $row['opt_subj'];

$str = '<option value="">' . $opt_subj . '선택</option>'.PHP_EOL;

// 옵션항목
$sql = " select opt_id from `{$g4['yc4_option_table']}` where it_id = '$it_id' and opt_use = '1' and opt_id like '$opt_id%' order by opt_no asc ";
$result = sql_query($sql);
$arr_item = array();

for($i = 0; $row = sql_fetch_array($result); $i++) {
    $opt_item = explode(chr(30), $row['opt_id']);
    $item = trim($opt_item[$idx+1]);
    array_push($arr_item, $item);
}

// 중복옵션 제거
$arr = array_unique($arr_item);

$deli = '';
foreach($arr as $value) {
    if($value) {
        if($showinfo) {
            // 옵션정보
            if($idx >= 0) {
                $deli = chr(30);
            }
            $new_opt_id = $opt_id . $deli . $value;
            $sql = " select opt_amount, opt_qty from {$g4['yc4_option_table']} where it_id = '$it_id' and opt_id = '$new_opt_id' and opt_use = '1' ";
            $row = sql_fetch($sql);
            $opt_info = '';
            if($row['opt_qty']) {
                if($row['opt_amount']) {
                    $opt_info = ' (+' . number_format($row['opt_amount']) . '원)';
                }
            } else {
                $opt_info = ' [품절]';
            }
        }

        $str .= '<option value="'.$value.'">'.$value.$opt_info.'</option>'.PHP_EOL;
    }
}

echo $str;
?>