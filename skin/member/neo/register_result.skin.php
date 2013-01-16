<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<div id="reg_result">

    <div class="reg_result_deco"><img src="<?=$g4['path']?>/img/reg_result_deco.jpg" alt=""></div>

    <p>
        <strong><?=$mb['mb_name']?></strong>님의 회원가입을 진심으로 축하합니다.<br>
    </p>

    <? if ($config['cf_use_email_certify']) { ?>
    <p>
        회원 가입 시 입력하신 이메일 주소로 인증메일이 발송되었습니다.<br>
        발송된 인증메일을 확인하신 후 인증처리를 하시면 사이트를 원활하게 이용하실 수 있습니다.
    </p>
    <div id="reg_result_email">
        <span>아이디</span>
        <strong><?=$mb['mb_id']?></strong><br>
        <span>이메일 주소</span>
        <strong><?=$mb['mb_email']?></strong>
    </div>
    <p>
        이메일 주소를 잘못 입력하셨다면, 사이트 관리자에게 문의해주시기 바랍니다.
    </p>
    <? } ?>

    <p>
        회원님의 패스워드는 아무도 알 수 없는 암호화 코드로 저장되므로 안심하셔도 좋습니다.<br>
        아이디, 패스워드 분실시에는 회원가입시 입력하신 패스워드 분실시 질문, 답변을 이용하여 찾을 수 있습니다.
    </p>

    <p>
        회원의 탈퇴는 언제든지 가능하며 탈퇴 후 일정기간이 지난 후, 회원님의 모든 소중한 정보는 삭제하고 있습니다.<br>
        감사합니다.
    </p>

    <div class="btn_confirm">
        <a href="<?=$g4['url']?>/" class="btn_cancel">메인으로</a>
    </div>

</div>