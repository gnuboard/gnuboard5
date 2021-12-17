<?php
$sub_menu = '200100';
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

function only_number($n) {
    return preg_replace('/[^0-9]/', '', $n);
}

// 엑셀 데이터가 많은 경우 대비 설정변경
set_time_limit ( 0 );
ini_set('memory_limit', '50M');

auth_check_menu($auth, $sub_menu, "w");

$admin_password = $_POST['admin_password'];

if (empty($admin_password)) {
    alert("관리자 비밀번호를 입력해주세요.");
}

if ($member['mb_level'] != 10) {
    alert("최고관리자만 이용가능합니다.");
}

$is_upload_file = (isset($_FILES['excelfile']['tmp_name']) && $_FILES['excelfile']['tmp_name']) ? 1 : 0;

if (!$is_upload_file){
    alert("엑셀 파일을 업로드해 주세요.");
}

if ($is_upload_file) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheet = $objPHPExcel->getSheet(0);

    $num_rows = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    $range_str = "A"."3".":".$highestColumn.$num_rows;
    $datas = $sheet->rangeToArray($range_str, null, true, false);

    $succ_count = 0;
    $fail_count = 0;
    $total_count = 0;

    $fail_array = array();
    
    foreach($datas as $key => $var) {
        try {

            $null_array = array_filter($var);
            if(empty($null_array)) continue;

            $insert_array = array();
            $mb_id = addslashes($var[1]);
            $mb_name = addslashes($var[2]);
            $mb_nick = addslashes($var[3]);
            $mb_nick_date = addslashes($var[4]);
            $mb_level = addslashes($var[5]);
            $status = addslashes($var[6]);
            $mb_point = addslashes($var[7]);
            $mb_email = addslashes($var[8]);
            $mb_homepage = addslashes($var[9]);
            $mb_hp_text = addslashes($var[10]);
            $mb_tel = addslashes($var[11]);
            $mb_certify_text = addslashes($var[12]);
            $mb_adult = addslashes($var[13]);
            $mb_email_certify = addslashes($var[14]);
            $zip_code = addslashes($var[15]);
            $mb_addr1 = addslashes($var[16]);
            $mb_addr2 = addslashes($var[17]);
            $mb_addr3 = addslashes($var[18]);
            $mb_addr_jibeon_text = addslashes($var[19]);
            $mb_mailling_text = addslashes($var[20]);
            $mb_sms_text = addslashes($var[21]);
            $mb_open_text = addslashes($var[22]);
            $mb_open_date = addslashes($var[23]);
            $mb_signature = addslashes($var[24]);
            $mb_profile = addslashes($var[25]);
            $mb_memo = addslashes($var[26]);
            $mb_datetime = addslashes($var[27]);
            $mb_today_login = addslashes($var[28]);
            $mb_ip = addslashes($var[29]);
            $mb_leave_date = addslashes($var[30]);
            $mb_intercept_date = addslashes($var[31]);
            $mb_recommend = addslashes($var[32]);
            $mb_1 = addslashes($var[33]);
            $mb_2 = addslashes($var[34]);
            $mb_3 = addslashes($var[35]);
            $mb_4 = addslashes($var[36]);
            $mb_5 = addslashes($var[37]);
            $mb_6 = addslashes($var[38]);
            $mb_7 = addslashes($var[39]);
            $mb_8 = addslashes($var[40]);
            $mb_9 = addslashes($var[41]);
            $mb_10 = addslashes($var[42]);

            // 입력할 시간
            $input_date = date('Y-m-d', time());

            // 아이디 유효성 체크
            if (empty($mb_id)) throw new Exception("아이디 미입력 오류");
            $result = sql_fetch("SELECT count(*) as `cnt` FROM `{$g5['member_table']}` WHERE `mb_id` = \"{$var[1]}\"");
            if ($result['cnt'] > 0) throw new Exception("아이디 중복 오류");

            // 이름 유효성 체크
            if (empty($mb_name)) throw new Exception("이름 미입력 오류");

            // 닉네임 유효성 체크
            if (empty_mb_nick($mb_nick) != "") throw new Exception("닉네임 미입력 오류");
            if (valid_mb_nick($mb_nick) != "") throw new Exception("닉네임 유효성 오류(공백없이 한글, 영문, 숫자)");
            if (count_mb_nick($mb_nick) != "") throw new Exception("닉네임 유효성 오류(한글 2글자, 영문 4글자 이상 입력)");
            if (exist_mb_nick($mb_nick, $mb_id) != "") throw new Exception("닉네임 중복 오류");
            if (reserve_mb_nick($mb_nick) != "") throw new Exception("예약어로 등록된 닉네임 등록 오류");
            
            if ($mb_nick_date == "") { 
                $mb_nick_date = date('Y-m-d', time());
            } else {
                print_r2($mb_nick_date);
                if (preg_match('/^\d+$/', $mb_nick_date) == false) throw new Exception("닉네임 등록날짜 오류");
                $mb_nick_date = date('Y-m-d', ($mb_nick_date - 25569) * 86400);
            }

            if (empty($mb_level)) throw new Exception("회원 권한 미입력 오류");
            if (preg_match('/^\d+$/', $mb_level) == false) throw new Exception("회원권한 유효성 오류(숫자만 입력)");
            if ($mb_level > 10 || $mb_level < 1) throw new Exception("회원권한 유효성 오류(회원권한은 1~10)");

            if (empty($mb_point)) $mb_point = 0;
            if (preg_match('/^\d+$/', $mb_point) == false) throw new Exception("포인트 유효성 오류(숫자만 입력)");

            // 이메일 유효성 체크
            if (empty_mb_email($mb_email) != "") throw new Exception("닉네임 미입력 오류");
            if (valid_mb_email($mb_email) != "") throw new Exception("이메일 유효성 오류(E-mail 주소 형식이 아님)");
            if (prohibit_mb_email($mb_email) != "") throw new Exception("금지 메일 도메인 입력 오류");
            if (exist_mb_email($mb_email, $mb_id) != "") throw new Exception("이메일 중복 오류");

            // 휴대폰번호 유효성 체크
            $mb_hp = "";
            if (!empty($mb_hp_text)) {
                $mb_hp = hyphen_hp_number($mb_hp_text);
                if (valid_mb_hp($mb_hp) != "") throw new Exception("휴대폰번호 유효성 오류");
                if (exist_mb_hp($mb_hp, $mb_id) != "") throw new Exception("휴대폰번호 중복 오류");
            }

            // 본인확인 유효성 체크
            $mb_certify = "";
            if ($mb_certify_text != "") {
                switch ($mb_certify_text) {
                    case "관리자":
                        $mb_certify = "admin";
                        break;
                    case "아이핀":
                        $mb_certify = "ipin";
                        break;
                    case "휴대폰":
                        $mb_certify = "hp";
                        break;
                    default:
                        $mb_certify = "";
                        break;
                }
            }

            // 성인인증 유효성 검사
            if (!empty($mb_adult)) {
                if ($mb_adult != 'Y' && $mb_adult != 'N') throw new Exception("성인인증 입력값 오류");
            } else {
                $mb_adult = 'N';
            }

            // 메일일증 유효성 검사
            if (!empty($mb_email_certify)) {
                if ($mb_email_certify != 'Y' && $mb_email_certify != 'N') throw new Exception("메일인증 입력값 오류");
            } else {
                $mb_email_certify = 'N';
            }

            // 우편번호 유효성 검사
            // 기본주소와 우편번호는 세트로 움직이므로 2가지 값을 함께 검사
            // 우편번호, 기본주소, 상세주소는 하나이상의 공백이 생길시 모두 공백으로 처리 (2021-12-14)
            if (empty($zip_code) || empty($mb_addr1) || empty($mb_addr2)) {
                $mb_addr1 = "";
                $mb_addr2 = "";
                $mb_addr3 = "";
                $mb_addr_jibeon = "";
                $mb_zip1 = "";
                $mb_zip2 = "";
            } else {
                if (preg_match('/^\d+$/', $zip_code) == false) throw new Exception("우편번호 유효성검사 오류");
                $mb_zip1 = substr($zip_code, 0, 3);
                $mb_zip2 = substr($zip_code, 3);

                // 지번도 함께 처리
                switch ($mb_addr_jibeon_text) {
                    case 'Y':
                        $mb_addr_jibeon = 'J';
                        break;
                    case 'N':
                    default:
                        $mb_addr_jibeon = "";
                        break;
                }
            }

            // 메일수신동의 검사
            if(!empty($mb_mailling_text)) {
                if($mb_mailling_text != 'Y' && $mb_mailling_text != 'N') throw new Exception("메일수신동의 입력값 오류");
            } else {
                $mb_mailling_text = 'N';
            }

            switch ($mb_mailling_text) {
                case 'Y':
                    $mb_mailling = 1;
                    break;
                case 'N':
                default:
                    $mb_mailling = 0;
                    break;
            }

            // SMS수신동의 검사
            if(!empty($mb_sms_text)) {
                if($mb_sms_text != 'Y' && $mb_sms_text != 'N') throw new Exception("SMS수신동의 입력값 오류");
            } else {
                $mb_sms_text = 'N';
            }

            switch ($mb_sms_text) {
                case 'Y':
                    $mb_sms = 1;
                    break;
                case 'N':
                default:
                    $mb_sms = 0;
                    break;
            }

            if(empty($mb_open_text)) throw new Exception("정보공개 미입력 오류");
            if($mb_open_text != 'Y' && $mb_open_text != 'N') throw new Exception("정보공개 입력값 오류");

            // 정보공개 검사
            switch ($mb_open_text) {
                case 'Y':
                    $mb_open = 1;
                    if (empty($mb_open_date)) {
                        $mb_open_date = $input_date;
                    } else {
                        if (preg_match('/^\d+$/', $mb_open_date) == false) throw new Exception("정보공개일 날짜형식 오류");
                        $mb_open_date = date('Y-m-d', ($mb_open_date - 25569) * 86400);
                    }
                    break;
                case 'N':
                default:
                    $mb_open = 0;
                    break;
            }

            // 회원가입일 검사
            if (empty($mb_datetime)) {
                $mb_datetime = $input_date;
            } else {
                if (preg_match('/^\d*(\.?\d*)$/', $mb_datetime) == false) throw new Exception("회원가입일 날짜형식 오류");
                $mb_datetime = date('Y-m-d h:i:s', ($mb_datetime - 25569) * 86400);
            }

            // 최근접속일 검사
            if (!empty($mb_today_login)) {
                if (preg_match('/^\d*(\.?\d*)$/', $mb_today_login) == false) throw new Exception("최근접속일 날짜형식 오류");
                $mb_today_login = date('Y-m-d h:i:s', ($mb_today_login - 25569) * 86400);
            }

            // IP 유효성 체크
            if (!empty($mb_ip)) {
                if (preg_match('/^[\d.]+$/', $mb_ip) == false || substr_count($mb_ip, ".") != 3) throw new Exception("올바르지 않은 IP형식");
            }

            // 탈퇴일자 유효성체크
            if (!empty($mb_leave_date)) {
                if (preg_match('/^\d+$/', $mb_leave_date) == false) throw new Exception("탈퇴일자 날짜형식 오류(숫자만)");
                if (strlen($mb_leave_date) != 8) throw new Exception("틸퇴일자 날짜형식 오류(YYmmdd)");
            }

            // 접근차단일자
            if (!empty($mb_intercept_date)) {
                if (preg_match('/^\d+$/', $mb_intercept_date) == false) throw new Exception("접근차단일자 날짜형식 오류(숫자만)");
                if (strlen($mb_intercept_date) != 8) throw new Exception("접근차단일자 날짜형식 오류(YYmmdd)");
            }

            // 추천인 체크
            if (!empty($mb_recommend)) {
                if($mb_id == $mb_recommend) throw new Exception("추천인과 회원아이디 동일 오류");
                
                $result = sql_fetch("SELECT * FROM `{$g5['member_table']}` WHERE `mb_id` = \"{$mb_recommend}\"");
                if($result != false) {
                    if(!empty($result['mb_leave_date']) || !empty($result['mb_intercept_date'])) throw new Exception("추천인의 회원상태 오류(탈퇴 or 차단)");
                } else {
                    throw new Exception("추천인이 회원목록에 존재하지 않음");
                }
            }

            $sql = "INSERT INTO `{$g5['member_table']}` SET 
                        `mb_id`             = '{$mb_id}',
                        `mb_name`           = '{$mb_name}',
                        `mb_nick`           = '{$mb_nick}',
                        `mb_nick_date`      = '{$mb_nick_date}',
                        `mb_level`          = '{$mb_level}',
                        `mb_point`          = '{$mb_point}',
                        `mb_email`          = '{$mb_email}',
                        `mb_homepage`       = '{$mb_homepage}',
                        `mb_hp`             = '{$mb_hp}',
                        `mb_tel`            = '{$mb_tel}',
                        `mb_certify`        = '{$mb_certify}',
                        `mb_adult`          = '{$mb_adult}',
                        `mb_email_certify`  = '{$mb_email_certify}',
                        `mb_addr1`          = '{$mb_addr1}',
                        `mb_addr2`          = '{$mb_addr2}',
                        `mb_addr3`          = '{$mb_addr3}',
                        `mb_addr_jibeon`    = '{$mb_addr_jibeon}',
                        `mb_mailling`       = '{$mb_mailling}',
                        `mb_sms`            = '{$mb_sms}',
                        `mb_open`           = '{$mb_open}',
                        `mb_open_date`      = '{$mb_open_date}',
                        `mb_signature`      = '{$mb_signature}',
                        `mb_profile`        = '{$mb_profile}',
                        `mb_memo`           = '{$mb_memo}',
                        `mb_datetime`       = '{$mb_datetime}',
                        `mb_today_login`    = '{$mb_today_login}',
                        `mb_ip`             = '{$mb_ip}',
                        `mb_leave_date`     = '{$mb_leave_date}',
                        `mb_intercept_date` = '{$mb_intercept_date}',
                        `mb_recommend`      = '{$mb_recommend}',
                        `mb_1`              = '{$mb_1}',
                        `mb_2`              = '{$mb_2}',
                        `mb_3`              = '{$mb_3}',
                        `mb_4`              = '{$mb_4}',
                        `mb_5`              = '{$mb_5}',
                        `mb_6`              = '{$mb_6}',
                        `mb_7`              = '{$mb_7}',
                        `mb_8`              = '{$mb_8}',
                        `mb_9`              = '{$mb_9}',
                        `mb_10`             = '{$mb_10}'
                        ";

            $result = @sql_query($sql);
            if($result == null || $result == false) throw new Exception("DB insert 오류");

            $mb_no = sql_insert_id();

            // 추천인 포인트 관련 프로세스
            if ($config['cf_use_recommend'] && $config['cf_recommend_point'] > 0 && !empty($mb_recommend)) {
                insert_point($mb_recommend, $config['cf_recommend_point'], $mb_id.'의 추천인', '@member', $mb_recommend, $mb_id.' 추천');
            }

            include_once(G5_LIB_PATH.'/mailer.lib.php');

            // 임시 비밀번호 랜덤 생성
            $change_password = rand(100000, 999999);
            $mb_lost_certify = get_encrypt_string($change_password);

            // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
            $mb_nonce = md5(pack('V*', rand(), rand(), rand(), rand()));

            // 임시비밀번호와 난수를 mb_lost_certify 필드에 저장
            $sql = " update {$g5['member_table']} set mb_lost_certify = '$mb_nonce $mb_lost_certify' where mb_id = '{$mb_id}' ";
            $result = @sql_query($sql);

            if($result == null || $result == false) throw new Exception("임시비밀번호 저장 오류");

            $href = G5_BBS_URL.'/password_lost_certify.php?mb_no='.$mb_no.'&amp;mb_nonce='.$mb_nonce;

            $subject = "[".$config['cf_title']."] 임시비밀번호 발급 안내 메일입니다.";

            $content = "";

            $content .= '<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">';
            $content .= '<div style="border:1px solid #dedede">';
            $content .= '<h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">';
            $content .= '임시비밀번호 발급 안내';
            $content .= '</h1>';
            $content .= '<span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">';
            $content .= '<a href="'.G5_URL.'" target="_blank">'.$config['cf_title'].'</a>';
            $content .= '</span>';
            $content .= '<p style="margin:20px 0 0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
            $content .= addslashes($mb_name)." (".addslashes($mb_nick).")"." 회원님은 ".G5_TIME_YMDHIS." 에 임시 비밀번호가 발급되었습니다.<br>";
            $content .= '저희 사이트는 계정등록과 관련하여 관리자라도 회원님의 비밀번호를 임의적으로 적용할 수 없기때문에, 임시비밀번호를 생성하여 안내 해드리고 있습니다.<br>';
            $content .= '아래에서 임시비밀번호를 확인하신 후, <span style="color:#ff3061"><strong>비밀번호 변경</strong> 링크를 클릭 하십시오.</span><br>';
            $content .= '비밀번호가 변경되었다는 인증 메세지가 출력되면, 홈페이지에서 회원아이디와 변경된 비밀번호를 입력하시고 로그인 하십시오.<br>';
            $content .= '로그인 후에는 정보수정 메뉴에서 새로운 비밀번호로 변경해 주십시오.';
            $content .= '</p>';
            $content .= '<p style="margin:0;padding:30px 30px 30px;border-bottom:1px solid #eee;line-height:1.7em">';
            $content .= '<span style="display:inline-block;width:100px">회원아이디</span> '.$mb_id.'<br>';
            $content .= '<span style="display:inline-block;width:100px">변경될 비밀번호</span> <strong style="color:#ff3061">'.$change_password.'</strong>';
            $content .= '</p>';
            $content .= '<a href="'.$href.'" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">비밀번호 변경</a>';
            $content .= '</div>';
            $content .= '</div>';

            mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);
            
            // 해당 event를 찾을 수 없음
            // run_event('password_lost2_after', $mb, $mb_nonce, $mb_lost_certify);

            $succ_count++;
        } catch(Exception $e) {
            $data = array();
            $fail_array[$key]['row'] = 'A'.($key + 3);
            $fail_array[$key]['message'] = $e->getMessage();

            $fail_count++;
        }

        $total_count++;
    }
}

$g5['title'] = '엑셀 회원 데이터 업로드 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <dl id="excelfile_result">
        <dt>총 데이터수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt>등록 성공</dt>
        <dd><?php echo number_format($succ_count); ?></dd>
        <dt>등록 실패</dt>
        <dd><?php echo number_format($fail_count); ?></dd>
        <?php if($fail_count > 0) { ?>
            <dt class="fail_log">등록 실패 사유</dt>
            <?php foreach($fail_array as $key => $var) { ?>
                <dd class="fail_log"><?php echo $var['row'].' -> '.$var['message']; ?></dd>
            <?php } ?>
        <?php } ?>
    </dl>

    <div class="btn_win01 btn_win">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>
</div>

<?php
include_once(G5_PATH.'/tail.sub.php');