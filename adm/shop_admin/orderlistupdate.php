<?php
$sub_menu = '400400';
include_once('./_common.php');

//print_r2($_POST); 

for ($i=0; $i<count($_POST['chk']); $i++)
{
    // 실제 번호를 넘김
    $k = $_POST['chk'][$i];

    $od_id = $_POST['od_id'][$k];


    echo $od_id . "<br>";
}
