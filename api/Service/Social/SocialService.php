<?php

namespace API\Service\Social;

use API\Database\Db;
use API\Service\ConfigService;
use API\Service\MemberService;
use GuzzleHttp\Exception\GuzzleException;
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


    public function __construct(MemberService $memberService)
    {
        $this->config = ConfigService::getConfig();
        $this->memberService = $memberService;
    }

    /**
     * HybirdAuth v3 라이브러리 설정
     * 콜백 URL, 키값 등 설정한다.
     * 기본적으로 id, secret key 는 web 용 설정.
     *
     */
    function setProviderConfig($callback_base_url)
    {
        $config = $this->config;
        $social_list = explode(',', $config['cf_social_servicelist']);

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
                    'id' => $config['cf_naver_clientid'],
                    'secret' => $config['cf_naver_secret'],
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
                    'id' => $config['cf_kakao_rest_key'],
                    'secret' => $config['cf_kakao_client_secret'] ?: $config['cf_kakao_rest_key']
                ],
            ];

            // Facebook
            $social_config['Facebook'] = [
                'enabled' => in_array('facebook', $social_list),
                'adapter' => in_array('facebook', $social_list) ? Facebook::class : '',
                'callback' => $callback_base_url . '/facebook',
                'supportRequestState' => false,
                'keys' => ['id' => $config['cf_facebook_appid'], 'secret' => $config['cf_facebook_secret']],
                'display' => "popup",
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
                    'id' => $config['cf_google_clientid'],
                    'secret' => $config['cf_google_secret']
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

            // Twitter
            $social_config['Twitter'] = [
                'enabled' => in_array('twitter', $social_list),
//                'adapter' => in_array('twitter', $social_list) ? \API\Service\Social\Twitter::class : '',
                'callback' => $callback_base_url . '/twitter',
                'supportRequestState' => false,
                'keys' => ['key' => $config['cf_twitter_key'], 'secret' => $config['cf_twitter_secret']],
                'trustForwarded' => false
            ];

            // Payco
            $social_config['Payco'] = [
                'enabled' => in_array('payco', $social_list),
//                'adapter' => in_array('payco', $social_list) ? \API\Service\Social\Payco::class : '',
                'callback' => $callback_base_url . '/payco',
                'supportRequestState' => false,
                'keys' => ['id' => $config['cf_payco_clientid'], 'secret' => $config['cf_payco_secret']],
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
    function setProvider(string $provider, $storage_data = null)
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
        // 로그인
        return ($member['mb_intercept_date'] && $member['mb_intercept_date'] <= date("Ymd", G5_SERVER_TIME))
            || ($member['mb_leave_date'] && $member['mb_leave_date'] <= date("Ymd", G5_SERVER_TIME));
    }

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

    /**
     * @param $provider
     * @param Profile $profile
     * @param $member_data
     * @return void
     * @throws \RuntimeException 이미 가입된 회원, 소셜 회원가입 실패
     */
    public function signUpSocialMember($provider, $profile, $member_data)
    {
        $is_exist = $this->checkExistSocialMember($provider, $profile->identifier);

        if ($is_exist) {
            throw new \RuntimeException('이미 가입된 회원입니다.', 409);
        }

        $social_profile = $this->convertGnuboardSocialData($provider, $profile);

        // 트랜젝션이 지원안되는 DB 는 오류 코드로 처리
        try {
            Db::getInstance()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
            $profile_insert_id = $this->insertSocialProfile($social_profile);
            if (!$profile_insert_id) {
                throw new \RuntimeException('회원가입이 되지 않았습니다.', 400);
            }

            $member_data = (array)$member_data;
            $member_data['mb_id'] = $social_profile['mb_id'];
            $member_insert_id = $this->memberService->insertMember($member_data);

            if (!$member_insert_id) {
                //rollback
                $rollback_result = $this->deleteSocialProfile($provider, $profile->identifier);
                if (!$rollback_result) {
                    throw new \RuntimeException('회원가입이 되지 않았습니다.,', 400);
                }

                throw new \RuntimeException('회원가입이 되지 않았습니다.', 400);
            }
        } catch (\Exception|\Error $e) {
            $rollback_result = false;

            if (isset($profile_insert_id) && !isset($member_insert_id)) {
                $rollback_result = $this->deleteSocialProfile($provider, $profile->identifier);
            }

            if (!$rollback_result) {
                error_log('social sign up DB error: ' . $e->getMessage() . print_r($e->getTrace(), true));
            } else {
                error_log('social sign up error: ' . $e->getMessage() . print_r($e->getTrace(), true));
            }

            throw new \RuntimeException('회원가입이 되지 않았습니다.', 400);
        } finally {
            //pdo 에러 모드 복귀
            Db::getInstance()->getPdo()->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        }
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
     * @param $dest_path
     * @param $file_url
     * @param $width
     * @param $height
     * @return void
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    function socialProfileImgResize($dest_path, $file_url, $width, $height)
    {
        //todo : allow_url_fopen 이 활성화 되어 있지 않은 경우에 대한 처리 필요
        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $file_url);
            $image_file = $response->getBody()->getContents();
        } catch (\Exception $e) {
            error_log($file_url . 'social profile image download error');
            error_log($e->getMessage());
            return;
        }
        list($w, $h, $ext) = getimagesize($image_file);

        if ($w && $h && $ext) {
            $ratio = max($width / $w, $height / $h);
            $h = ceil($height / $ratio);
            $x = ($w - $width / $ratio) / 2;
            $w = ceil($width / $ratio);

            $tmp = imagecreatetruecolor($width, $height);

            if ($ext == 1) {
                $image = imagecreatefromgif($image_file);
            } else if ($ext == 3) {
                $image = imagecreatefrompng($image_file);
            } else {
                $image = imagecreatefromjpeg($image_file);
            }

            if (!$image) {
                return;
            }

            imagecopyresampled($tmp, $image,
                0, 0,
                $x, 0,
                $width, $height,
                $w, $h);

            switch ($ext) {
                case '2':
                    imagejpeg($tmp, $dest_path, G5_THUMB_JPG_QUALITY);
                    break;
                case '3':
                    imagepng($tmp, $dest_path, 0);
                    break;
                case '1':
                    imagegif($tmp, $dest_path);
                    break;
            }

            chmod($dest_path, G5_FILE_PERMISSION);

            /* cleanup memory */
            imagedestroy($image);
            imagedestroy($tmp);
        }
    }

    /**
     * allow_url_fopen 를 쓰지 않고 이미지를 메모리에 로드
     * @return string
     * @throws GuzzleException
     */
    public function getImageFromUrl($url)
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $url);
        return $response->getBody()->getContents();
    }

    /**
     * 소셜 config 에서 사용여부를 체크한다.
     */
    public function socialUseCheck($provider_name)
    {
        $provider_name = ucfirst($provider_name);
        return isset($this->social_config['providers'][$provider_name]['enabled']) && $this->social_config['providers'][$provider_name]['enabled'];
    }
}
