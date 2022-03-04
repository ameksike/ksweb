

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
            'url'=>'https://myweb.com'
        ],
        '/\/api\/v2\/.*/i' => [
            'host' => 'https://jsonplaceholder.typicode.com',
            'scheme'=> 'https',
            'method' => 'POST',
            'action' => '/.*/i',
            'data'=> [
                "email"=>"myemail@gmail.com",
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

## Create a POST request
```php
$ksurl = new KsURL();
$res = $ksurl->send([
    "headers"=>[
        "Authorization: Basic WFhQW8ZZ9CNzZjGVU6TYT4MLKM3CTCTR5YTo0b2pjSlN4bm5maGhEamtFRHU1Vh",
        "Content-Type: application\/x-www-form-urlencoded",
        "Accept: *\/*"
    ],
    "url"=>"https://myweb.com/oauth2/token",
    "data"=>"grant_type=client_credentials&scope=enzona_business_payment+enzona_business_qr",
    "method"=>"POST"
]);
```