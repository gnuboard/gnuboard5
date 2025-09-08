<?php
include_once(G5_KAKAO5_PATH . '/Popbill/PopbillKakao.php');

/*************************************************************************
**
**  공통 : 팝빌 카카오톡 발송
**
*************************************************************************/
/** 
 * kakao 서비스 인스턴스 생성
 */
function get_kakao_service_instance() {
    global $linkID, $secretKey;

    // 이미 생성된 인스턴스가 있으면 반환
    static $KakaoService = null;
    if ($KakaoService !== null) {
        return $KakaoService;
    }

    // 통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
    define('LINKHUB_COMM_MODE','CURL');

    $KakaoService = new KakaoService($linkID, $secretKey);

    // 연동환경 설정, true-테스트, false-운영(Production), (기본값:false)
    $KakaoService->IsTest(G5_KAKAO5_IS_TEST);

    return $KakaoService;
}

/** 
 * 팝빌 정보 확인
 */ 
function get_popbill_service_info(){
    global $userID, $corpnum;

    if (empty($userID) || strlen($userID) < 4) {
        return array('error' => '연결 실패: 회원아이디가 없거나 올바르지 않습니다. 회원아이디를 확인해주세요.');
    }

    try {
        $KakaoService = get_kakao_service_instance();
        $corpInfo = $KakaoService->GetCorpInfo($corpnum, $userID);
        $balance = $KakaoService->GetBalance($corpnum);

        if ($balance === false || $balance < 0) {
            return array('error' => '팝빌 API 연결에 실패했습니다. 설정값을 확인해주세요.');
        }

        return array('success' => true, 'balance' => $balance, 'corpInfo' => $corpInfo);
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 팝빌 템플릿 목록 조회
 */
function get_popbill_template_list(){
    global $corpnum;

    try {
        $KakaoService = get_kakao_service_instance();
        $templates = $KakaoService->ListATSTemplate($corpnum);

        if (empty($templates)) {
            return array('error' => '템플릿 목록을 가져올 수 없습니다.');
        }

        return $templates;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 포인트 충전 팝업 URL
 */
function get_popbill_point_URL(){
    global $corpnum, $userID;
    
    try {
        $KakaoService = get_kakao_service_instance();
        $url = $KakaoService->GetChargeURL($corpnum, $userID);

        if (empty($url)) {
            return array('error' => '포인트 충전 URL을 가져올 수 없습니다.');
        }

        return $url;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 템플릿 정보 확인 
 */
function get_popbill_template_info($template_code, $type = ''){
    global $corpnum;

    try {
        $KakaoService = get_kakao_service_instance();
        $info = $KakaoService->GetATSTemplate($corpnum, $template_code);

        if (empty($info)) {
            return array('error' => '해당 템플릿 정보를 가져올 수 없습니다.');
        }

        if ($type) {
            if (is_object($info) && isset($info->$type)) {
                return $info->$type;
            } else if (is_array($info) && isset($info[$type])) {
                return $info[$type];
            } else {
                return array('error' => '요청하신 타입의 정보가 없습니다.');
            }
        }

        return $info;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 템플릿 관리 팝업 URL
 */
function get_popbill_template_manage_URL(){
    global $corpnum, $userID;

    try {
        $KakaoService = get_kakao_service_instance();
        $url = $KakaoService->GetATSTemplateMgtURL($corpnum, $userID);

        if (empty($url)) {
            return array('error' => '템플릿관리 URL을 가져올 수 없습니다.');
        }

        return $url;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 플러스친구 관리 팝업 URL
 */
function get_popbill_plusfriend_manage_URL(){
    global $corpnum, $userID;
    try {
        $KakaoService = get_kakao_service_instance();
        $url = $KakaoService->GetPlusFriendMgtURL($corpnum, $userID);
        if (empty($url)) {
            return array('error' => '플러스친구 관리 URL을 가져올 수 없습니다.');
        }
        return $url;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 전송내역 관리 팝업 URL
 */ 
function get_popbill_send_manage_URL(){
    global $corpnum, $userID;
    try {
        $KakaoService = get_kakao_service_instance();
        $url = $KakaoService->GetSentListURL($corpnum, $userID);
        if (empty($url)) {
            return array('error' => '전송내역 URL을 가져올 수 없습니다.');
        }
        return $url;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/**
 * 발신번호 등록 팝업 URL
 */
function get_popbill_sender_number_URL(){
    global $corpnum, $userID;
    try {
        $KakaoService = get_kakao_service_instance();
        $url = $KakaoService->GetSenderNumberMgtURL($corpnum, $userID);
        if (empty($url)) {
            return array('error' => '발신번호 등록 URL을 가져올 수 없습니다.');
        }
        return $url;
    } catch (Exception $e) {
        return array('error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(), 'code' => $e->getCode());
    }
}

/*************************************************************************
**
**  알림톡 : 팝빌 카카오톡 발송
**
*************************************************************************/
/**
 * 팝빌 알림톡 전송 함수 (SendATS 파라미터를 배열에서 바로 전달, 예외처리 포함)
 */
function send_popbill_alimtalk($params = []){
    global $corpnum, $userID;

    try {
        $KakaoService = get_kakao_service_instance();

        $receipt_num = $KakaoService->SendATS(
            $corpnum,
            $params['template_code'],
            $params['sender_hp'],
            $params['content'],
            isset($params['alt_content']) ? $params['alt_content'] : '',
            isset($params['alt_send']) ? $params['alt_send'] : null,
            $params['messages'],
            isset($params['reserveDT']) ? $params['reserveDT'] : null,
            $userID,
            isset($params['request_num']) ? $params['request_num'] : null,
            isset($params['buttons']) ? $params['buttons'] : null,
            isset($params['alt_subject']) ? $params['alt_subject'] : ''
        );

        if ($receipt_num) {
            return $receipt_num;
        } else {
            return [ 'error' => '알림톡 전송에 실패했습니다.' ];
        }
    } catch (Exception $e) {
        return [
            'error' => '팝빌 서비스 처리 중 오류가 발생했습니다: ' . $e->getMessage(),
            'code' => $e->getCode()
        ];
    }
}