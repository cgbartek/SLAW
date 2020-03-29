<?php
/**
 * API for internal and external AJAX calls
 *
 * @package    DB
 * @subpackage API
 * @author     Chris Bartek <cgbartek@gmail.com>
 */

header("Access-Control-Allow-Origin: *");
session_start();

require_once('../config.php');

$input = $_REQUEST;

// Find and load API libraries
$libs = glob("*.php");

foreach ($libs as $lib) {
	if($lib != 'index.php' && $lib != 'sandbox.php') {
		require($lib);
	}
}

// Calls a function if it exists and returns the function's return value
function callFunc($func,$arg) {
	$func = "api_".$func; // prevent from executing just any random function
	if (function_exists($func)) {
		die ($func($arg));
	} else {
		die("ERROR: Function $func does not exist.");
	}
}
if($input['action']) {
	callFunc($input['action'],$input);
}
?>
