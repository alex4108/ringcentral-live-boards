<?php
require_once(__DIR__ . '/vendor/autoload.php');
use RingCentral\SDK\Http\HttpException;
use RingCentral\SDK\Http\ApiResponse;
use RingCentral\SDK\SDK;
require_once ('config/config.php');
require_once ('database/redis.php');


if ( $redis->get("loggedIn") ) { 
  header("Location: " . $host . '/queues.php');
}
session_start();

// Using the authUrl to call the platform function
$url = $platform->authUrl(array(
          'redirectUri' => $RINGCENTRAL_REDIRECT_URL,
          'state' => 'initialState',
          'brandId' => '',
          'display' => '',
          'prompt' => ''
        ));



?>

  <?php
    $pageTitle = "Login";
    require_once("html/header.php");
  ?>
  <div class="row">
                <div class="col text-center">
                <h1>Welcome!</h1>
                <p>Please log in to RingCentral</p>
                <p><a href="<?php echo $url; ?>">Click here to log in</a></p>
                </div>
            </div>
  <?php require_once("html/footer.php") ?>