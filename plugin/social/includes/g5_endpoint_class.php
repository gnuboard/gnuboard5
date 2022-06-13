<?php
if (!defined('_GNUBOARD_')) exit;

class G5_Hybrid_Endpoint extends Hybrid_Endpoint
{
	protected function authInit() {
		if (!$this->initDone) {
			$this->initDone = true;

			// Init Hybrid_Auth
			try {
				if (!class_exists("Hybrid_Storage", false)) {
					require_once realpath(dirname(dirname(__FILE__))). "/Hybrid/Storage.php";
				}
				if (!class_exists("Hybrid_Exception", false)) {
					require_once realpath(dirname(dirname(__FILE__))). "/Hybrid/Exception.php";
				}
				if (!class_exists("Hybrid_Logger", false)) {
					require_once realpath(dirname(dirname(__FILE__))). "/Hybrid/Logger.php";
				}

				$storage = new Hybrid_Storage();
				$provider_id = ucfirst(trim(strip_tags($this->request["hauth_start"])));
				if(!$provider_id) $provider_id = ucfirst(trim(strip_tags($this->request["hauth_done"])));

				$storage->config("CONFIG", social_build_provider_config($provider_id));
				// Check if Hybrid_Auth session already exist
				if (!$storage->config("CONFIG")) {
					$this->dieError("CONFIG FAILED: ", "Unable to get config", array());
				}

				Hybrid_Auth::initialize($storage->config("CONFIG"));
			} catch (Exception $e) {
				Hybrid_Logger::error("Endpoint: Error while trying to init Hybrid_Auth: " . $e->getMessage());
				$this->dieError("Endpoint Error: ", $e->getMessage(), $e);
			}
		}
	}

    protected function processAuthStart(){
		try {
			parent::processAuthStart();
		}
		catch( Exception $e ){
			$this->dieError( "412 Precondition Failed", $e->getMessage(), $e );
		}
    }

	protected function processAuthDone()
	{
		try {
			parent::processAuthDone();
		}
		catch( Exception $e ){
			$this->dieError( "410 Gone", $e->getMessage(), $e );
		}
	}

	public function dieError( $code, $message, $e )
	{
        $get_error = $message;
        include_once(G5_SOCIAL_LOGIN_PATH.'/error.php');
        die();
    }
}