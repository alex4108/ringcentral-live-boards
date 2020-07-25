<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$logLevel = Logger::DEBUG;

// Create logger
$log = new Logger("rc-boards");
$log->pushHandler(new StreamHandler('php://stdout', $logLevel)); // <<< uses a stream
