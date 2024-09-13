<?php

namespace API\Service\Social;

use API\Database\Db;
use API\Service\AuthenticationService;
use API\Service\ConfigService;
use API\Service\MemberService;
use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedValueException;
use Hybridauth\Hybridauth;
use Hybridauth\Provider\Facebook;
use Hybridauth\Provider\Google;
use Hybridauth\User\Profile;

class SocialService
{
    /**
     * @var MemberService $memberService
     */
    private $memberService;
    private $config;

    /**
     * PHP 는 매번 초기화 되므로 현재 요청의 로그인 하는 provider 객체가 지정된다.
     * @var \Hybridauth\Adapter\AbstractAdapter
     */
    public $current_provider_instance;

    private array $social_config = [];

    private AuthenticationService $authentication_service;

    public function __construct(
        AuthenticationService $authentication_service,
        MemberService $memberService
    ) {
        $this->authentication_service = $authentication_service;
        $this->config = ConfigService::getConfig();
        $this->memberService = $memberService;
    }

    /**
     *
     *  HybirdAuth v3 라이브러리 설정
     *  콜백 URL, 키값 등 설정한다.
     *  기본적으로 id, secret key 는 callback web 용 설정.
     * @param string $callback_base_url 콜백 URL
     * @param bool $from_callback 웹 콜백에서 호출되었는지 여부
     * @return void
     */
    public function setProviderConfig($callback_base_url, $from_callback = false)
    {
        $config = $this->config;
        $social_list = explode(',', $config['cf_social_servicelist']) ?: [];

        if (empty($this->social_config)) {
            // Naver
            $social_config = [];
            $use_naver = in_array('naver', $social_list);
            $social_config['Naver'] = [
                'enabled' => $use_naver,
                'adapter' => $use_naver ? \API\Service\Social\Naver::class : '',
                'callback' => $callback_base_url . '/naver',
                'supportRequestState' => false,
                'keys' => [
                    'id' => $from_callback ? $config['cf_naver_clientid'] : 'empty-id',
                    'secret' => $from_callback ? $config['cf_naver_secret'] : 'empty-secret'
                ],
            ];

            // Kakao
            $use_kakao = in_array('kakao', $social_list);
            $social_config['Kakao'] = [
                'enabled' => $use_kakao,
                'adapter' => $use_kakao ? \API\Service\Social\Kakao::class : '',
                'callback' => $callback_base_url . '/kakao',
                'supportRequestState' => false,
                'keys' => [
                    'id' => $from_callback ? $config['cf_kakao_rest_key'] : 'empty-id',
                    'secret' => $from_callback ? $config['cf_kakao_client_secret'] : 'empty-secret'
                ],
            ];

            // Facebook
            $social_config['Facebook'] = [
                'enabled' => in_array('facebook', $social_list),
                'adapter' => in_array('facebook', $social_list) ? Facebook::class : '',
                'callback' => $callback_base_url . '/facebook',
                'supportRequestState' => false,
                'keys' => [
                    'id' => $from_callback ? $config['cf_facebook_appid'] : 'empty-id',
                    'secret' => $from_callback ? $config['cf_facebook_secret'] : 'empty-secret'
                ],
                'display' => 'popup',
                'scope' => 'email', // optional
                'trustForwarded' => false
            ];

            // Google
            $social_config['Google'] = [
                'enabled' => in_array('google', $social_list),
                'adapter' => in_array('google', $social_list) ? Google::class : '',
                'callback' => $callback_base_url . '/google',
                'supportRequestState' => false,
                'keys' => [
                    'id' => $from_callback ? $config['cf_google_clientid'] : 'empty-id',
                    'secret' => $from_callback ? $config['cf_google_secret'] : 'empty-secret'
                ],
                'scope' => 'https://www.googleapis.com/auth/userinfo.profile ' . 'https://www.googleapis.com/auth/userinfo.email',
                /*
                'scope'   => "https://www.googleapis.com/auth/plus.login ". // optional
                                "https://www.googleapis.com/auth/plus.me ". // optional
                                "https://www.googleapis.com/auth/plus.profile.emails.read", // optional
                */
                //"access_type"     => "offline",   // optional
                //"approval_prompt" => "force",     // optional
            ];

            // Twitter v1
            $social_config['Twitter'] = [
                'enabled' => in_array('twitter', $social_list),
                'adapter' => in_array('twitter', $social_list) ? \Hybridauth\Provider\Twitter::class : '',
                'callback' => $callback_base_url . '/twitter',
                'supportRequestState' => false,
                'keys' => [
                    'key' => $from_callback ? $config['cf_twitter_key'] : 'empty-id',
                    'secret' => $from_callback ? $config['cf_twitter_secret'] : 'empty-secret'
                ],
                'trustForwarded' => false
            ];

            // Payco
            $social_config['Payco'] = [
                'enabled' => in_array('payco', $social_list),
//                'adapter' => in_array('payco', $social_list) ? \API\Service\Social\Payco::class : '',
                'callback' => $callback_base_url . '/payco',
                'supportRequestState' => false,
                'keys' => [
                    'id' => $from_callback ? $config['cf_payco_clientid'] : 'empty-id',
                    'secret' => $from_callback ? $config['cf_payco_secret'] : 'empty-secret'
                ],
                'trustForwarded' => false
            ];

            $this->social_config['providers'] = $social_config;
//            $this->social_config['debug_mode'] = G5_DEBUG;
//            $this->social_config['debug_file'] = G5_DATA_PATH . '/hybridauth.log';
        }

        // 설정 추가
        add_event('social_provider_add', 'add_social_provider_config', G5_HOOK_DEFAULT_PRIORITY);
    }

    /**
     * 소셜 프로바이더 설정
     * hybridauth 의 스토리지와 config 를 지정합니다
     * @param string $provider
     * @param ?array $storage_data
     * @return void
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     */
    public function setProvider(string $provider, $storage_data = null)
    {
        // api 에서는 기본 세션저장소를 사용하지 않음.
        $storage = new StatelessStorage();
        if ($storage_data) {
            $provider_class = ucfirst($provider);
            $storage->set($provider_class . '.access_token', $storage_data['access_token']);
            $storage->set($provider_class . '.refresh_token', $storage_data['refresh_token']);
            $storage->set($provider_class . '.expires_at', $storage_data['expires_at']);
            $storage->set($provider_class . '.expires_in', $storage_data['expires_in']);
        }

        $this->current_provider_instance = (new Hybridauth($this->social_config, null, $storage))->getAdapter($provider);
    }

    /**
     * 소셜 제공자의 고유아이디를 그누보드 ID 로 변환 - 그누보드 5 기준
     * @param $provider
     * @param $identifier
     * @return string
     */
    public function convertGnuboardId($provider, $identifier)
    {
        return strtolower($provider) . '_' . hash('adler32', md5($identifier));
    }

    /**
     * 그누보드 소셜 프로필 테이블 데이터로 변환
     * @param string $provider 소셜 제공자
     * @param Profile $social_data 소셜api 에서 받아온 데이터
     * @return array
     */
    public function convertGnuboardSocialData($provider, $social_data)
    {
        return [
            'mb_id' => $this->convertGnuboardId($provider, $social_data->identifier),
            'identifier' => $social_data->identifier,
            'provider' => $provider,
            'displayname' => $social_data->displayName ?? '',
            'profileurl' => $social_data->profileURL ?? '',
            'photourl' => $social_data->photoURL ?? '',
        ];
    }

    /**
     * 기존 소셜 로그인 가입자 확인
     * @param string $provider
     * @param string $identifier
     * @return bool
     */
    public function checkExistSocialMember($provider, $identifier)
    {
        $social_profile = $this->fetchSocialProfileByIdentifier($provider, $identifier);

        if (isset($social_profile['mb_id'])) {
            return true;
        }
        return false;
    }


    /**
     * 차단 회원 조회
     * @return bool 차단회원이면 true
     */
    public function checkDisallowMember($provider, $identifier)
    {
        $member = $this->fetchMemberByIdentifier($provider, $identifier);
        if (!$member) {
            return false;
        }
        // 로그인
        return ($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date('Ymd', G5_SERVER_TIME))
            || ($member['mb_leave_date'] && $member['mb_leave_date'] <= date('Ymd', G5_SERVER_TIME));
    }


    /**
     * 기존 회원에 소셜 로그인 연결하기
     * @param string $provider 소셜 제공자이름
     * @param Profile $profile 소셜 프로필
     * @param string $mb_id 그누보드 회원아이디
     * @return void
     */
    public function linkSocialMember($provider, $profile, $mb_id)
    {
        $social_profile = $this->convertGnuboardSocialData($provider, $profile);
        $social_profile['mb_id'] = $mb_id;
        $this->insertSocialProfile($social_profile);
    }


    /**
     * @param string $provider
     * @param Profile $profile
     * @param $member_data
     * @return void
     * @throws \RuntimeException 이미 가입된 회원, 소셜 회원가입 실패
     */
    public function signUpSocialMember(string $provider, Profile $profile, $member_data)
    {
        $is_exist = $this->checkExistSocialMember($provider, $profile->identifier);

        if ($is_exist) {
            throw new \RuntimeException('이미 가입된 회원입니다.', 409);
        }

        $social_profile = $this->convertGnuboardSocialData($provider, $profile);
        $generated_mb_id = $this->memberService->existsMemberIdRecursive($social_profile['mb_id']);
        $social_profile['mb_id'] = $generated_mb_id;
        $member_data = (array)$member_data;

        if (empty($member_data['mb_email'])) {
            $member_data['mb_email'] = $profile->email ?? '';
        }

        if (empty($member_data['mb_nick'])) {
            // 닉네임이 없으면 소셜 닉네임으로 설정, 닉네임 중복되면 뒤에 숫자 추가
            if (empty($social_profile['displayname'])) {
                $random_nick = bin2hex(random_bytes(5));
                $member_data['mb_nick'] = $this->memberService->existsMemberNicknameRecursive($random_nick);
            } else {
                $member_data['mb_nick'] = $this->memberService->existsMemberNicknameRecursive($social_profile['displayname']);
            }
        }

        $member_data['mb_id'] = $social_profile['mb_id'];

        // 트랜젝션이 지원안되는 DB 는 오류 코드로 처리
        // 수동 트랜젝션 start
        try {
            Db::getInstance()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
            $profile_insert_id = $this->insertSocialProfile($social_profile);
            if (!$profile_insert_id) {
                throw new \RuntimeException('회원가입이 되지 않았습니다.', 400);
            }

            $member_insert_id = $this->memberService->insertMember($member_data);
            //Throwable 오류 발생안하고 실패시(PDO stmt의 false) 롤백
            if (!$member_insert_id) {
                $rollback_result = $this->deleteSocialProfile($provider, $profile->identifier);
                if (!$rollback_result) {
                    throw new \RuntimeException('회원가입이 되지 않았습니다.,', 400);
                }

                throw new \RuntimeException('회원가입이 되지 않았습니다.', 400);
            }
        } catch (\Exception|\Throwable $e) {
            // 프로필 저장되고 멤버 실패시 (Throwable 오류발생 - 타입에러, ValueError 등 에러)
            if (isset($profile_insert_id) && $profile_insert_id && !isset($member_insert_id)) {
                $rollback_result = $this->deleteSocialProfile($provider, $profile->identifier);
                if (!$rollback_result) {
                    error_log('social sign up DB rollback error: ' . (string)$profile_insert_id . $e->getMessage() . print_r($e->getTrace(), true));
                }
            }
            throw new \RuntimeException('회원가입이 실패했습니다.', 400);
        } finally {
            //pdo 에러 모드 복귀
            Db::getInstance()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
        // 수동 트랜젝션 end

        try {
            $member_icon_file_path = G5_DATA_PATH . '/member/' . substr($social_profile['mb_id'], 0, 2) . '/' . $social_profile['mb_id'] . '.gif';
            $member_img_file_path = G5_DATA_PATH . '/member_image/' . substr($social_profile['mb_id'], 0, 2) . '/' . $social_profile['mb_id'] . '.gif';
            $this->socialProfileImgUploader($member_icon_file_path, $profile->photoURL, $this->config['cf_member_icon_width'], $this->config['cf_member_icon_height']);
            $this->socialProfileImgUploader($member_img_file_path, $profile->photoURL, $this->config['cf_member_img_width'], $this->config['cf_member_img_height']);
        } catch (\Exception|\Throwable $e) {
            // 이미지 업로드 실패 (이미지가 없는 등)
            error_log('social sign up image upload error: ' . $e->getMessage() . print_r($e->getTrace(), true));
            // 회원가입에 영향없으므로 계속 진행
        }
    }

    /**
     * @param $dest_file_path
     * @param $file_url
     * @param $width
     * @param $height
     * @return void
     */
    public function socialProfileImgUploader($dest_file_path, $file_url, $width, $height)
    {
        if (empty($file_url)) {
            return;
        }

        if ($width && $height) {
            return;
        }

        $dir_path = dirname($dest_file_path);
        if (!mkdir($dir_path, G5_DIR_PERMISSION, true) && !is_dir($dir_path)) {
            return;
        }

        list($image_width, $image_height, $ext) = getimagesize($file_url);
        if ($image_width && $image_height && $ext) {
            $ratio = max($width / $image_width, $height / $image_height);
            $image_height = ceil($height / $ratio);
            $x = ($image_width - $width / $ratio) / 2;
            $image_width = ceil($width / $ratio);

            // 최종이미지가 저장될 객체
            $new_image = imagecreatetruecolor($width, $height);
            if (!$new_image) {
                return;
            }
            if ($ext == 1) {
                $image = imagecreatefromgif($file_url);
            } else if ($ext == 3) {
                $image = imagecreatefrompng($file_url);
            } else if ($ext == 18) {
                $image = imagecreatefromwebp($file_url);
            } else {
                $image = imagecreatefromjpeg($file_url);
            }

            if (!$image) {
                return;
            }

            $result = imagecopyresampled(
                $new_image,
                $image,
                0, 0,
                $x, 0,
                $width, $height,
                $image_width, $image_height
            );
            if (!$result) {
                return;
            }

            if (!imagegif($new_image, $dest_file_path)) {
                error_log('social profile image upload error: ' . $dest_file_path);
            }

            $result = chmod($dest_file_path, G5_FILE_PERMISSION);
            if (!$result) {
                error_log('social profile image upload error: chmod fail', $dest_file_path);
            }

            if (\PHP_VERSION_ID > 80000) {
                //php 8 부터 지원안함.
                @imagedestroy($new_image);
            }
        }
    }

    /**
     * 소셜 config 에서 사용여부를 체크한다.
     * @param string $provider_name
     * @return bool
     */
    public function socialUseCheck($provider_name)
    {
        $provider_name = ucfirst($provider_name);
        return isset($this->social_config['providers'][$provider_name]['enabled']) && $this->social_config['providers'][$provider_name]['enabled'];
    }

    /**
     * 소셜로그인 연결 끊기
     * @param $provider
     * @param $mb_id
     * @return void
     */
    public function unlink($provider, $mb_id)
    {
        $count = $this->countProfileByMemeberId($mb_id);
        if ($count < 1) {
            return;
        }

        // 템플릿 버전과 다른점
        if ($count === 1) {
            throw new \RuntimeException('연결된 계정이 하나뿐인 경우 해제할 수 없습니다.', 400);
        }

        $this->deleteSocialProfile($provider, $mb_id);
    }


    /**
     * 회원의 모든 소셜 프로필 삭제
     * @param $mb_id
     * @return void
     * @example - 회원 탈퇴시
     */
    public function leaveMember($mb_id)
    {
        $this->deleteAllSocialProfileByMemberId($mb_id);
    }


    //---------- DB query ------------------------

    public function fetchSocialProfileByIdentifier($provider, $identifier)
    {
        $social_table = $GLOBALS['g5']['social_profile_table'];
        $query = "SELECT * FROM {$social_table} WHERE identifier = ? AND provider = ?";
        return Db::getInstance()->run($query, [$identifier, $provider]
        )->fetch();
    }

    /**
     * 소셜로그인 고유번호로 회원 조회
     * @param $provider
     * @param $identifier
     * @return mixed
     */
    public function fetchMemberByIdentifier($provider, $identifier)
    {
        $social_table = $GLOBALS['g5']['social_profile_table'];
        $member_table = $GLOBALS['g5']['member_table'];
        $query = "SELECT m.* FROM {$social_table} s JOIN {$member_table} m ON s.mb_id = m.mb_id WHERE s.identifier = ? AND s.provider = ?";
        return Db::getInstance()->run($query, [$identifier, $provider]
        )->fetch();
    }

    public function countProfileByMemeberId($mb_id)
    {
        $profile_table = $GLOBALS['g5']['social_profile_table'];
        $query = "SELECT count(*) as cnt FROM {$profile_table} WHERE mb_id = ?";
        return Db::getInstance()->run($query, [$mb_id])->fetchColumn();
    }

    /**
     * @param $data
     * @return false|string
     */
    public function insertSocialProfile($data)
    {
        $social_table = $GLOBALS['g5']['social_profile_table'];
        return Db::getInstance()->insert($social_table, $data);
    }

    public function deleteSocialProfile($provider, $identifier)
    {
        $social_table = $GLOBALS['g5']['social_profile_table'];
        return Db::getInstance()->delete($social_table, ['identifier' => $identifier, 'provider' => $provider]);
    }

    /**
     * 한 회원의 모든 소셜 프로필 삭제
     * @param string $mb_id 회원아이디
     * @return int 삭제된 행의 수
     */
    public function deleteAllSocialProfileByMemberId($mb_id)
    {
        $social_table = $GLOBALS['g5']['social_profile_table'];
        return Db::getInstance()->delete($social_table, ['mb_id' => $mb_id]);
    }

    /**
     * 소셜 로그인 인증이후 서버로그인을 위한 토큰 생성
     * @param $provider
     * @param $profile
     * @return array
     */
    public function getLoginTokenBySocialAuth($provider, $profile)
    {
        $member = $this->fetchMemberByIdentifier($provider, $profile->identifier);
        return $this->authentication_service->generateLoginTokenByAuthMemberId($member['mb_id']);
    }

}
