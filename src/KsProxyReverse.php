<?php
/**
 * @description Proxy Reverse
 * @author		Antonio Membrides Espinosa
 * @email		tonykssa@gmail.com
 * @package    	Ksike Server
 * @date		15/07/2020
 * @copyright  	Copyright (c) 2020-2050
 * @license    	GPL
 * @version    	1.0
 * @require 	KsURL, KsSec, KsLog
 */
class KsProxyReverse 
{
	/**
	 * @description initialize server
	 */
	public function __construct(){
		$this->load('KsURL');
		$this->load('KsSec');
		$this->load('KsLog');

		$this->http = new KsURL();
		$this->sec = new KsSec();
		$this->log = new KsLog();
		
		$this->cfg = array( 'routes' => array() );
		$this->srv = $_SERVER;
	}

	/**
	 * @description load class if not exists
	 * @param {STRING} name
	 */
	public function load($name) {
		if (!class_exists($name)) {
			require_once(__DIR__."/$name.php");
		}
	}
	
	/**
	 * @description configure server
	 * @param {STRING} name
	 */
	public function configure($options){
		$this->cfg = $options;
		$this->sec->configure(
			isset($this->cfg['security']) && isset($this->cfg['security']['secret']) ? 
			$this->cfg['security']['secret'] : 
			'ksike'
		);
		$this->log->configure(
			isset($this->cfg['log']) ? $this->cfg['log'] : [], 
			$this->srv
		);
		return $this;
	}

	/**
	 * @description get if server is in strict mode
	 * @return {BOOLEAN}
	 */
	public function istrict(){
		return isset($this->cfg['mode']) ? ($this->cfg['mode'] == 'strict' ? true : false) : false;
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
			return $this->sec->verify($headers[$header]);
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
		$server = $server ? $server : $this->srv;
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

	/**
	 * @description get target host 
	 * @param {STRING} url 
	 * @return {STRING}
	 */
	protected function getTargetHost($url) {
		return $this->http->decodeURL($url, PHP_URL_HOST);
	}

	/**
	 * @description get target url 
	 * @param {ARRAY} target 
	 * @return {STRING}
	 */
	protected function getTargetUrl($target){
		$target['path'] = isset($target['path']) ? $target['path'] : (isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '/' );
		return $this->http->encodeURL($target);
	}

	/**
	 * @description get target headers from indexed list 
	 * @param {STRING} url 
	 * @param {ARRAY} headers 
	 * @return {ARRAY}
	 */
	protected function getTargetHeaders($url, $headers=null){
		$request_headers = $headers ? $headers : $this->http->getRequestHeaders();
		$request_headers['Host'] = $this->getTargetHost($url);
		//$request_headers['X-Forwarded-Host'] = $_SERVER['SERVER_NAME'];
		unset($request_headers['Content-Length']);
		return $this->http->getRequestHeadersFormatted($request_headers);
	}

	/**
	 * @description process request
	 * @return {OBJECT}
	 */
	public function process(){
		$target = $this->getRoute($_SERVER);
		if(empty($target)){
			$this->log->save();
			return [];
		}
		$headers = $this->http->getRequestHeaders();
		unset($headers['Content-Length'], $headers['Accept-Encoding']);
		$target['auth'] = $this->auth($headers);
		if(!$target['auth']){
			$this->log->save($target);
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
		$target['query'] = $this->srv['QUERY_STRING'];
		$this->log->save($target);
		return $this->http->send($target);
	}

	/**
	 * @description set response code
	 */
	public function sendCode($code){
		http_response_code($code);
	}	

	/**
	 * @description set response headers
	 */
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

	/**
	 * @description send user information
	 */
	public function respond($data){
		$out = is_string($data) ? $data : ($data !== null ? json_encode($data) : '');
		echo $out;
		if($this->istrict()){
			die();
		}
	}
	
	/**
	 * @description start service listener
	 */
	public function start(){
		try {
			$res = $this->process();
			if(empty($res)) {
				if(!$this->istrict()){
					return null;
				}
				$this->sendCode(404);
				$this->respond(json_encode(array(
					'error' => array(
						"message" => "Not found"
					)
				)));
			}else{
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