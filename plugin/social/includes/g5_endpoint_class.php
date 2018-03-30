<?php
if (!defined('_GNUBOARD_')) exit;

class G5_Hybrid_Endpoint extends Hybrid_Endpoint
{
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
?>