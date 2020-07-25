<?php
require_once(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../config/logger.php');
use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;

$rcsdk = new SDK($RINGCENTRAL_CLIENT_ID, $RINGCENTRAL_CLIENT_SECRET, $RINGCENTRAL_SERVER_URL);
$platform = $rcsdk->platform();

$redis_host = "127.0.0.1";
$redis_port = "6379";

$redis = new Predis\Client(array(
    "scheme" => "tcp",
    "host" => $redis_host,
    "port" => $redis_port));

$timeInRedis = unserialize($redis->get("lastCallSync"));
if ($timeInRedis == null) { 
    $lastCallSyncTime = "NEVER!";
}
else {
    $dt = new DateTime('@' . $timeInRedis);
    $dt->setTimeZone(new DateTimeZone('America/Chicago'));
    $lastCallSyncTime = $dt->format('Y-m-d H:i:s') . " US/Central";
}


$timeInRedis = unserialize($redis->get("lastQueueSync"));
if ($timeInRedis == null) { 
    $lastQueueSyncTime = "NEVER!";
}
else {
    $dt = new DateTime('@' . $timeInRedis);
    $dt->setTimeZone(new DateTimeZone('America/Chicago'));
    $lastQueueSyncTime = $dt->format('Y-m-d H:i:s') . " US/Central";
}

?>