<?php
$sub_menu = '100400';
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$g5['title'] = '부가서비스';
include_once('./admin.head.php');
?>

<div class="local_desc02 local_desc">
    <p>아래의 서비스들은 그누보드에서 이미 지원하는 기능으로 별도의 개발이 필요 없으며 서비스 신청후 바로 사용 할수 있습니다.</p>
</div>

<div class="service_wrap">
    <div class="sevice_1">
        <img src="<?php echo G5_ADMIN_URL ?>/img/service_img1.jpg" alt="휴대폰 본인확인 서비스" class="svc_img">
        <h3>휴대폰 본인확인 서비스</h3>
        <p>정보통신망법 23조 2항(주민등록번호의 사용제한)에 따라 기존 주민등록번호 기반의 인증서비스 이용이 불가합니다. 주민등록번호 대체수단으로 최소한의 정보(생년월일, 휴대폰번호, 성별)를 입력받아 본인임을 확인하는 인증수단 입니다</p>

        <ul>
            <li><a href="http://sir.co.kr/main/service/p_cert.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_01.jpg" alt="KCP 휴대폰 본인확인 신청하기"></a></li>
            <li><a href="http://sir.co.kr/main/service/lg_cert.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_02.jpg" alt="LG유플러스 휴대폰대체인증 신청하기"></a></li>
            <li class="last"><a href="http://sir.co.kr/main/service/b_cert.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_03.jpg" alt="OKname 휴대폰 본인확인 신청하기"></a></li>
        </ul>
    </div>
    <div class="sevice_1">
        <img src="<?php echo G5_ADMIN_URL ?>/img/service_img2.jpg" alt="아이핀 본인확인 서비스" class="svc_img">
        <h3>아이핀 본인확인 서비스</h3>
        <p>정부가 주관하는 주민등록번호 대체 수단으로 본인의 개인정보를 아이핀 사이트에 한번만 발급해 놓고, 이후부터는 아이디와 패스워드 만으로
        본인임을 확인하는 인증수단 입니다.</p>

       <h4><a href="http://sir.co.kr/main/service/b_ipin.php" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_04.jpg" alt="OKname 아이핀 본인확인 신청하기"></a></h4>
    </div>
    <div class="sevice_2">
            <div class="svc_sms">
                <div class="svc_a">
                    <h3>SMS 문자 서비스</h3>
                    <p>사이트 관리자 또는 회원이 다른 회원의 <br>휴대폰으로 단문메세지(최대 한글 40자, 영문 80자)를 발송할 수 있습니다.</p>
                </div>
                <div class="svc_btn"><a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank"><img src="<?php echo G5_ADMIN_URL ?>/img/svc_btn_05.jpg" alt="아이코드 SMS 서비스 신청하기"></a></div>
            </div>
            
    </div>
</div>

<?php
include_once('./admin.tail.php');
?>
