<?php
$sub_menu = '600510';
include_once './_common.php';

auth_check_menu($auth, $sub_menu, 'r');

$data = unserialize(base64_decode(get_subs_option('su_holiday_settings')));
$data = is_array($data) ? $data : array();

if ($mode === 'add') {
    $title = isset($_POST['title']) ? clean_xss_tags(trim($_POST['title']), 1, 1) : '';
    $date = isset($_POST['date']) ? trim($_POST['date']) : '';
    $type = ($_POST['type'] === 'w') ? 'w' : 'h';
    
    $pattern = '/^\d{4}-\d{2}-\d{2}$/';
    
    if (!preg_match($pattern, $date)) {
        die('날짜형식에 맞지 않습니다.');
    }
    
    // 중복된 날짜가 이미 있는지 확인
    $exists = false;
    foreach ($data as $item) {
        if ($item['date'] === $date) {
            $exists = true;
            break;
        }
    }
    
    if ($exists) {
        die('날짜마다 1개만 등록할수 있습니다.');
    }
    
    // ID 생성 (가장 큰 ID + 1)
    $new_id = 1;
    if (!empty($data)) {
        $ids = array_column($data, 'id');
        $new_id = max($ids) + 1;
    }

    $data[] = array(
        'id' => $new_id,
        'title' => $title,
        'date' => $date,
        'type' => $type
    );

    $encoded = base64_encode(serialize($data));
    sql_query("UPDATE `{$g5['g5_subscription_config_table']}` SET su_holiday_settings = '{$encoded}'");
    echo 'ok';

} elseif ($mode === 'delete') {
    $id = (int)$_POST['id'];

    $data = array_filter($data, function ($item) use ($id) {
        return $item['id'] != $id;
    });

    $encoded = base64_encode(serialize(array_values($data))); // 인덱스 재정렬
    sql_query("UPDATE `{$g5['g5_subscription_config_table']}` SET su_holiday_settings = '{$encoded}'");
    echo 'deleted';

} else {
    echo 'invalid mode';
}
