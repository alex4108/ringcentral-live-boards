<?php
    require_once("database/redis.php");
    try {
      $loggedIn = $redis->get("loggedIn");
    }
    catch (Exception $e) { 
      $log->critical("Redis error checking if logged in...");
      die();
    }
    if ( !$loggedIn ) { 
      header("Location: " . $host);
      die();
    }
    $pageTitle = "Queues";
    session_start();
    require_once("html/header.php");
  ?>
  <div class="row">
                <div class="col text-center">
                <h1><?php echo $pageTitle; ?></h1>
                <?php
                  $queuesToList = unserialize($redis->get('queues'));
                  if ($queuesToList == null) { 
                    echo("<p><img src='meme.jpg' /></p>");
                  }
                  else {
                    foreach($queuesToList as $queueToList) { 
                      echo("<p><a href=\"/queueView.php?q=" . $queueToList->queueId . "\">" . $queueToList->queueName . "</a></p>");
                    }
                  }
                ?>
                </div>
            </div>
<?php require_once('html/footer.php');
