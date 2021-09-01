<?php
$sub_menu = '100400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '부가서비스';
include_once('./admin.head.php');
?>

<div class="local_desc02 local_desc">
    <p>아래의 서비스들은 영카트에서 이미 지원하는 기능으로 별도의 개발이 필요 없으며 서비스 신청후 바로 사용 할수 있습니다.</p>
</div>

<div class="service_wrap">
    <div class="sevice_1 svc_card">
        <h3>신용카드 전자결제 서비스<br><span>(계좌이체, 가상계좌 결제 포함)</span></h3>

        <ul>
            <li><a href="http://sir.kr/main/service/p_pg.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_01.jpg" alt="KCP 신용카드 전자결제 신청하기"></a></li>
            <li><a href="http://sir.kr/main/service/lg_pg.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_02.jpg?v2" alt="토스페이먼츠 전자결제 신청하기"></a></li>
            <li class="last"><a href="http://sir.kr/main/service/inicis_pg.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_06.jpg" alt="KG 이니시스 전자결제 신청하기"></a></li>
        </ul>
    </div>

    <div class="sevice_1 svc_phone">
        <h3>본인확인 서비스</h3>

        <ul>
            <li><a href="http://sir.kr/main/service/p_cert.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_01.jpg" alt="KCP 신청하기"></a></li>
            <li><a href="http://sir.kr/main/service/inicis_cert.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_06.jpg" alt="KG이니시스 신청하기"></a></li>
        </ul>
    </div>

    <div class="service_2">
        <div class="svc_ri svc_sms">
            <div class="svc_a">
                <h3>SMS 문자 서비스</h3>
                <p>주문이나 배송시에 상점운영자 또는 고객에게 휴대폰으로 단문메세지 (최대 한글 40자, 영문 80자)를 발송합니다.</p>
            </div>
            <div class="svc_btn2"><a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_05.jpg" alt="아이코드 SMS 서비스 신청하기"></a></div>
        </div>

    </div>
</div>

<?php
include_once('./admin.tail.php');
