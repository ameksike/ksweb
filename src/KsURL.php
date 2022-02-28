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
class KsURL 
{
	public function __construct(){ 
		$this->method = array( 
			'heavy'=> array('POST', 'PUT', 'PATCH'), 
			'light'=> array('GET', 'DELETE')
		);
	}
	
	protected function isHeavyMethod($method){
		return in_array(strtoupper($method), $this->method['heavy'] );
	}
	
	/**
	 * @description send HTTP request
	 * @param {ARRAY|STRING} data
	 * @return {STRING} 
	 */
	protected function getOptParam($data){
		return is_string($data) ? $data : http_build_query($data);
	}
	
	/**
	 * @description send HTTP request
	 * @param {ARRAY|STRING} data
	 * @param {STRING} method default POST
	 * @return {STRING} 
	 */
	protected function getOptData($data, $method='POST'){
		if(!$data) return null;
		if($this->isHeavyMethod($method)){
			return is_string($data) ? $data : json_encode($data); 
		}
		return $this->getOptParam($data);
	}
	
	/**
	 * @description send HTTP request
	 * @param {STRING} url [REQUIRED]
	 * @param {STRING} method default GET
	 * @param {ARRAY} headers default ['content-type: application/json']
	 * @param {NUMBER} timeout default 30
	 * @param {NUMBER} maxredirs default 10
	 * @param {ARRAY} data
	 * @param {ARRAY} param
	 * @return {ARRAY} { data: ANY; error: ARRAY; code: NUMBER }
	 */
	public function send($opt=null){
		try {
			if(!$opt) return null;
			$opt = is_string($opt) ? array( 'url' => $opt ) : $opt;
			if(!isset($opt['url'])) return null;
			//... set default values 
			$opt['method'] = isset($opt['method']) ? strtoupper($opt['method']) : 'GET';
			$opt['headers'] = isset($opt['headers']) ? $opt['headers'] : array("content-type: application/json");
			$opt['data'] = isset($opt['data']) ? $this->getOptData($opt['data'], $opt['method']) : '';
			$opt['param'] = isset($opt['param']) ? $this->getOptParam($opt['param']) : '';
			$opt['timeout'] = isset($opt['timeout']) ? $opt['timeout'] : 30;
			$opt['maxredirs'] = isset($opt['maxredirs']) ? $opt['maxredirs'] : 10;
			
			//... add settings
			$options = array(
			  CURLOPT_ENCODING => "",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_MAXREDIRS => $opt['maxredirs'],
			  CURLOPT_TIMEOUT => $opt['timeout'],
			  CURLOPT_CUSTOMREQUEST => $opt['method'],
			  CURLOPT_HTTPHEADER => $opt['headers']
			);
			if($this->isHeavyMethod($opt['method'])) {
				$options[CURLOPT_POSTFIELDS] = $opt['data'];
			}
			if(!empty($opt['param'])){
				$opt['url'] .= '?' . $opt['param'];
			}
			$options[CURLOPT_URL] = $opt['url'];
			
			//... init curl 
			$curl = curl_init();
			curl_setopt_array($curl, $options);
			
			//... save request headers
			$headers = [];    
			curl_setopt($curl, CURLOPT_VERBOSE, 1);
			curl_setopt($curl, CURLOPT_HEADER, 0);
			curl_setopt($curl, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
				$item = explode(':', $header, 2);
				if (count($item) > 1){
					$headers[trim($item[0])] = trim($item[1]);
				}
				return strlen($header);
			});
			
			//... get response 
			$response = curl_exec($curl);
			$response_code = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
			$error = curl_error($curl);
			curl_close($curl);
			
			//... retur values
			return array(
				'status' => !$error ?  'success' : 'failed', 
				'code' => $response_code, 
				'error' => is_string($error) ? $error : json_decode($error, true),
				'data' => is_string($response) ? $response : json_decode($response, true),
				'headers' => $headers,
				'request' => $opt
			);
		}
		catch(Exception $e) {
			return array(
				'status' => 'failed', 
				'code' => 500, 
				'error' => array("message" => $e->getMessage(), "code"=> $e->getCode()),
				'data' => [],
				'request' => $opt
			);
		}
	}
	
	protected function reformat($headers) {
		foreach ($headers as $name => $value) {
			yield "$name: $value";
		}
	}
	
	public function getRequestHeadersFormatted($headers) {
		return iterator_to_array($this->reformat($headers));
	}
	
	/**
	 * @description get request headers
	 */
	public function getRequestHeaders($indexed=true){
		if (function_exists('getallheaders') && $indexed) { 
			return getallheaders();
		}else {
		   // https://www.php.net/manual/en/function.getallheaders.php#84262
		   $headers = array (); 
		   foreach ($_SERVER as $name => $value) { 
			   if (substr($name, 0, 5) == 'HTTP_') { 
					$key = str_replace(' ', '-', ucwords(strtoupper(str_replace('_', ' ', substr($name, 5)))));
					if($indexed){
						$headers[$key] = $value; 
					}else{
						$headers[] = "$key: $value"; 
					}
			   } 
		   } 
		   return $headers; 
		} 
	}
	
	/**
	 * @description get request body
	 */
	public function getRequestBody(){
		return file_get_contents('php://input');
	}
	
	/**
	 * @description get url info 'http://username:password@hostname:9090/path?arg=value#anchor'
	 * @return [
		◦ scheme 	PHP_URL_SCHEME - e.g. http 
		◦ host  	PHP_URL_HOST
		◦ port  	PHP_URL_PORT
		◦ user  	PHP_URL_USER
		◦ pass  	PHP_URL_PASS
		◦ path  	PHP_URL_PATH
		◦ query 	PHP_URL_QUERY	 - after the question mark ?  
		◦ fragment 	PHP_URL_FRAGMENT - after the hashmark # 
	 * ]
	 */
	public function decodeURL($url, $key=false) {
		return $key ? parse_url($url, $key) : parse_url($url);
	}
	
	/**
	 * @description get url string 'http://username:password@hostname:9090/path?arg=value#anchor'
	 * @param [
		◦ scheme 	PHP_URL_SCHEME - e.g. http 
		◦ host  	PHP_URL_HOST
		◦ port  	PHP_URL_PORT
		◦ user  	PHP_URL_USER
		◦ pass  	PHP_URL_PASS
		◦ path  	PHP_URL_PATH
		◦ query 	PHP_URL_QUERY	 - after the question mark ?  
		◦ fragment 	PHP_URL_FRAGMENT - after the hashmark # 
	 * ]
	 * @return {STRING}
	 */
	public function encodeURL($list) {
		if(is_string($list)) return $list;
		if(!is_array($list)) return 'http://127.0.0.1';
		if(isset($list['url'])) return $list['url'];
		if(!isset($list['host'])) return 'http://127.0.0.1';
		$port = isset($list['port']) ? ':' . $list['port'] : '';
		$pass = isset($list['pass']) ? ':' . $list['pass'] : '';
		$user = isset($list['user']) ? $list['user'] . $pass . '@' : '';
		$query = isset($list['query']) ? '?' . $list['query'] : '';
		$fragment = isset($list['fragment']) ? '#' . $list['fragment'] : '';
		$path = isset($list['path']) ? $list['path'] : '';
		$scheme = isset($list['scheme']) ? $list['scheme'] : 'http';
		$host = $list['host'];
		return "$scheme://$user$host$path$query$fragment";
	}
}