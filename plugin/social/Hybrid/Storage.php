<?php

/**
 * HybridAuth
 * http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
 * (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html 
 */
require_once realpath(dirname(__FILE__)) . "/StorageInterface.php";

/**
 * HybridAuth storage manager
 */
class Hybrid_Storage implements Hybrid_Storage_Interface {
    public static $stores = array();

	/**
	 * Constructor
	 */
	function __construct() {
		if (!session_id()) {
			if (!session_start()) {
				throw new Exception("Hybridauth requires the use of 'session_start()' at the start of your script, which appears to be disabled.", 1);
			}
		}

		$this->config("php_session_id", session_id());
		$this->config("version", Hybrid_Auth::$version);
	}

	/**
	 * Saves a value in the config storage, or returns config if value is null
	 *
	 * @param string $key   Config name
	 * @param string $value Config value
	 * @return array|null
	 */
	public function config($key, $value = null) {
		$key = strtolower($key);

		if ($value) {
			$serialize_value = function_exists('get_string_encrypt') ? get_string_encrypt(serialize($value)) : serialize($value);

			if( in_array($key, array('php_session_id', 'config')) ){
				$this->stores[$key] = $serialize_value;
			} else {
				$_SESSION["HA::CONFIG"][$key] = $serialize_value;
			}
		} elseif (isset($this->stores[$key])) {
			$unserialize_value = function_exists('get_string_decrypt') ? unserialize(get_string_decrypt($this->stores[$key])) : unserialize($this->stores[$key]);
			return $unserialize_value;
		} elseif (isset($_SESSION["HA::CONFIG"][$key])) {
			$unserialize_value = function_exists('get_string_decrypt') ? unserialize(get_string_decrypt($_SESSION["HA::CONFIG"][$key])) : unserialize($_SESSION["HA::CONFIG"][$key]);
			return $unserialize_value;
		}

		return null;
	}

	/**
	 * Returns value from session storage
	 *
	 * @param string $key Key
	 * @return string|null
	 */
	public function get($key) {
		$key = strtolower($key);

		if (isset($_SESSION["HA::STORE"], $_SESSION["HA::STORE"][$key])) {
			$unserialize_value = function_exists('get_string_decrypt') ? unserialize(get_string_decrypt($_SESSION["HA::STORE"][$key])) : unserialize($_SESSION["HA::STORE"][$key]);
			return $unserialize_value;
		}

		return null;
	}

	/**
	 * Saves a key value pair to the session storage
	 *
	 * @param string $key   Key
	 * @param string $value Value
	 * @return void
	 */
	public function set($key, $value) {
		$key = strtolower($key);
		$serialize_value = function_exists('get_string_encrypt') ? get_string_encrypt(serialize($value)) : serialize($value);
		$_SESSION["HA::STORE"][$key] = $serialize_value;
	}

	/**
	 * Clear session storage
	 * @return void
	 */
	function clear() {
		$_SESSION["HA::STORE"] = array();
	}

	/**
	 * Delete a specific key from session storage
	 *
	 * @param string $key Key
	 * @return void
	 */
	function delete($key) {
		$key = strtolower($key);

		if (isset($_SESSION["HA::STORE"], $_SESSION["HA::STORE"][$key])) {
			$f = $_SESSION['HA::STORE'];
			unset($f[$key]);
			$_SESSION["HA::STORE"] = $f;
		}
	}

	/**
	 * Delete all keys recursively from session storage
	 *
	 * @param string $key Key
	 * @retun void
	 */
	function deleteMatch($key) {
		$key = strtolower($key);

		if (isset($_SESSION["HA::STORE"]) && count($_SESSION["HA::STORE"])) {
			$f = $_SESSION['HA::STORE'];
			foreach ($f as $k => $v) {
				if (strstr($k, $key)) {
					unset($f[$k]);
				}
			}
			$_SESSION["HA::STORE"] = $f;
		}
	}

	/**
	 * Returns session storage as a serialized string
	 * @return string|null
	 */
	function getSessionData() {
		if (isset($_SESSION["HA::STORE"])) {
			return serialize($_SESSION["HA::STORE"]);
		}
		return null;
	}

	/**
	 * Restores the session from serialized session data
	 * 
	 * @param string $sessiondata Serialized session data
	 * @return void
	 */
	function restoreSessionData($sessiondata = null) {
		$_SESSION["HA::STORE"] = unserialize($sessiondata);
	}

}