<?php
	include './src/KsProxyReverse.php';

	$server = new KsProxyReverse();
	$server->configure([
		"security" => [
			"secret"=> "ewtertw44t34",
			"header"=> "Auth-Ks",
			"type"=> "token"
		],
		"routes" => [
			'/^\//i' => [
				'url'=>'https://konukos.nat.cu'
			],
			// http://localhost/index.php/api/v2/access/login
			'/\/api\/v2\/.*/i' => [
				'host' => 'tropipay-dev.herokuapp-1.com',
				'scheme'=> 'https',
				'method' => 'POST',
				'action' => '/.*/i',
				'data'=> [
					"email"=>"tonykssa@gmail.com",
					"password"=>"****************"
				]
			],
			// http://localhost/index.php/api/data/tieso
			'/^\/api\/data\/.*/i' => [
				'host' => 'jsonplaceholder.typicode.com',
				'scheme'=> 'https',
				'path' => '/posts',
				'method' => 'GET',
				'action' => '/POST|PUT/i'
			],
			'/^\/api\/data.*/i' => [
				'url' => 'https://jsonplaceholder.typicode.com/comments',
				'method' => 'GET'
			],
			'/^\/api\/send/i' => [
				'url' => 'https://jsonplaceholder.typicode.com/comments',
				'method' => 'POST'
			]
		]
	]);
	
	$server->start();