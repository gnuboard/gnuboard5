<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConfigController extends Controller
{
    public function index()
    {
        $config = Config::first();
        
        if (!$config) {
            // 기본 설정 생성
            $config = Config::create([
                'cf_title' => config('app.name'),
                'cf_admin' => 'admin',
                'cf_admin_email' => 'admin@example.com',
                'cf_admin_email_name' => '최고관리자',
                'cf_new_del' => 30,
                'cf_login_point' => 100,
                'cf_memo_del' => 180,
                'cf_visit_del' => 180,
                'cf_popular_del' => 180,
                'cf_page_rows' => 15,
                'cf_mobile_page_rows' => 15,
                'cf_write_pages' => 10,
                'cf_mobile_pages' => 5,
                'cf_new_skin' => 'basic',
                'cf_mobile_new_skin' => 'basic',
                'cf_search_skin' => 'basic',
                'cf_mobile_search_skin' => 'basic',
                'cf_connect_skin' => 'basic',
                'cf_mobile_connect_skin' => 'basic',
                'cf_faq_skin' => 'basic',
                'cf_mobile_faq_skin' => 'basic',
                'cf_register_level' => 2,
                'cf_register_point' => 1000,
                'cf_icon_level' => 2,
                'cf_member_icon_size' => 5000,
                'cf_member_icon_width' => 22,
                'cf_member_icon_height' => 22,
                'cf_member_img_size' => 50000,
                'cf_member_img_width' => 60,
                'cf_member_img_height' => 60,
                'cf_login_minutes' => 10,
                'cf_cut_name' => 15,
                'cf_nick_modify' => 60,
                'cf_new_rows' => 15,
                'cf_search_part' => 10000,
                'cf_email_use' => 1,
                'cf_email_wr_super_admin' => 0,
                'cf_email_wr_group_admin' => 0,
                'cf_email_wr_board_admin' => 0,
                'cf_email_wr_write' => 0,
                'cf_email_wr_comment_all' => 0,
                'cf_email_mb_super_admin' => 0,
                'cf_email_mb_member' => 0,
                'cf_email_po_super_admin' => 0,
                'cf_prohibit_id' => 'admin,administrator,관리자,운영자,어드민,주인장,webmaster,웹마스터,sysop,시삽,시샵,manager,매니저,메니저,root,루트,su,guest,방문객',
                'cf_prohibit_email' => '',
                'cf_new_del' => 30,
                'cf_memo_del' => 180,
                'cf_visit_del' => 180,
                'cf_popular_del' => 180,
                'cf_optimize_date' => date('Y-m-d'),
                'cf_use_member_icon' => 2,
                'cf_member_icon_size' => 5000,
                'cf_member_icon_width' => 22,
                'cf_member_icon_height' => 22,
                'cf_member_img_size' => 50000,
                'cf_member_img_width' => 60,
                'cf_member_img_height' => 60,
                'cf_login_minutes' => 10,
                'cf_cut_name' => 15,
                'cf_nick_modify' => 60,
                'cf_new_rows' => 15,
                'cf_search_part' => 10000,
                'cf_email_use' => 1,
                'cf_formmail_is_member' => 1,
                'cf_page_rows' => 15,
                'cf_mobile_page_rows' => 15,
                'cf_cert_use' => 0,
                'cf_cert_find' => 0,
                'cf_cert_ipin' => '',
                'cf_cert_hp' => '',
                'cf_cert_simple' => '',
                'cf_cert_kg_cd' => '',
                'cf_cert_kg_mid' => '',
                'cf_cert_kcb_cd' => '',
                'cf_cert_kcp_cd' => '',
                'cf_lg_mid' => '',
                'cf_lg_mert_key' => '',
                'cf_cert_limit' => 2,
                'cf_cert_req' => 0,
                'cf_sms_use' => '',
                'cf_sms_type' => '',
                'cf_icode_id' => '',
                'cf_icode_pw' => '',
                'cf_icode_server_ip' => '',
                'cf_icode_server_port' => '',
                'cf_icode_token_key' => '',
                'cf_googl_shorturl_apikey' => '',
                'cf_social_login_use' => 0,
                'cf_naver_clientid' => '',
                'cf_naver_secret' => '',
                'cf_facebook_appid' => '',
                'cf_facebook_secret' => '',
                'cf_google_clientid' => '',
                'cf_google_secret' => '',
                'cf_twitter_key' => '',
                'cf_twitter_secret' => '',
                'cf_kakao_rest_key' => '',
                'cf_kakao_client_secret' => '',
                'cf_kakao_js_apikey' => '',
                'cf_captcha' => 'kcaptcha',
                'cf_recaptcha_site_key' => '',
                'cf_recaptcha_secret_key' => '',
                'cf_read_point' => -1,
                'cf_write_point' => 10,
                'cf_comment_point' => 1,
                'cf_download_point' => -20,
                'cf_faq_skin' => 'basic',
                'cf_mobile_faq_skin' => 'basic',
                'cf_add_script' => '',
            ]);
        }

        return view('admin.config.index', compact('config'));
    }

    public function update(Request $request)
    {
        // 유효성 검증 규칙
        $rules = [
            'cf_title' => 'required|string|max:255',
            'cf_admin' => 'required|string|max:255',
            'cf_admin_email' => 'required|email|max:255',
            'cf_admin_email_name' => 'required|string|max:255',
            'cf_new_del' => 'required|integer|min:0',
            'cf_memo_del' => 'required|integer|min:0',
            'cf_visit_del' => 'required|integer|min:0',
            'cf_popular_del' => 'required|integer|min:0',
            'cf_login_point' => 'required|integer',
            'cf_cut_name' => 'required|integer|min:0',
            'cf_nick_modify' => 'required|integer|min:0',
            'cf_open_modify' => 'required|integer|min:0',
            'cf_new_rows' => 'required|integer|min:1',
            'cf_page_rows' => 'required|integer|min:1',
            'cf_mobile_page_rows' => 'required|integer|min:1',
            'cf_write_pages' => 'required|integer|min:1',
            'cf_mobile_pages' => 'required|integer|min:1',
            'cf_new_skin' => 'required|string|max:255',
            'cf_mobile_new_skin' => 'required|string|max:255',
            'cf_search_skin' => 'required|string|max:255',
            'cf_mobile_search_skin' => 'required|string|max:255',
            'cf_connect_skin' => 'nullable|string|max:255',
            'cf_mobile_connect_skin' => 'nullable|string|max:255',
            'cf_faq_skin' => 'nullable|string|max:255',
            'cf_mobile_faq_skin' => 'nullable|string|max:255',
            'cf_member_skin' => 'nullable|string|max:255',
            'cf_mobile_member_skin' => 'nullable|string|max:255',
            'cf_register_level' => 'required|integer|min:1|max:10',
            'cf_register_point' => 'required|integer',
            'cf_leave_day' => 'required|integer|min:0',
            'cf_search_part' => 'required|integer|min:0',
            'cf_icon_level' => 'required|integer|min:1|max:10',
            'cf_use_member_icon' => 'required|integer|in:0,1,2',
            'cf_member_icon_size' => 'required|integer|min:0',
            'cf_member_icon_width' => 'required|integer|min:0',
            'cf_member_icon_height' => 'required|integer|min:0',
            'cf_member_img_size' => 'required|integer|min:0',
            'cf_member_img_width' => 'required|integer|min:0',
            'cf_member_img_height' => 'required|integer|min:0',
            'cf_login_minutes' => 'required|integer|min:1',
            'cf_memo_send_point' => 'required|integer|min:0',
            'cf_recommend_point' => 'nullable|integer',
            'cf_prohibit_id' => 'nullable|string',
            'cf_prohibit_email' => 'nullable|string',
            'cf_stipulation' => 'nullable|string',
            'cf_privacy' => 'nullable|string',
            'cf_captcha' => 'required|string|in:kcaptcha,recaptcha,recaptcha_inv',
            'cf_recaptcha_site_key' => 'nullable|string|max:255',
            'cf_recaptcha_secret_key' => 'nullable|string|max:255',
            'cf_analytics' => 'nullable|string',
            'cf_add_meta' => 'nullable|string',
            'cf_add_script' => 'nullable|string',
            'cf_syndi_token' => 'nullable|string|max:255',
            'cf_syndi_except' => 'nullable|string|max:255',
            'cf_use_copy_log' => 'nullable|boolean',
            'cf_point_term' => 'required|integer|min:0',
            'cf_possible_ip' => 'nullable|string',
            'cf_intercept_ip' => 'nullable|string',
            'cf_filter' => 'nullable|string',
            'cf_delay_sec' => 'required|integer|min:0',
            'cf_link_target' => 'required|string|in:_self,_blank,_top,_new',
            'cf_read_point' => 'required|integer',
            'cf_write_point' => 'required|integer',
            'cf_comment_point' => 'required|integer',
            'cf_download_point' => 'required|integer',
            'cf_image_extension' => 'nullable|string',
            'cf_flash_extension' => 'nullable|string',
            'cf_movie_extension' => 'nullable|string',
            'cf_formmail_is_member' => 'nullable|boolean',
            'cf_social_login_use' => 'nullable|boolean',
            'cf_facebook_appid' => 'nullable|string|max:255',
            'cf_facebook_secret' => 'nullable|string|max:255',
            'cf_google_clientid' => 'nullable|string|max:255',
            'cf_google_secret' => 'nullable|string|max:255',
            'cf_naver_clientid' => 'nullable|string|max:255',
            'cf_naver_secret' => 'nullable|string|max:255',
            'cf_kakao_rest_key' => 'nullable|string|max:255',
            'cf_kakao_client_secret' => 'nullable|string|max:255',
            'cf_kakao_js_apikey' => 'nullable|string|max:255',
            'cf_twitter_key' => 'nullable|string|max:255',
            'cf_twitter_secret' => 'nullable|string|max:255',
            'cf_editor' => 'nullable|string|max:255',
            'cf_captcha_mp3' => 'nullable|string|max:255',
        ];

        // 여분필드 추가
        for ($i = 1; $i <= 10; $i++) {
            $rules['cf_' . $i . '_subj'] = 'nullable|string|max:255';
            $rules['cf_' . $i] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        // 체크박스 처리
        $checkboxFields = [
            'cf_use_point',
            'cf_use_copy_log',
            'cf_use_homepage',
            'cf_req_homepage',
            'cf_use_tel',
            'cf_req_tel',
            'cf_use_hp',
            'cf_req_hp',
            'cf_use_addr',
            'cf_req_addr',
            'cf_use_signature',
            'cf_req_signature',
            'cf_use_profile',
            'cf_req_profile',
            'cf_use_member_icon',
            'cf_use_recommend',
            'cf_email_use',
            'cf_email_wr_super_admin',
            'cf_email_wr_group_admin',
            'cf_email_wr_board_admin',
            'cf_email_wr_write',
            'cf_email_wr_comment_all',
            'cf_email_mb_super_admin',
            'cf_email_mb_member',
            'cf_email_po_super_admin',
            'cf_formmail_is_member',
            'cf_use_copy_log',
            'cf_social_login_use',
        ];

        foreach ($checkboxFields as $field) {
            $validated[$field] = $request->has($field) ? 1 : 0;
        }

        // 기본값 설정
        $validated['cf_connect_skin'] = $validated['cf_connect_skin'] ?? 'basic';
        $validated['cf_mobile_connect_skin'] = $validated['cf_mobile_connect_skin'] ?? 'basic';
        $validated['cf_faq_skin'] = $validated['cf_faq_skin'] ?? 'basic';
        $validated['cf_mobile_faq_skin'] = $validated['cf_mobile_faq_skin'] ?? 'basic';
        $validated['cf_member_skin'] = $validated['cf_member_skin'] ?? 'basic';
        $validated['cf_mobile_member_skin'] = $validated['cf_mobile_member_skin'] ?? 'basic';
        $validated['cf_editor'] = $validated['cf_editor'] ?? '';
        $validated['cf_captcha_mp3'] = $validated['cf_captcha_mp3'] ?? '';

        $config = Config::first();
        $config->update($validated);

        // 현재 탭 상태 유지
        $tab = $request->input('active_tab', 'basic');
        
        return redirect()->route('admin.config', ['tab' => $tab])
            ->with('success', '환경설정이 저장되었습니다.')
            ->with('active_tab', $tab);
    }
}