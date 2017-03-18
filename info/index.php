<?php
/*
 * Author: Jose Perez (80473954)
 * Course: Programming Languages Design and Implementation
 *
 * info/index.php
 */
require_once ("..\constants\strategy.php");
require_once ("..\constants\board.php");

$output = array (
      "width" => Board::$WIDTH,
      "height" => Board::$HEIGHT,
      "strategies" => Strategy::$STRATEGIES 
);
echo json_encode ( $output );

?>