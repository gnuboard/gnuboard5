<?php

	class properties {
        function getMobileAuthUrl($idc_name) {
            $urls = array(
                'fc' => 'https://fcmobile.inicis.com/smart/payReq.ini',
                'ks' => 'https://ksmobile.inicis.com/smart/payReq.ini',
                'stg' => 'https://stgmobile.inicis.com/smart/payReq.ini'
            );
            return is_string($idc_name) && isset($urls[$idc_name]) ? $urls[$idc_name] : '';
        }


		function getAuthUrl($idc_name)	{
            $url = "stdpay.inicis.com/api/payAuth";
			switch ($idc_name) {
				case 'fc':
                    $authUrl = "https://fc".$url;
					break;
				case 'ks':
					$authUrl = "https://ks".$url;
					break;
				case 'stg':
					$authUrl = "https://stg".$url;
					break;
				default:
                    return '';
			}			
			return $authUrl;
		}

		function getNetCancel($idc_name)	{
            $url = "stdpay.inicis.com/api/netCancel";
			switch ($idc_name) {
				case 'fc':
                    $netCancel = "https://fc".$url;
					break;
				case 'ks':
					$netCancel = "https://ks".$url;
					break;
				case 'stg':
					$netCancel = "https://stg".$url;
					break;
				default:
                    return '';
			}			
			return $netCancel;
		}
	}

?>