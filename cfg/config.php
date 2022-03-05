<?php
//... options
$config["mode"] = "flex";  // strict|flex
$config["log"]['allow'] = ["INFOR", "ERROR", "NAUTH", "NFOUN", "QUERY"]; 

//... security
$config["security"]["secret"] = "ewtertw44t34";
$config["security"]["header"] = "Auth-Ks";
$config["security"]["type"] = "token";

//... router 
$config["routes"]['/^\/api\/v2\/.*/i'] = [
	'host' => 'tropipay-dev.herokuapp-1.com',
	'scheme'=> 'https',
	'method' => 'POST',
	'action' => '/.*/i',
	'data'=> [ "email"=>"tonykssa@gmail.com", "password"=>"***************" ]
];
$config["routes"]['/^\/api\/data\/.*/i'] = [
	'host' => 'jsonplaceholder.typicode.com',
	'scheme'=> 'https',
	'path' => '/posts',
	'method' => 'GET',
	'action' => '/POST|PUT/i'
];
$config["routes"]['/^\/api\/data.*/i']["url"] = "https://jsonplaceholder.typicode.com/comments";
$config["routes"]['/^\/api\/send/i'] = [
	'url' => 'https://jsonplaceholder.typicode.com/comments',
	'method' => 'POST'
];

//...
return $config;