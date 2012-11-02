<? 
//
// 조병완(korone)님 , 남규아빠(eagletalon)님께서 만들어 주셨습니다.
//

$sub_menu = '100400'; 
include_once('./_common.php'); 

auth_check($auth[$sub_menu], 'r');

$g4[title] = '버전확인'; 

include_once('./admin.head.php'); 
include_once('$g4['path']/lib/mailer.lib.php'); 

echo '현재버전 : <strong>';
$args = 'head -1 '.$g4['path'].'/HISTORY'; 
system($args); 
echo '</strong>';
?> 

<p><?=implode('', file('$g4['path']/HISTORY'));?></p>

<? 
include_once('./admin.tail.php'); 
?> 
