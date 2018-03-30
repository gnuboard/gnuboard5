<?php
/**
 * This is a PHP library that handles calling reCAPTCHA.
 *
 * @copyright Copyright (c) 2015, Google Inc.
 * @link      http://www.google.com/recaptcha
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

if ( ! class_exists( 'ReCaptchaResponse_v' ) ){
    class ReCaptchaResponse_v
    {
        public $success;
        public $errorCodes;
    }
}

class ReCaptcha_GNU
{
    /**
     * Version of this client library.
     * @const string
     */
    const VERSION = 'php_1.1.2';
    
    private static $_signupUrl = 'https://www.google.com/recaptcha/admin';
    private static $_siteVerifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * Shared secret for the site.
     * @var string
     */
    private $secret;

    /**
     * Create a configured instance to use the reCAPTCHA service.
     *
     * @param string $secret shared secret between site and reCAPTCHA server.
     * @param RequestMethod $requestMethod method used to send the request. Defaults to POST.
     * @throws \RuntimeException if $secret is invalid
     */
    public function __construct($secret)
    {
        if (empty($secret)) {
            throw new Exception('No secret provided');
        }

        if (!is_string($secret)) {
            throw new Exception('The provided secret must be a string');
        }

        $this->secret = $secret;
    }

    public function get_content($url, $data=array()) {

        $curlsession = curl_init();
        curl_setopt ($curlsession, CURLOPT_URL, $url);
        curl_setopt ($curlsession, CURLOPT_POST, 1);
        curl_setopt ($curlsession, CURLOPT_POSTFIELDS, http_build_query($data, '', '&'));
        curl_setopt ($curlsession, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt ($curlsession, CURLINFO_HEADER_OUT, false);
        curl_setopt ($curlsession, CURLOPT_HEADER, false);
        curl_setopt ($curlsession, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($curlsession, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt ($curlsession, CURLOPT_TIMEOUT, 3);

        $response = curl_exec($curlsession);
        $cinfo = curl_getinfo($curlsession);
        curl_close($curlsession);

        if ($cinfo['http_code'] != 200){
            return '';
        }
        return $response;
    }

    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param string $path url path to recaptcha server.
     * @param array  $data array of parameters to be sent.
     *
     * @return array response
     */
    private function submit($url, $data)
    {
        $response = $this->get_content($url, $data);
        return $response;
    }

    /**
     * Calls the reCAPTCHA siteverify API to verify whether the user passes
     * CAPTCHA test.
     *
     * @param string $remoteIp   IP address of end user.
     * @param string $response   response string from recaptcha verification.
     *
     * @return ReCaptchaResponse_v
     */
    public function verify($response, $remoteIp = null)
    {
        // Discard empty solution submissions
        if ($response == null || strlen($response) == 0) {
            $recaptchaResponse = new ReCaptchaResponse_v();
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = 'missing-input';
            return $recaptchaResponse;
        }
        $getResponse = $this->submit(
            self::$_siteVerifyUrl,
            array (
                'secret' => $this->secret,
                'remoteip' => $remoteIp,
                'version' => self::VERSION,
                'response' => $response
            )
        );
        $answers = $getResponse ? json_decode($getResponse, true) : array();
        $recaptchaResponse = new ReCaptchaResponse_v();
        if (isset($answers['success']) && $answers['success'] == true) {
            $recaptchaResponse->success = true;
        } else {
            $recaptchaResponse->success = false;
            $recaptchaResponse->errorCodes = isset($answers['error-codes']) ? $answers['error-codes'] : 'http_error';
        }
        return $recaptchaResponse;
    }
}
?>