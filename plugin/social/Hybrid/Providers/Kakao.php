<?php
/**
 * Copyright (c) 2014 Team TamedBitches.
 * Written by Chuck JS. Oh <jinseokoh@hotmail.com>
 * http://facebook.com/chuckoh
 *
 * Date: 11 10, 2014
 * Time: 01:51 AM
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/txt/copying/ for more details. 
 *
 */

//https://github.com/jinseokoh/additional-providers
class Hybrid_Providers_Kakao extends Hybrid_Provider_Model_OAuth2
{
    /**
    * initialization
    */
    function initialize()
    {
        parent::initialize();

        // Provider API end-points
        $this->api->api_base_url  = "https://kapi.kakao.com/v2/";
        $this->api->authorize_url = "https://kauth.kakao.com/oauth/authorize";
        $this->api->token_url     = "https://kauth.kakao.com/oauth/token";

		// redirect uri mismatches when authenticating with Kakao.
		if (isset($this->config['redirect_uri']) && !empty($this->config['redirect_uri'])) {
			$this->api->redirect_uri = $this->config['redirect_uri'];
		}
    }

    /**
    * finish login step
    */
    function loginFinish()
    {
        $error = (array_key_exists('error', $_REQUEST)) ? $_REQUEST['error'] : "";
        // check for errors
        if ( $error ){
            throw new Exception( "Authentication failed! {$this->providerId} returned an error: $error", 5 );
        }
        // try to authenicate user
        $code = (array_key_exists('code', $_REQUEST)) ? $_REQUEST['code'] : "";
        try{
            $this->authenticate( $code );
        }
        catch( Exception $e ){
            throw new Exception( "User profile request failed! {$this->providerId} returned an error: $e", 6 );
        }
        // check if authenticated
        if ( ! $this->api->access_token ){
            throw new Exception( "Authentication failed! {$this->providerId} returned an invalid access token.", 5 );
        }
        // store tokens
        $this->token("access_token",  $this->api->access_token);
        $this->token("refresh_token", $this->api->refresh_token);
        $this->token("expires_in",    $this->api->access_token_expires_in);
        $this->token("expires_at",    $this->api->access_token_expires_at);
        // set user connected locally
        $this->setUserConnected();
    }

    /**
    * load the user profile
    */
    function getUserProfile()
    {
        //$params = array('property_keys'=>'kaccount_email');	// v1 parameter
        $params = array('property_keys'=>array('kakao_account.email'));		// v2 parameter

        $this->api->decode_json = false;
        $this->api->curl_header = array( 'Authorization: Bearer ' . $this->api->access_token );

        $data = $this->api->api("user/me", "POST", $params);

        if ( ! isset( $data->id ) ) {
            throw new Exception("User profile request failed! {$this->providerId} returned an invalid response.", 6);
        }
        # store the user profile.
        $this->user->profile->identifier  = @ $data->id;
        $this->user->profile->displayName = @ $data->properties->nickname;
        $this->user->profile->photoURL    = @ $data->properties->thumbnail_image;
        //$email = @ $data->properties->kaccount_email;	// v1 version
        
        $email = @ $data->kakao_account->email;   // v2 version

        if( $email ){
            $this->user->profile->email = $email;
        }

        $this->user->profile->sid         = get_social_convert_id( $this->user->profile->identifier, $this->providerId );

        return $this->user->profile;
    }

    private function authenticate($code)
    {
        $params = array(
            "grant_type"    => "authorization_code",
            "client_id"     => $this->api->client_id,
            "redirect_uri"  => $this->api->redirect_uri,
            "code"          => $code
        );

        if( $this->api->client_secret && ($this->api->client_secret !== $this->api->client_id) ){
            $params['client_secret'] = $this->api->client_secret;
        }

        $response = $this->request($this->api->token_url, $params, $this->api->curl_authenticate_method);
        $response = $this->parseRequestResult($response);
        if ( ! $response || ! isset($response->access_token) ) {
            throw new Exception("The Authorization Service has return: " . $response->error);
        }
        if ( isset($response->access_token) )  $this->api->access_token            = $response->access_token;
        if ( isset($response->refresh_token) ) $this->api->refresh_token           = $response->refresh_token;
        if ( isset($response->expires_in) )    $this->api->access_token_expires_in = $response->expires_in;

        // calculate when the access token expire
        if ( isset($response->expires_in) ) {
            $this->api->access_token_expires_at = time() + $response->expires_in;
        }

        return $response;
    }

    private function request($url, $params=false, $type="GET")
    {
        if(Class_exists('Hybrid_Logger')){
            Hybrid_Logger::info("Enter OAuth2Client::request( $url )");
            Hybrid_Logger::debug("OAuth2Client::request(). dump request params: ", serialize( $params ));
        }
        $this->http_info = array();
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL           , $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT       , $this->api->curl_time_out);
        curl_setopt($ch, CURLOPT_USERAGENT     , $this->api->curl_useragent);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->api->curl_connect_time_out);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->api->curl_ssl_verifypeer);
        curl_setopt($ch, CURLOPT_HTTPHEADER    , $this->api->curl_header);

        if ( $this->api->curl_proxy ) {
            curl_setopt( $ch, CURLOPT_PROXY, $this->curl_proxy);
        }
        if ( $type == "POST" ) {
            curl_setopt($ch, CURLOPT_POST, 1);
            if ($params) curl_setopt( $ch, CURLOPT_POSTFIELDS, http_build_query($params) );
        }

        $response = curl_exec($ch);
        if(Class_exists('Hybrid_Logger')){
            Hybrid_Logger::debug( "OAuth2Client::request(). dump request info: ", serialize(curl_getinfo($ch)) );
            Hybrid_Logger::debug( "OAuth2Client::request(). dump request result: ", serialize($response ));
        }
        $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->http_info = array_merge($this->http_info, curl_getinfo($ch));
        curl_close ($ch);

        return $response;
    }

    private function parseRequestResult($result)
    {
        if ( json_decode($result) ) return json_decode($result);
        parse_str( $result, $ouput );
        $result = new StdClass();
        foreach( $ouput as $k => $v )
            $result->$k = $v;

        return $result;
    }
}