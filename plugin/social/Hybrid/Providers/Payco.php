<?php
/* !
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2012, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
 */
/**
 * Hybrid_Providers_Payco provider adapter based on OAuth2 protocol
 * Copyright (c) 2017 SIR - thisgun
 * http://sir.kr
 *
 *
 */
class Hybrid_Providers_Payco extends Hybrid_Provider_Model_OAuth2 {

    private $idNo;

    /**
     * {@inheritdoc}
     */
    function initialize() {

        parent::initialize();

        // Provider API end-points
        $this->api->api_base_url  = 'https://id.payco.com/oauth2.0/';
        $this->api->authorize_url = 'https://id.payco.com/oauth2.0/authorize';
        $this->api->token_url     = 'https://id.payco.com/oauth2.0/token';
        $this->api->token_info   = 'https://apis3.krp.toastoven.net/payco/friends/getIdNoByFriendsToken.json';
        $this->api->profile_url   = 'https://apis-payco.krp.toastoven.net/payco/friends/find_member_v2.json';

        if (!$this->config["keys"]["id"] || !$this->config["keys"]["secret"]) {
            throw new Exception("Your application id and secret are required in order to connect to {$this->providerId}.", 4);
        }

        // redirect uri mismatches when authenticating with Payco.
        if (isset($this->config['redirect_uri']) && !empty($this->config['redirect_uri'])) {
            $this->api->redirect_uri = $this->config['redirect_uri'];
        }
    }
    /**
     * {@inheritdoc}
     */
    function loginBegin() {
        
        $token = md5(uniqid(mt_rand(), true));
        Hybrid_Auth::storage()->set('payco_auth_token', $token);

        $parameters = array(
            "response_type" => "code",
            "client_id" => $this->api->client_id,
            "redirect_uri" => $this->api->redirect_uri,
            "state" => $token,
            "userLocale" => "ko_KR",
            "serviceProviderCode" => "FRIENDS",
            );
        
        Hybrid_Auth::redirect($this->api->authorizeUrl($parameters));

        exit;

    }
    /**
     * {@inheritdoc}
     */
    function loginFinish() {

        // in case we get error_reason=user_denied&error=access_denied
        if (isset($_REQUEST['error']) && $_REQUEST['error'] == "access_denied") {
            throw new Exception("Authentication failed! The user denied your request.", 5);
        }
        
        // try to authenicate user
        $code = (array_key_exists('code', $_REQUEST)) ? $_REQUEST['code'] : "";
        try{
            $response = $this->api->authenticate( $code );
        }
        catch( Exception $e ){
            throw new Exception( "User profile request failed! {$this->providerId} returned an error: $e", 6 );
        }
        
        // check if authenticated
        if ( ! $this->api->authenticated() ){
            throw new Exception( "Authentication failed! {$this->providerId} returned an invalid access token.", 5 );
        }
        // store tokens
        $this->token("access_token",  $this->api->access_token);
        $this->token("refresh_token", $this->api->refresh_token);
        $this->token("expires_in",    $this->api->access_token_expires_in);
        $this->token("expires_at",    $this->api->access_token_expires_at);
        
        $this->setUserConnected();

    }

    function check_valid_access_token(){

        $params = array(
            'body' => array(
                'client_id'=>$this->api->client_id, 
                'access_token'=>$this->api->access_token,
            ),
        );
        
        $this->api->curl_header = array(

            'Content-Type:application/json',
            'client_id: '.$this->api->client_id,
            'access_token: '.$this->api->access_token,

        );

        $response = $this->api->api( $this->api->token_info, 'POST', $params );

        if( is_object($response) && !empty($response->idNo) && $response->header->successful ){
            $this->idNo = $response->idNo;

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    function logout() {
        parent::logout();
    }
    /**
     * {@inheritdoc}
     */

    /**
    * set propper headers
    */
    function getUserProfile() {

        $data = null;

        // request user profile
        try {

            if( $this->check_valid_access_token() ){
                $params = array(
                    'body' => array(
                    'client_id'=>$this->api->client_id, 
                    'access_token'=>$this->api->access_token,
                    'MemberProfile'=>'idNo,id,name',
                    'idNo'=>$this->idNo,
                    ),
                );

                $this->api->curl_header = array(
                    'Content-Type:application/json',
                    'client_id: '.$this->api->client_id,
                    'access_token: '.$this->api->access_token,
                    'Authorization: Bearer ' . $this->api->access_token,
                );

                $response = $this->api->api( $this->api->profile_url, 'POST', $params );
            }

        } catch (Exception $e) {
            throw new Exception("User profile request failed! {$this->providerId} returned an error: {$e->getMessage()}", 6, $e);
        }

        if( ! is_object($response) || property_exists($response, 'error_code') ){
            $this->logout();

            throw new Exception( "Authentication failed! {$this->providerId} returned an invalid access token.", 5 );
        }

        $data = array();

        if( is_object($response) ){
            $result = json_decode(json_encode($response), true);

            // 성공이면
            if(isset($result['header']) && isset($result['header']['isSuccessful']) && $result['header']['isSuccessful']){
            $data = $result['data']['member'];
            }
        }

        // if the provider identifier is not received, we assume the auth has failed
        if (!isset($data["idNo"])) {
            $this->logout();
            throw new Exception("User profile request failed! {$this->providerId} api returned an invalid response: " . Hybrid_Logger::dumpData( $data ), 6);
        }

        # store the user profile.
        $this->user->profile->identifier = (array_key_exists('idNo', $data)) ? $data['idNo'] : "";
        $this->user->profile->username = (array_key_exists('name', $data)) ? $data['name'] : "";
        $this->user->profile->displayName = (array_key_exists('name', $data)) ? $data['name'] : "";
        $this->user->profile->age = (array_key_exists('ageGroup', $data)) ? $data['ageGroup'] : "";
        $this->user->profile->hp = (array_key_exists('mobile', $data)) ? $data['mobile'] : "";

        include_once(G5_LIB_PATH.'/register.lib.php');

        $payco_no = substr(base_convert($this->user->profile->identifier, 16, 36), 0, 16);
        //$email = (array_key_exists('id', $data)) ? $data['id'] : "";

        $email = (array_key_exists('email', $data)) ? $data['email'] : "";

        //$this->user->profile->gender = (array_key_exists('sexCode', $data)) ? $data['sexCode'] : "";

        $this->user->profile->gender = (array_key_exists('genderCode', $data)) ? strtolower($data['genderCode']) : "";
        $this->user->profile->email = ! valid_mb_email($email) ? $email : "";
        $this->user->profile->emailVerified = ! valid_mb_email($email) ? $email : "";


        if (array_key_exists('birthdayMMdd', $data)) {
            $this->user->profile->birthMonth = substr($data['birthdayMMdd'], 0, 2);
            $this->user->profile->birthDay = substr($data['birthdayMMdd'], 2, 4);
        }

        $this->user->profile->sid         = get_social_convert_id( $this->user->profile->identifier, $this->providerId );

        return $this->user->profile;
    }   //end function getUserProfile

}