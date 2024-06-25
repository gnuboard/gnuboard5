<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// 회원 > 회원가입
// 작업 진행 중
$app->post('/members', function (Request $request, Response $response) {
    global $g5;

    $data = $request->getParsedBody();

    // 유효성 검사
    // 아이디
    // 이름
    // 닉네임
    // 이메일
    // 추천인

    // 비밀번호 확인 및 암호화
    if ($data['mb_password'] != $data['mb_password_re']) {
        $result = array(
            "message" => "비밀번호가 일치하지 않습니다."
        );
        $result_json = json_encode($result, JSON_UNESCAPED_UNICODE);

        $response->getBody()->write($result_json);
        return $response->withAddedHeader('Content-Type', 'application/json');
    }
    $data['mb_password'] = get_encrypt_string($data['mb_password']);
    unset($data['mb_password_re']);

    // 우편번호 분리
    $data['mb_zip1'] = substr($data['mb_zip'], 0, 3);
    $data['mb_zip2'] = substr($data['mb_zip'], 4, 3);
    unset($data['mb_zip']);

    // 기타 기본 가입정보 설정
    $data['mb_ip'] = $_SERVER['REMOTE_ADDR'];
    $data['mb_level'] = 1;  // 설정에서 불러와야 함.

    $query = "";
    foreach ($data as $key => $value) {
        $query .= "{$key} = '{$value}', ";
    }

    $sql = "INSERT INTO {$g5['member_table']} SET {$query} mb_datetime = NOW()";
    sql_query($sql);

    $result = array(
        "message" => "회원가입이 완료되었습니다.",
        "mb_id" => $data['mb_id'],
        "mb_name" => $data['mb_name'],
        "mb_nick" => $data['mb_nick']
    );
    $result_json = json_encode($result, JSON_UNESCAPED_UNICODE);

    $response->getBody()->write($result_json);
    return $response->withAddedHeader('Content-Type', 'application/json');
});