<?php
/*
 * Author: Jose Perez (80473954)
 * Course: Programming Languages Design and Implementation
 * 
 * play/index.php
 */
require_once ("../constants/strategy.php");
require_once ("../constants/board.php");

$pid = isset ( $_GET ['pid'] ) ? $_GET ['pid'] : "";
$move = isset ( $_GET ['move'] ) ? $_GET ['move'] : "";
// Parse the move as an int if possible.
$move = is_numeric ( $move ) ? intval ( $move ) : $move;

if (empty ( $pid ))
   $response = array (
         "response" => false,
         "reason" => "Pid not specified" 
   );

else if (! file_exists ( Constants::$DIR_SAVING . "$pid.txt" ))
   $response = array (
         "response" => false,
         "reason" => "Unknown pid" 
   );
   
   // PHP considers 0 to be empty so we can't just
   // use empty()
else if ($move === "")
   $response = array (
         "response" => false,
         "reason" => "Move not specified" 
   );

else if (! is_int ( $move ))
   $response = array (
         "response" => false,
         "reason" => "$move is not a number" 
   );
else if ($move < 0 || $move >= Board::$WIDTH)
   $response = array (
         "response" => false,
         "reason" => "Invalid slot, $move" 
   );

else {
   // Reconstruct the board
   $board = Board::fromPID ( $pid );
   
   // Place the player's token
   $result = $board->drop_token ( $move, Board::$PLAYER_TOKEN );
   
   if ($result == Board::$R_GOOD) {
      // Play our strategy
      $strategy = Strategy::fromPID ( $pid );
      $cpuColumn = $strategy->selectColumn ( $board, $move );
      
      $response = array (
            "response" => true,
            "ack_move" => array (
                  "slot" => $move,
                  "isWin" => false,
                  "isDraw" => false,
                  "row" => array () 
            ),
            "move" => cpu_move_array ( $board, $cpuColumn ) 
      );
      
      // Save our progress
      $board->toPID ( $strategy->toString (), $pid );
   } else {
      $response = array (
            "response" => true,
            "ack_move" => array (
                  "slot" => $move,
                  "isWin" => $result == Board::$R_WIN,
                  "isDraw" => $result == Board::$R_DRAW,
                  "row" => ($result == Board::$R_WIN) ? $board->winning_coords : array () 
            ) 
      );
      
      // Delete the game. It's over
      unlink ( Constants::$DIR_SAVING . "$pid.txt" );
   }
}

echo json_encode ( $response );
function cpu_move_array($board, $cpuColumn) {
   $cpuResult = $board->drop_token ( $cpuColumn, Board::$CPU_TOKEN );
   
   return array (
         "slot" => $cpuColumn,
         "isWin" => $cpuResult == Board::$R_WIN,
         "isDraw" => $cpuResult == Board::$R_DRAW,
         "row" => ($cpuResult == Board::$R_WIN) ? $board->winning_coords : array () 
   );
}
?>