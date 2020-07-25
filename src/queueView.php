<?php
  
   require_once "checkLoggedIn.php";
    if (!isset($_GET['q'])) { 
        header("Location: " . $host . "/queues.php");
    }
    else {
        $q = $_GET['q'];
    }
    require_once 'parseCalls.php';
    $pageTitle = $thisQueue->queue_name;
    require_once "html/header.php";
    require_once "database/redis.php";
  ?>
  <div class="row">
                <div class="col text-center">
                <h1><?php echo $queue_name; ?></h1>
                </div>
            </div>
            <h2>Inbound</h2>
            <table class="table">
            <thead>
                <tr>
                <th scope="col">From</th>
                <th scope="col">Duration</th>
                <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach( $thisQueue->calls as $call ) { 
                        echo '<tr><td><a href="rcmobile://call?number=' . $call->callerIdNumber . '">' . $call->callerIdName . '</a> (' . $call->callerIdNumber . ')</td><td>' . $call->duration . '</td><td>' . $call->status . '</td></tr>';
                    }
                ?>
            </tbody>
            </table>

            
            <h2>Outbound</h2>
            <table class="table">
            <thead>
                <tr>
                <th scope="col">To</th>
                <th scope="col">Duration</th>
                <th scope="col">From</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    foreach( $outboundCalls as $call ) { 
                        echo '<tr><td><a href="rcmobile://call?number=' . $call->toNumber . '">' . $call->toNumber . '</a></td><td>' . $call->duration . '</td><td><a href="rcmobile://call?number=' . $call->fromNumber . '">' . $call->fromName . ' (' . $call->fromNumber . ')</a></tr>';
                    }
                ?>
            </tbody>
            </table>

            
        
<?php require_once('html/footer.php');