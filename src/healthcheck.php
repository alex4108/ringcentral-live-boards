<?php

require_once(__DIR__ . '/database/redis.php');
require_once(__DIR__ . '/config/logger.php');
$output = '';
function throw500($msg, $log) { 
    header("HTTP/1.1 500 Internal Server Error");
    $log->alert($msg);
    die($msg);
}
try {
    $redis->connect();
} catch(Predis\Connection\ConnectionException $e) {
    throw500("Can't get to redis", $log);
}
$output .= "redis connect passed\n";


$loggedIn = $redis->get("loggedIn");
if ( ! $loggedIn ) { 
    throw500( "Not logged in", $log );
}

else {
    $output .= "logged in passed\n";
}

$lastCronRun = unserialize($redis->get("lastCallSync"));
$lastCronExpected = strtotime("-2 minutes");

if ($lastCronExpected > $lastCronRun) {
    throw500( "lastCallSync isn't running!", $log );    
}
else {
    $output .= "lastCallSync passed\n";
}


$lastCronRun = unserialize($redis->get("lastQueueSync"));
$lastCronExpected = strtotime("-2 minutes");

if ($lastCronExpected > $lastCronRun) {
    throw500( "lastQueueSync isn't running!", $log );    
}

else {
    $output .= "queue sync passed\n";
}

$output .=('OK');
echo $output;
?>