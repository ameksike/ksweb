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

    public function configure($cfg, $srv=null){
        $this->srv = $srv ? $srv : $this->srv;
        $this->cfg = $cfg ? $cfg : $this->cfg;
        return $this;
    }

	/**
	 * @description generate logs
	 */
	public function save($target=null){
		date_default_timezone_set('UTC');

		$name = date("ymd");
		$path = __DIR__ . '/../' . (isset($this->cfg['log']) ? $this->cfg['log'] : 'log/');

		$data["date"] = date("y-m-d H:i:s");
		$data["host"] = $this->srv["REMOTE_ADDR"];//  $this->srv["HTTP_HOST"];
		$data["agent"] = $this->srv["HTTP_USER_AGENT"];
		$data["method"] = $this->srv["REQUEST_METHOD"];
		$data["path"] = $this->srv["REQUEST_URI"];
		$data["target"] = $target;
		file_put_contents("$path/$name.log", json_encode($data).',', FILE_APPEND | LOCK_EX);
	}
}