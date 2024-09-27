<?php

namespace API\Service\Social;


use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;


class Kakao extends OAuth2
{
    // phpcs:ignore
    protected $scope = 'account_email, profile_image';


    protected $apiBaseUrl = 'https://kapi.kakao.com/v2/user/me';


    protected $authorizeUrl = 'https://kauth.kakao.com/oauth/authorize';


    protected $accessTokenUrl = 'https://kauth.kakao.com/oauth/token';


    protected $apiDocumentation = 'https://developers.kakao.com/docs/latest/ko/kakaologin/common';

    protected function initialize()
    {
        parent::initialize();

        $this->AuthorizeUrlParameters += [
            'access_type' => 'offline'
        ];

        if ($this->isRefreshTokenAvailable()) {
            $this->tokenRefreshParameters += [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ];
        }
    }


    /**
     * {@inheritdoc}
     * See: https://developers.kakao.com/docs/latest/ko/kakaologin/rest-api#req-user-info
     * @return User\Profile
     * @throws UnexpectedApiResponseException
     * @throws \Hybridauth\Exception\HttpClientFailureException
     * @throws \Hybridauth\Exception\HttpRequestFailedException
     * @throws \Hybridauth\Exception\InvalidAccessTokenException
     * @throws HttpRequestFailedException
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest($this->apiBaseUrl);

        $data = new Data\Collection($response);

        if (!$data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $user_profile = new User\Profile();
        $user_profile->identifier = $data->get('id');
        $user_profile->firstName = $data->get('nickname');
        $user_profile->displayName = $data->get('nickname');
        $user_profile->photoURL = $data->get('thumbnail_image_url');
        $user_profile->gender = $data->get('gender');
        $user_profile->email = $data->get('account_email');
        $user_profile->emailVerified = $data->get('is_email_verified') ? $user_profile->email : '';

        return $user_profile;
    }

}
