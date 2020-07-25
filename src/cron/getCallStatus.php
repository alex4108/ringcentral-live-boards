<?php
require(__DIR__ . '/../vendor/autoload.php');
require_once(__DIR__ . '/../config/config.php');
require_once(__DIR__ . '/../database/redis.php');

use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;
$refreshTime = 15; // Interval of seconds to refresh data on
$holdTime = 90; // Number of seconds to hold in the event of an error


while(true) { 
    
    // Log in to the API
    $rcsdk = new SDK($RINGCENTRAL_CLIENT_ID, $RINGCENTRAL_CLIENT_SECRET, $RINGCENTRAL_SERVER_URL);
    $platform = $rcsdk->platform();

    if ( ! $redis->get("loggedIn") ) { 
        $log->warning("[cron] getCallStatus is exiting because the app is not logged in");
        sleep($holdTime);
        continue;
    }

    // Get authentication
    $platform->auth()->setData( unserialize( $redis->get("accessToken") ) );
    if ( ! $platform->loggedIn() ) {
        $platform->refresh();
        $log->info("I refreshed my own token!");
    }

    try { 
        // Get the call records
        $apiResponse = $rcsdk->platform()->get('/restapi/v1.0/account/~/active-calls', array( "Direction" => "Inbound", "view" => "Detailed")) ;
    }
    catch (\RingCentral\SDK\Http\ApiException $e) {
        // Getting error messages using PHP native interface
        $log->alert($e);
        print 'RC API Error: ' . $e->getMessage() . PHP_EOL;
        sleep($holdTime);
        continue;
    }
    // Store to cache
    $redis->set("callLog", serialize($apiResponse->json()->records));

    // Store the token object back to cache.
    // The RCSDK::platform object will automatically refresh the token if needed.
    // If the token was refreshed, we will need to store the new one
    $log->info("Refreshed the call data!");

    /* Get all extensions
    Store as ext_<extensionId> object in redis
    */
    // Get a list of extensions and store them to redis...
    $tryingExtensionEndpoint = true;
    $tryNumber = 1;
    $sleepTime = $exponential_sleep;
    while($tryingExtensionEndpoint) { 
        try { 
            $extensionsResponse = $rcsdk->platform()->get('/restapi/v1.0/account/~/extension') ;
        }
        catch (\RingCentral\SDK\Http\ApiException $e) {
            // Getting error messages using PHP native interface
            $log->alert("Error in getting extension data");
            $log->alert($e);
            print 'RC API Error: ' . $e->getMessage() . PHP_EOL;
            sleep($holdTime);
            continue;
        }
        foreach ( $extensionsResponse->json()->records as $extensionInfo ) { 
            $thisExtension = new stdClass();
            //$log->debug($extensionInfo);
            if ($extensionInfo->type == "User" && $extensionInfo->status == "Enabled") { 
                //$log->debug("Saving extension # " . $extensionInfo->extensionNumber);
                $thisExtension->name = $extensionInfo->contact->firstName . ' ' . $extensionInfo->contact->lastName;
                $thisExtension->id = $extensionInfo->id;
                $thisExtension->extensionNumber = $extensionInfo->extensionNumber;
                //$log->debug("SETTING EXT " . "ext_" . $thisExtension->id);
                $redis->set("ext_" . $thisExtension->id, serialize($thisExtension));
            }
        }
        $log->info("Got extensions!");
        break;

    }


    $redis->set("lastCallSync", serialize(strtotime("now")));
    $redis->set("accessToken", serialize($platform->auth()->data()));

    sleep($refreshTime);
}
?>
