<?php
/**
 * @author		Antonio Membrides Espinosa
 * @email		tonykssa@gmail.com
 * @package    	Ksike Server
 * @date		15/07/2020
 * @copyright  	Copyright (c) 2020-2050
 * @license    	GPL
 * @version    	1.0
 * @require 	KsURL
 */
class KsProxyReverse 
{
	public function __construct(){
		if (!class_exists('KsURL')) {
			require_once(__DIR__.'/KsURL.php');
		}
		$this->http = new KsURL();
		$this->cfg = array( 'routes' => array() );
	}
	
	public function configure($options){
		$this->cfg = $options;
		return $this;
	}
	
	/**
	 * @description generate API key base64(code : md5( code - secret ) )
	 * @param {STRING} code
	 * @param {STRING} secret
	 * @return {STRING}
	 */
	public function getAPIKey($code=null, $secret=null){
		try{
			$code = $code ? $code : date('YYMMDD');
			$secret = $secret ? $secret : ( isset($this->cfg['security']) && isset($this->cfg['security']['secret']) ? $this->cfg['security']['secret'] : 'ksike') ;
			return base64_encode($code.":".md5($code."-".$secret));
		}
		catch(Exception $e) {
			return '';
		}
	}
	
	/**
	 * @description generate API key base64
	 * @param {STRING} code
	 * @param {STRING} secret
	 * @return {BOOLEAN}
	 */
	public function isAPIKey($token, $secret=null){
		try{
			$secret = $secret ? $secret : ( isset($this->cfg['security']) && isset($this->cfg['security']['secret']) ? $this->cfg['security']['secret'] : 'ksike') ;
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
	
	/**
	 * @description verify security 
	 * @param {ARRAY} headers
	 * @return {BOOLEAN}
	 */
	public function auth($headers=[]){
		if(!isset($this->cfg['security'])){
			return true;
		}
		$type = !isset($this->cfg['security']['type']) ? 'token' : $this->cfg['security']['type'];
		if($type == 'token'){
			$header = !isset($this->cfg['security']['header']) ? 'Auth-Ks' : $this->cfg['security']['header'];
			if(empty($headers) || !isset($headers[$header])) {
				return false;
			}
			return $this->isAPIKey($headers[$header]);
		}
		return true;
	}
	
	/**
	 * @description get route options
	 * @param {ARRAY} server 
	 * @return {ARRAY}
	 */
	public function getRoute($server=null){
		$routes = $this->cfg['routes'];
		$server = $server ? $server : $_SERVER;
		$url = isset($server['PATH_INFO']) ? $server['PATH_INFO'] : '/';
		foreach ($routes as $pattern => $route) {
			$route['method'] = isset($route['method']) ? $route['method'] : 'GET';
			$route['action'] = isset($route['action']) ? $route['action'] : '/'.$route['method'] . '/';
			$pattern = isset($route['pattern']) ? $route['pattern'] : $pattern;
			if(preg_match($pattern, $url) && preg_match($route['action'], $server['REQUEST_METHOD'])){
				return $route;
			}
		}
		return [];
	}
		
	protected function getTargetHost($url) {
		return $this->http->decodeURL($url, PHP_URL_HOST);
	}
	
	protected function getTargetUrl($target){
		$target['path'] = isset($target['path']) ? $target['path'] : (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/' );
		return $this->http->encodeURL($target);
	}
	
	protected function getTargetHeaders($url, $headers=null){
		$request_headers = $headers ? $headers : $this->http->getRequestHeaders();
		$request_headers['Host'] = $this->getTargetHost($url);
		//$request_headers['X-Forwarded-Host'] = $_SERVER['SERVER_NAME'];
		unset($request_headers['Content-Length']);
		return $this->http->getRequestHeadersFormatted($request_headers);
	}

	public function process(){
		$target = $this->getRoute($_SERVER);
		$headers = $this->http->getRequestHeaders();
		unset($headers['Content-Length'], $headers['Accept-Encoding']);
			
		if(!$this->auth($headers)){
			return [];
		}
		if(!$target) return [];
		if(isset($target['data'])){
			$body = json_decode($this->http->getRequestBody(), true);
			$target['data'] = is_array($body) ? array_merge($target['data'], $body) : $target['data'];
		}else {
			$target['data'] = $this->http->getRequestBody();
		}
		
		$target['url'] = $this->getTargetUrl($target);
		$target['headers'] = $this->getTargetHeaders($target['url'], $headers);
		return $this->http->send($target);
	}
	
	public function sendCode($code){
		http_response_code($code);
	}	

	public function sendHeaders($headers, $indexed=true){
		$avoid = array(
			"content-encoding",
			"transfer-encoding",
			"content-length"
		);
		foreach($headers as $name => $value){
			if(!in_array(strtolower($name), $avoid )){
				if($indexed){ 
					header("$name: $value", false) ;
				} else {
					header($value, false) ;
				}
			}
		}
	}
	
	public function respond($data){
		die(is_string($data) ? $data : ($data !== null ? json_encode($data) : ''));
	}
	
	public function start(){
		try {
			$res = $this->process();
			if(empty($res)) {
				$this->sendCode(404);
				$this->respond('{ "error": { "message": "Not found" } }');
			}
			if(isset($res['headers'])){
				$this->sendHeaders($res['headers']);
			}
			if(isset($res['data'])){
				$this->sendCode($res['code']);
				$this->respond($res['data']);
			}else{
				$this->sendCode(500);
				$this->respond($res['error']);
			}
		}
		catch(Exception $e) {
			$this->sendCode(500);
			$this->respond(json_encode(array(
					'error' => array(
						"message" => $e->getMessage(), 
						"code"=> $e->getCode()
					)
			)));
		}
	}
}