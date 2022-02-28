<?php
	include './src/KsProxyReverse.php';
	
	$server = new KsProxyReverse();
	$server->configure([
		"security" => [
			"secret"=> "ewtertw44t34",
			"header"=> "auth-ks",
			"type"=> "token"
		],
		"routes" => [
			'/^\//i' => [
				'url'=>'https://konukos.nat.cu'
			],
			// http://localhost/index.php/api/v2/access/login
			'/\/api\/v2\/.*/i' => [
				'host' => 'tropipay-dev.herokuapp.com',
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
	
	
	//die($server->isAPIKey("Bearer MjAyMjIwMjJGZWJGZWJNb25Nb246OGNmMjQxNWQyYzM1NmE2ZjRhMzE1YTc3MWMzYTgzZTA=") ? 'SI' : 'NO' ); 
	//die($server->getAPIKey()); // MjAyMjIwMjJGZWJGZWJNb25Nb246OGNmMjQxNWQyYzM1NmE2ZjRhMzE1YTc3MWMzYTgzZTA=
