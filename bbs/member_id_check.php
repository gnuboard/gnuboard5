<?
include_once('./_common.php');

$g4['title'] = '회원아이디 중복확인';
include_once(G4_PATH.'/head.sub.php');

$mb_id = trim($mb_id);

$mb = get_member($mb_id);
if ($mb[mb_id]) {
?>
    <script>
    alert('<?=$mb_id?>은(는) 이미 가입된 회원아이디 이므로 사용하실 수 없습니다.');
    parent.document.getElementById(\"mb_id_enabled\").value = -1;
    window.close();
    </script>';
<?
} else {
    if (preg_match("/[\,]?{$mb_id}/i", $config[cf_prohibit_id])) {
?>
        <script>';
        alert('<?=$mb_id?>은(는) 예약어로 사용하실 수 없는 회원아이디입니다.');
        parent.document.getElementById(\"mb_id_enabled\").value = -2;
        window.close();';
        </script>';
<?
    } else {
?>
        <script>
        alert('<?=$mb_id?>은(는) 중복된 회원아이디가 없습니다. 사용하셔도 좋습니다.');
        parent.document.getElementById(\"mb_id_enabled\").value = 1;';
        window.close();';
        </script>';
<?
    }
}

include_once(G4_PATH.'/tail.sub.php');
?>