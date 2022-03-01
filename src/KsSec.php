<?php
/**
 * @author		Antonio Membrides Espinosa
 * @email		tonykssa@gmail.com
 * @package    	Ksike Client HTTP
 * @date		15/07/2020
 * @copyright  	Copyright (c) 2020-2050
 * @license    	GPL
 * @version    	1.0
 */
class KsSec 
{
    public function __construct(){
		if (!class_exists('KsURL')) {
			require_once(__DIR__.'/KsURL.php');
		}
		$this->http = new KsURL();
		$this->secret = '123654321';
	}

    public function configure($secret){
        $this->secret = $secret;
        return $this;
    }

	/**
	 * @description generate API key base64(code : md5( code - secret ) )
	 * @param {STRING} code
	 * @param {STRING} secret
	 * @return {STRING}
	 */
	public function generate($code=null, $secret=null){
		try{
			$code = $code ? $code : date('YYMMDD');
			$secret = $secret ? $secret : $this->secret;
			return base64_encode($code.":".md5($code."-".$secret));
		}
		catch(Exception $e) {
			return '';
		}
	}
	
	/**
	 * @description verify API key base64
	 * @param {STRING} code
	 * @param {STRING} secret
	 * @return {BOOLEAN}
	 */
	public function verify($token, $secret=null){
		try{
			$secret = $secret ? $secret : $this->secret;
			$token = explode(' ', $token, 2);
			if(!isset($token[1])) return false;
			$token = base64_decode($token[1]);
			$token = explode(':', $token, 2);
			if(!isset($token[1])) return false;
			return $token[1] == md5($token[0]."-".$secret);
		}
		catch(Exception $e) {
			return false;
		}
	}
}