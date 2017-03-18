<?php
/*
 * Author: Jose Perez (80473954)
 * Course: Programming Languages Design and Implementation
 * 
 * new/index.php
 */
require_once ("../constants/strategy.php");
require_once ("../constants/board.php");

// Fetch the strategy
$strategy = isset ( $_GET ['strategy'] ) ? $_GET ['strategy'] : "";

// Place to store our response
$response = array ();

if (empty ( $strategy )) {
   // No strategy provided
   $response = array (
         "response" => false,
         "reason" => "Strategy not specified." 
   );
} else if (! in_array_ignore_case ( $strategy, Strategy::$STRATEGIES )) {
   // Strategy is not valid
   $response = array (
         "response" => false,
         "reason" => "Unknown strategy." 
   );
} else {
   $pid = uniqid ();
   $response = array (
         "response" => true,
         "pid" => $pid 
   );
   
   $emptyBoard = Board::getEmptyBoard ();
   $emptyBoard->toPID ( $strategy, $pid );
}

echo json_encode ( $response );

// Case-insensitive array searching
// Code by Kelvin J
// http://php.net/manual/en/function.in-array.php
function in_array_ignore_case($needle, $haystack) {
   return in_array ( strtolower ( $needle ), array_map ( 'strtolower', $haystack ) );
}
?>