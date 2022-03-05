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
class KsLog 
{
    public function __construct(){
		$this->srv = $_SERVER;
		$this->cfg = array();
	}

	/**
	 * @description configure logs
	 */
    public function configure($cfg, $srv=null){
        $this->srv = $srv ? $srv : $this->srv;
        $this->cfg = $cfg ? $cfg : $this->cfg;
        return $this;
    }
	
	/**
	 * @description is allow 
	 * @return {BOOLEAN}
	 */
	public function isAllow($name) {
		if(!$this->cfg) return true;
		if(!is_array($this->cfg['allow'])) return true;
		if(!isset($this->cfg['allow'])) return true;
		return in_array(strtoupper($name), $this->cfg['allow']);
	}
	
	/**
	 * @description generate logs
	 * @param {OBJECT} target
	 * @param {STRING} label
	 */
	public function save($target=null, $label='INFOR'){
		$label = strtoupper($label);
		if(!$this->isAllow($label)){
			return null;
		}
		date_default_timezone_set('UTC');
		$name = $label . '-' . date("ymd");
		$path = isset($this->cfg['path']) ? $this->cfg['path'] :  __DIR__ . '/../log/';
		if($target){
			unset($target['headers']);
		}
		$data["date"] = date("y-m-d H:i:s");
		$data["host"] = $this->srv["REMOTE_ADDR"]; 
		$data["agent"] = isset($this->srv["HTTP_USER_AGENT"]) ? $this->srv["HTTP_USER_AGENT"] : 'none';
		$data["method"] = $this->srv["REQUEST_METHOD"];
		$data["path"] = $this->srv["REQUEST_URI"];
		$data["target"] = $target;
		file_put_contents("$path/$name.log", json_encode($data).',', FILE_APPEND | LOCK_EX);
	}
}