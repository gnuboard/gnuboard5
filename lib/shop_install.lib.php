<?php
if (!defined('_GNUBOARD_')) exit;

function g5_shop_install_prefix()
{
    return G5_TABLE_PREFIX . 'shop_';
}

function g5_shop_install_table_names($prefix)
{
    return array(
        'banner', 'cart', 'category', 'coupon', 'coupon_log', 'coupon_zone', 'default',
        'event', 'event_item', 'item', 'item_option', 'item_use', 'item_qa', 'item_relation',
        'order', 'order_address', 'order_data', 'order_delete', 'personalpay', 'sendcost',
        'wish', 'item_stocksms', 'order_post_log', 'inicis_log', 'order_cancel_log',
        'inicis_pay', 'inicis_pay_event'
    );
}

function g5_shop_install_existing_tables($prefix)
{
    $tables = array();
    $result = sql_query('SHOW TABLES', false);
    if (!$result) {
        return false;
    }
    while ($row = sql_fetch_array($result)) {
        $table = reset($row);
        if (is_string($table) && strpos($table, $prefix) === 0) {
            $tables[] = $table;
        }
    }
    return $tables;
}

function g5_shop_install_finish($lock_name, $success, $error)
{
    sql_query("SELECT RELEASE_LOCK('" . sql_real_escape_string($lock_name) . "')", false);
    return array('success' => $success, 'error' => $error);
}

function g5_shop_install_config_block($prefix)
{
    $map = array(
        'default', 'banner', 'cart', 'category', 'event', 'event_item', 'item',
        'item_option', 'item_use', 'item_qa', 'item_relation', 'order', 'order_delete',
        'wish', 'coupon', 'coupon_zone', 'coupon_log', 'sendcost', 'personalpay',
        'order_address', 'item_stocksms', 'post_log' => 'order_post_log',
        'order_data', 'inicis_log', 'inicis_pay', 'inicis_pay_event', 'order_cancel_log'
    );
    $block = "\n\ndefine('G5_USE_SHOP', true);\n\ndefine('G5_SHOP_TABLE_PREFIX', '" . addcslashes($prefix, "\\'") . "');\n\n";
    foreach ($map as $key => $value) {
        if (is_int($key)) {
            $key = $value;
        }
        $block .= "\$g5['g5_shop_" . $key . "_table'] = G5_SHOP_TABLE_PREFIX.'" . $value . "';\n";
    }
    return $block;
}

function g5_shop_install_write_config($prefix, $file = '')
{
    if ($file === '') {
        $file = G5_DATA_PATH . '/' . G5_DBCONFIG_FILE;
    }
    $contents = @file_get_contents($file);
    if ($contents === false || strpos($contents, "define('G5_USE_SHOP'") !== false) {
        return 'DB 설정 파일을 읽을 수 없거나 쇼핑몰 설정이 이미 존재합니다.';
    }
    $closing = strrpos($contents, '?>');
    $updated = $closing === false
        ? $contents . g5_shop_install_config_block($prefix)
        : substr($contents, 0, $closing) . g5_shop_install_config_block($prefix) . "?>" . substr($contents, $closing + 2);
    $temp = @tempnam(dirname($file), 'shop-config-');
    if (!$temp) {
        return 'DB 설정 임시 파일을 만들 수 없습니다.';
    }
    $written = @file_put_contents($temp, $updated, LOCK_EX);
    if ($written === false || $written !== strlen($updated)) {
        @unlink($temp);
        return 'DB 설정 임시 파일을 저장하지 못했습니다.';
    }
    @chmod($temp, G5_FILE_PERMISSION);
    if (!@rename($temp, $file)) {
        @unlink($temp);
        return 'DB 설정 파일을 교체하지 못했습니다.';
    }
    return '';
}

function g5_shop_install_default($table)
{
    $sql = "INSERT INTO `{$table}` SET
        de_admin_company_name = '회사명', de_admin_company_saupja_no = '123-45-67890',
        de_admin_company_owner = '대표자명', de_admin_company_tel = '02-123-4567',
        de_admin_company_fax = '02-123-4568', de_admin_tongsin_no = '제 OO구 - 123호',
        de_admin_buga_no = '12345호', de_admin_company_zip = '123-456',
        de_admin_company_addr = 'OO도 OO시 OO구 OO동 123-45', de_admin_info_name = '정보책임자명',
        de_admin_info_email = '정보책임자 E-mail', de_shop_skin = 'basic', de_shop_mobile_skin = 'basic',
        de_type1_list_use = '1', de_type1_list_skin = 'main.10.skin.php', de_type1_list_mod = '5', de_type1_list_row = '1', de_type1_img_width = '160', de_type1_img_height = '160',
        de_type2_list_use = '1', de_type2_list_skin = 'main.20.skin.php', de_type2_list_mod = '4', de_type2_list_row = '1', de_type2_img_width = '215', de_type2_img_height = '215',
        de_type3_list_use = '1', de_type3_list_skin = 'main.40.skin.php', de_type3_list_mod = '4', de_type3_list_row = '1', de_type3_img_width = '215', de_type3_img_height = '215',
        de_type4_list_use = '1', de_type4_list_skin = 'main.50.skin.php', de_type4_list_mod = '5', de_type4_list_row = '1', de_type4_img_width = '215', de_type4_img_height = '215',
        de_type5_list_use = '1', de_type5_list_skin = 'main.30.skin.php', de_type5_list_mod = '4', de_type5_list_row = '1', de_type5_img_width = '215', de_type5_img_height = '215',
        de_mobile_type1_list_use = '1', de_mobile_type1_list_skin = 'main.30.skin.php', de_mobile_type1_list_mod = '2', de_mobile_type1_list_row = '4', de_mobile_type1_img_width = '230', de_mobile_type1_img_height = '230',
        de_mobile_type2_list_use = '1', de_mobile_type2_list_skin = 'main.10.skin.php', de_mobile_type2_list_mod = '2', de_mobile_type2_list_row = '2', de_mobile_type2_img_width = '230', de_mobile_type2_img_height = '230',
        de_mobile_type3_list_use = '1', de_mobile_type3_list_skin = 'main.10.skin.php', de_mobile_type3_list_mod = '2', de_mobile_type3_list_row = '4', de_mobile_type3_img_width = '300', de_mobile_type3_img_height = '300',
        de_mobile_type4_list_use = '1', de_mobile_type4_list_skin = 'main.20.skin.php', de_mobile_type4_list_mod = '2', de_mobile_type4_list_row = '2', de_mobile_type4_img_width = '80', de_mobile_type4_img_height = '80',
        de_mobile_type5_list_use = '1', de_mobile_type5_list_skin = 'main.10.skin.php', de_mobile_type5_list_mod = '2', de_mobile_type5_list_row = '2', de_mobile_type5_img_width = '230', de_mobile_type5_img_height = '230',
        de_bank_use = '1', de_bank_account = 'OO은행 12345-67-89012 예금주명', de_vbank_use = '0', de_iche_use = '0', de_card_use = '0',
        de_settle_min_point = '5000', de_settle_max_point = '50000', de_settle_point_unit = '100',
        de_cart_keep_term = '15', de_card_point = '0', de_point_days = '7', de_pg_service = 'kcp', de_kcp_mid = '',
        de_send_cost_case = '차등', de_send_cost_limit = '20000;30000;40000', de_send_cost_list = '4000;3000;2000',
        de_hope_date_use = '0', de_hope_date_after = '3', de_baesong_content = '배송 안내 입력전입니다.', de_change_content = '교환/반품 안내 입력전입니다.',
        de_rel_list_use = '1', de_rel_list_skin = 'relation.10.skin.php', de_rel_list_mod = '5', de_rel_img_width = '215', de_rel_img_height = '215',
        de_mobile_rel_list_use = '1', de_mobile_rel_list_skin = 'relation.10.skin.php', de_mobile_rel_list_mod = '3', de_mobile_rel_img_width = '230', de_mobile_rel_img_height = '230',
        de_search_list_skin = 'list.10.skin.php', de_search_img_width = '225', de_search_img_height = '225', de_search_list_mod = '5', de_search_list_row = '5',
        de_mobile_search_list_skin = 'list.10.skin.php', de_mobile_search_img_width = '230', de_mobile_search_img_height = '230', de_mobile_search_list_mod = '2', de_mobile_search_list_row = '5',
        de_listtype_list_skin = 'list.10.skin.php', de_listtype_img_width = '225', de_listtype_img_height = '225', de_listtype_list_mod = '5', de_listtype_list_row = '5',
        de_mobile_listtype_list_skin = 'list.10.skin.php', de_mobile_listtype_img_width = '230', de_mobile_listtype_img_height = '230', de_mobile_listtype_list_mod = '2', de_mobile_listtype_list_row = '5',
        de_simg_width = '230', de_simg_height = '230', de_mimg_width = '300', de_mimg_height = '300',
        de_item_use_use = '1', de_level_sell = '1', de_code_dup_use = '1', de_card_test = '1',
        de_sms_cont1 = '{이름}님의 회원가입을 축하드립니다.\nID:{회원아이디}\n{회사명}',
        de_sms_cont2 = '{이름}님 주문해주셔서 고맙습니다.\n{주문번호}\n{주문금액}원\n{회사명}',
        de_sms_cont3 = '{이름}님께서 주문하셨습니다.\n{주문번호}\n{주문금액}원\n{회사명}',
        de_sms_cont4 = '{이름}님 입금 감사합니다.\n{입금액}원\n주문번호:\n{주문번호}\n{회사명}',
        de_sms_cont5 = '{이름}님 배송합니다.\n택배:{택배회사}\n운송장번호:\n{운송장번호}\n{회사명}'";
    return sql_query($sql, false) ? '' : sql_error_info();
}

function g5_shop_install_run($options = array())
{
    $allow_enabled = isset($options['allow_enabled']) && $options['allow_enabled'];
    if (!$allow_enabled && defined('G5_USE_SHOP') && G5_USE_SHOP) {
        return array('success' => false, 'error' => '쇼핑몰이 이미 설치되어 있습니다.');
    }
    $prefix = isset($options['prefix']) && $options['prefix'] !== '' ? $options['prefix'] : g5_shop_install_prefix();
    $data_path = isset($options['data_path']) && $options['data_path'] !== '' ? $options['data_path'] : G5_DATA_PATH;
    $config_file = isset($options['config_file']) ? $options['config_file'] : '';
    $lock_name = 'g5_shop_install_' . substr(md5(G5_TABLE_PREFIX), 0, 16);
    $lock = sql_fetch("SELECT GET_LOCK('{$lock_name}', 10) AS acquired", false);
    if (!$lock || (int) $lock['acquired'] !== 1) {
        return array('success' => false, 'error' => '다른 쇼핑몰 설치 작업이 실행 중이거나 실행 잠금을 얻지 못했습니다.');
    }
    $existing = g5_shop_install_existing_tables($prefix);
    if ($existing === false) {
        return g5_shop_install_finish($lock_name, false, '쇼핑몰 테이블 존재 여부를 확인하지 못했습니다.');
    }
    if ($existing) {
        return g5_shop_install_finish($lock_name, false, '같은 접두어의 쇼핑몰 테이블이 이미 존재합니다: ' . implode(', ', $existing));
    }

    include_once G5_PATH . '/install/install.function.php';
    $queries = install_load_sql_file(G5_PATH . '/install/gnuboard5shop.sql', 'g5_shop_', $prefix);
    if ($queries === false) {
        return g5_shop_install_finish($lock_name, false, '쇼핑몰 설치 SQL을 읽을 수 없습니다.');
    }
    $created = array();
    foreach ($queries as $sql) {
        $sql = get_db_create_replace($sql);
        if (!sql_query($sql, false)) {
            $error = sql_error_info();
            foreach (array_reverse($created) as $table) {
                sql_query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`", false);
            }
            return g5_shop_install_finish($lock_name, false, '쇼핑몰 테이블 생성에 실패했습니다: ' . $error);
        }
        if (preg_match('/^CREATE TABLE(?: IF NOT EXISTS)?\s+`([^`]+)`/i', trim($sql), $matches)) {
            $created[] = $matches[1];
        }
    }

    $error = g5_shop_install_default($prefix . 'default');
    if ($error === '') {
        $dirs = array('banner', 'common', 'event', 'item');
        foreach ($dirs as $dir) {
            if (!install_ensure_dir($data_path . '/' . $dir)) {
                $error = '쇼핑몰 데이터 디렉터리를 만들 수 없습니다: data/' . $dir;
                break;
            }
        }
    }
    if ($error === '') {
        $copies = array(
            array(G5_PATH . '/install/logo_img', $data_path . '/common/logo_img'),
            array(G5_PATH . '/install/logo_img', $data_path . '/common/logo_img2'),
            array(G5_PATH . '/install/mobile_logo_img', $data_path . '/common/mobile_logo_img'),
            array(G5_PATH . '/install/mobile_logo_img', $data_path . '/common/mobile_logo_img2')
        );
        foreach ($copies as $copy) {
            if (!file_exists($copy[1]) && !@copy($copy[0], $copy[1])) {
                $error = '쇼핑몰 기본 이미지를 복사할 수 없습니다: ' . $copy[1];
                break;
            }
        }
    }
    if ($error === '') {
        $error = g5_shop_install_write_config($prefix, $config_file);
    }
    if ($error !== '') {
        foreach (array_reverse($created) as $table) {
            sql_query("DROP TABLE IF EXISTS `" . str_replace('`', '``', $table) . "`", false);
        }
        return g5_shop_install_finish($lock_name, false, $error);
    }
    return g5_shop_install_finish($lock_name, true, '');
}
