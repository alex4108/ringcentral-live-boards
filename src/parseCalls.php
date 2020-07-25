<?php

require_once('./database/redis.php');

$queues = array(); // The queue objects to parse for
$queueInfos = unserialize($redis->get('queues'));
$callLog = unserialize($redis->get("callLog"));

foreach($queueInfos as $queue) { 
    if ($queue->queueId == $q) { 
        array_push($queues, $queue);
        $queue_name = $queue->queueName; 
    }
}

$thisQueue = (object) [ 
    'calls' => array(),
    'queue_name' => $queue_name
];

if (!is_array($callLog)) {
    $msg = "WARNING: RC Boards has not gotten any data from RingCentral.  Dashboards will not be usable!";
    print_r($msg);
    $log->warning($msg);
    //die();
}

foreach( $queues as $queue ) {
    /** This parses the inbound calls off the call log for the queue */
    foreach( $callLog as $call ) { 
        $pushToList = false;
        $thisCall = new stdClass();
        if ( $call->type == "Voice" && $call->direction == "Inbound") { // Inbound call
            
            foreach( $call->legs as $leg ) { 
                if ( property_exists($leg, 'type') ) { 
                    $start = new DateTime( $call->startTime );
                    $thisCall->duration = $start->diff( new DateTime('NOW') )->format( '%I:%S' );
                    if ( isset( $call->from->name ) ) { 
                        $thisCall->callerIdName = str_replace($queue->queueName . ' - ', '', $call->from->name);
                        if ($thisCall->callerIdName == '') { 
                            $thisCall->callerIdName = $call->from->location;    
                        }
                    }
                    else {
                        $thisCall->callerIdName = $call->from->location;
                    }
                    if ( isset( $call->from->phoneNumber ) ) { 
                        $thisCall->callerIdNumber = $call->from->phoneNumber;
                    }
                    else if ( isset ( $call->from->extensionNumber ) ) { 
                        $thisCall->callerIdNumber = $call->from->extensionNumber;
                    }
                    else {
                        $thisCall->callerIdNumber = "PRIVATE";
                    }
                    

                    if ( isset($leg->to->name) && $leg->result == "In Progress" && $leg->action == "Phone Call" && $leg->to->name == $queue->queueName ) { 
                        $thisCall->status = "Ringing";
                        $pushToList = true;
                    }
    
                    if ( ( $leg->action == "VoIP Call" || $leg->action == "FindMe" ) && $leg->direction == "Outbound" && $leg->result == "In Progress" && $pushToList && $leg->reason == "Accepted" ) { 
                        $thisCall->status = "Talking to " . $leg->from->name;
                        $pushToList = true;
                    }
                }
    
            } 
    
            if ( $pushToList ) { 
                array_push( $thisQueue->calls, $thisCall );
            }
        } 
    
    }

        
    // TODO: Populate this from queue members APIs
    $techs = unserialize($redis->get("queue_" . $queue->queueId . "_techs"));
    $outboundCalls = array();
    /**
     * Outbound call object
     * toNumber
     * duration
     */
    foreach( $techs as $tech ) { 
        foreach( $callLog as $call ) {
            $thisCall = new stdClass(); 
            if ( isset($call->from->extensionNumber) && $call->direction == "Outbound" && $call->from->extensionNumber == $tech->extensionNumber && $call->result == "In Progress" ) { // Only outbound calls from techs
                if ( isset( $call->to->phoneNumber ) ) { 
                    $thisCall->toNumber = $call->to->phoneNumber;
                }
                
                else if ( isset ( $call->to->extensionNumber ) ) { 
                    $thisCall->toNumber = $call->to->extensionNumber;
                }
                else {
                    $thisCall->toNumber = "UNKNOWN";
                }

                $start = new DateTime( $call->startTime );
                $thisCall->duration = $start->diff( new DateTime('NOW') )->format( '%I:%S' );
                $thisCall->fromName = $tech->name;
                $thisCall->fromNumber = $tech->extensionNumber;
                array_push($outboundCalls, $thisCall);
            }
        }
    }

}
