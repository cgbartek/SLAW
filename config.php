<?php
// S*L*A*W CONFIGURATION

//set_time_limit(0);        // Script time limit
//error_reporting(0);       // Error reporting on

// Dev and Production constants
if ($_SERVER['HTTP_HOST'] == "slawdog.net") {
  define ("DB_SERVER",    "?");
  define ("DB_NAME",      "?");
  define ("DB_USER",      "?");
  define ("DB_PASSWORD",  "?");
  define ("IP",           "?");
} else {
  define ("DB_SERVER",    "localhost");
  define ("DB_NAME",      "slaw");
  define ("DB_USER",      "root");
  define ("DB_PASSWORD",  "");
  define ("IP",           "localhost");
}

// Define autoload classes
spl_autoload_register(function ($class_name) {
  include_once ('core/Game.php');
  include_once ('core/ScreenChannel.php');
  include_once ('core/ScreenStart.php');
  include_once ('core/ScreenAuction.php');
  include_once ('core/ScreenMap.php');
  include_once ('core/ScreenOverview.php');
  include_once ('core/ScreenScore.php');
});

?>
