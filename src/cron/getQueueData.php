<?php

// Log in to the API
require(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../database/redis.php');

use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;
$refreshTime = 60; // Interval of seconds to refresh data on
$holdTime = 90; // Number of seconds to hold in the event of an error

while(true) { 
    $rcsdk = new SDK($RINGCENTRAL_CLIENT_ID, $RINGCENTRAL_CLIENT_SECRET, $RINGCENTRAL_SERVER_URL);
    $platform = $rcsdk->platform();


    if ( ! $redis->get("loggedIn") ) { 
        $log->warning("[cron] getQueueData is exiting because the app is not logged in");
        sleep($refreshTime);
        continue;
    }
    $platform->auth()->setData( unserialize( $redis->get("accessToken") ) );
    if ( ! $platform->loggedIn() ) {
        $platform->refresh();
        $log->info("I refreshed my own token!");
    }

    /* 
    Get techs assigned to each queue, store as queue_${queueId}_techs
    Store as as a serialized array of "tech" objects
    Tech stdObject
    Field extensionNumber
    Field name
    */

    // First get a list of all call queues

    try { 
        $callQueueResponse = $rcsdk->platform()->get('/restapi/v1.0/account/~/call-queues') ;
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        // Getting error messages using PHP native interface
        $log->alert("Error in getting call queue data");
        $log->alert($e);
        print 'RC API Error: ' . $e->getMessage() . PHP_EOL;
        sleep($holdTime);
        continue;
    }



    $queueInfos = array();
    /**
     * Queue Info Object
     * field queueId
     * field queueName
     */
    // For each queue, we need to get a list of techs and drop that into the DB
    foreach( $callQueueResponse->json()->records as $queueInfo ) {  // foreach queue
        $thisQueueMembers = array();
        $thisQueueInfo = new stdClass();
        $thisQueueInfo->queueId = $queueInfo->id;
        $thisQueueInfo->queueName = $queueInfo->name;

        try { 
            $url = "/restapi/v1.0/account/~/call-queues/" . $thisQueueInfo->queueId . "/members";
            $callQueueMembersResponse = $rcsdk->platform()->get($url); // get members
        }
        catch (\RingCentral\SDK\Http\ApiException $e) {
            // Getting error messages using PHP native interface
            $log->alert("Error in getting call queue member data");
            $log->alert($e);
            print 'RC API Error: ' . $e->getMessage() . PHP_EOL;
            sleep($holdTime);
            continue;
        }

        foreach( $callQueueMembersResponse->json()->records as $queueMembersInfo ) { // foreach member
            $thisQueueMemberInfo = new stdClass();

            $thisQueueMemberInfo->id = $queueMembersInfo->id;
            $queueMember = unserialize($redis->get('ext_' . $queueMembersInfo->id));
        
            $thisQueueMemberInfo->extensionNumber = $queueMembersInfo->extensionNumber;
            $thisQueueMemberInfo->name = $queueMember->name;
            array_push($thisQueueMembers, $thisQueueMemberInfo);                // store to this queue
            
        }
        $redis->set("queue_" . $thisQueueInfo->queueId . "_techs", serialize($thisQueueMembers)); // store queue members to cache
        array_push($queueInfos, $thisQueueInfo);
    }
    $log->info("Got queue member details!");

    $redis->set("queues", serialize($queueInfos));
    $redis->set("lastQueueSync", serialize(strtotime("now")));
    $redis->set("accessToken", serialize($platform->auth()->data()));
    sleep($refreshTime);
}

?>