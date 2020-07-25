<?php


require_once(__DIR__ . '/vendor/autoload.php');
require_once(__DIR__ . '/config/config.php');
require_once(__DIR__ .'/database/redis.php');
use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;

if ( !$redis->get("loggedIn") ) { 
    header("Location: " . $host);
}
else {
    session_start();
    $platform->auth()->setData( unserialize( $redis->get('accessToken') ) );
    //header("Location: " . $host . '/queues.php');
}

?>