

## Define your own server with separate configuration file
```php
include __DIR__.'/../src/KsProxyReverse.php';
$config = include __DIR__.'/../cfg/config.php';
$server = new KsProxyReverse();
$server->configure($config)->start();
```

## Define your own server with implicit configuration
```php
$server = new KsProxyReverse();

$server->configure([
    "security" => [
        "secret"=> "123321456987",
        "header"=> "Auth-Ks",
        "type"=> "token"
    ],
    "routes" => [
        '/^\//i' => [
            'url'=>'https://konukos.nat.cu'
        ],
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
```