<?php
/*
 * Author: Jose Perez (80473954)
 * Course: Programming Languages Design and Implementation 
 * 
 * strategy.php
 */
require_once ("constants.php");
abstract class Strategy {
   public static $STRATEGIES = array (
         "Smart",
         "Random" 
   );
   public static function fromPID($pid) {
      $data = json_decode ( file_get_contents ( Constants::$DIR_SAVING . "$pid.txt" ), true );
      
      if (strtolower ( $data ["strategy"] ) == "smart")
         return new SmartStrategy ();
      
      else
         return new RandomStrategy ();
   }
   public abstract function selectColumn($board, $playerMove);
   public abstract function toString();
}
class SmartStrategy extends Strategy {
   // Internal Board Constants
   private static $D_LEFT = 1;
   private static $D_RIGHT = 2;
   private static $D_UP = 3;
   private static $D_DOWN = 4;
   private static $D_LEFT_DIAGONAL_DOWN = 5;
   private static $D_RIGHT_DIAGONAL_DOWN = 6;
   private static $D_LEFT_DIAGONAL_UP = 7;
   private static $D_RIGHT_DIAGONAL_UP = 8;
   public function selectColumn($board, $playerMove) {
      $playerMove = intval ( $playerMove );
      $grid = $board->grid;
      
      // $board->print_html();
      // Check if we can win somewhere
      for($col = 0; $col < Board::$WIDTH; $col ++) {
         for($row = Board::$HEIGHT - 1; $row >= 0; $row --) {
            $token = $this->get_token ( $grid, $row, $col );
            
            // We found one of our tokens
            if ($token == Board::$CPU_TOKEN) {
               // Down
               $upOne = $this->get_token ( $grid, $row + 1, $col );
               $downOne = $this->get_token ( $grid, $row + 1, $col );
               $downTwo = $this->get_token ( $grid, $row + 1, $col );
               $matchingTokens = $downOne == Board::$CPU_TOKEN && $downTwo == Board::$CPU_TOKEN;
               
               if ($upOne == Board::$EMPTY_TOKEN && $matchingTokens) {
                  // print "downwin";
                  return $col;
               }
               
               // Left
               $leftOne = $this->get_token ( $grid, $row, $col - 1 );
               $leftTwo = $this->get_token ( $grid, $row, $col - 2 );
               
               $matchingTokens = ($leftOne == Board::$CPU_TOKEN && $leftTwo == Board::$CPU_TOKEN);
               // Bottom row
               if ($row == Board::$HEIGHT - 1) {
                  $leftThree = $this->get_token ( $grid, $row, $col - 3 );
                  $matchingTokens &= ($leftThree == Board::$EMPTY_TOKEN);
               } else {
                  $leftThreeDownOne = $this->get_token ( $grid, $row + 1, $col - 3 );
                  $matchingTokens &= ($leftThreeDownOne == Board::$PLAYER_TOKEN || $leftThreeDownOne == Board::$CPU_TOKEN);
               }
               
               if ($matchingTokens) {
                  // print "leftwin";
                  return $col - 3;
               }
               
               // Right
               $rightOne = $this->get_token ( $grid, $row, $col + 1 );
               $rightTwo = $this->get_token ( $grid, $row, $col + 2 );
               
               $matchingTokens = ($rightOne == Board::$CPU_TOKEN && $rightTwo == Board::$CPU_TOKEN);
               // Bottom row
               if ($row == Board::$HEIGHT - 1) {
                  $rightThree = $this->get_token ( $grid, $row, $col + 3 );
                  $matchingTokens &= ($rightThree == Board::$EMPTY_TOKEN);
               } else {
                  $rightThreeDownOne = $this->get_token ( $grid, $row + 1, $col + 3 );
                  $matchingTokens &= ($rightThreeDownOne == Board::$PLAYER_TOKEN || $rightThreeDownOne == Board::$CPU_TOKEN);
               }
               
               if ($matchingTokens) {
                  // print "rightwin";
                  return $col + 3;
               }
            }
         }
      }
      
      // Find what row the player placed their
      // token in
      for($row = 0; $row < Board::$HEIGHT; $row ++) {
         if ($grid [$row] [$playerMove] == Board::$PLAYER_TOKEN) {
            $highestRow = $row;
            break;
         }
      }
      // print "$playerMove, $highestRow <br />";
      // Check if the player might win in any
      // direction
      for($direction = 1; $direction <= 8; $direction ++) {
         $gameWon = $this->check_win ( $grid, $highestRow, $playerMove, Board::$PLAYER_TOKEN, $direction );
         if ($gameWon) {
            // The player might win to the left
            // Check if placing a token will make
            // them win
            if ($direction == self::$D_LEFT) {
               // print "Left <br/ >";
               if ($highestRow == Board::$HEIGHT - 1) {
                  // Check if we can place our
                  // token
                  $slotAvailable = $this->get_token ( $grid, $highestRow, $playerMove + 1 ) == Board::$EMPTY_TOKEN;
                  return ($slotAvailable) ? $playerMove + 1 : 0;
               }
               
               $slot = $this->get_token ( $grid, $highestRow + 1, $playerMove + 1 );
               $slotAvailable = $slot == Board::$PLAYER_TOKEN || $slot == Board::$CPU_TOKEN;
               if ($slotAvailable)
                  return $playerMove + 1;
            } else if ($direction == self::$D_RIGHT) {
               // print "Right <br/ >";
               // Check if we are at the bottom
               if ($highestRow == Board::$HEIGHT - 1) {
                  // Check if we can place our
                  // token
                  $slotAvailable = $this->get_token ( $grid, $highestRow, $playerMove - 1 ) == Board::$EMPTY_TOKEN;
                  return ($slotAvailable) ? $playerMove - 1 : 0;
               }
               
               $slot = $this->get_token ( $grid, $highestRow + 1, $playerMove - 1 );
               $slotAvailable = $slot == Board::$PLAYER_TOKEN || $slot == Board::$CPU_TOKEN;
               
               if ($slotAvailable)
                  return $playerMove - 1;
            } else if ($direction == self::$D_LEFT_DIAGONAL_UP) {
               $slot = $this->get_token ( $grid, $highestRow - 2, $playerMove - 3 );
               $slotAvailable = $slot == Board::$PLAYER_TOKEN || $slot == Board::$CPU_TOKEN;
               if ($slotAvailable)
                  return $playerMove - 3;
            } else if ($direction == self::$D_RIGHT_DIAGONAL_UP) {
               $slot = $this->get_token ( $grid, $highestRow - 2, $playerMove + 3 );
               $slotAvailable = $slot == Board::$PLAYER_TOKEN || $slot == Board::$CPU_TOKEN;
               if ($slotAvailable)
                  return $playerMove + 3;
            } else if ($direction == self::$D_LEFT_DIAGONAL_DOWN) {
               $slot = $this->get_token ( $highestRow + 3, $playerMove - 3 );
               $slotAvailable = $slot == Board::$PLAYER_TOKEN || $slot == Board::$CPU_TOKEN;
               if ($slotAvailable)
                  return $palyerMove - 3;
            } else if ($direction == self::$D_RIGHT_DIAGONAL_DOWN) {
               $slot = $this->get_token ( $highestRow + 3, $playerMove + 3 );
               $slotAvailable = $slot == Board::$PLAYER_TOKEN || $slot == Board::$CPU_TOKEN;
               if ($slotAvailable)
                  return $playerMove + 3;
            } else if ($direction == self::$D_UP) {
               // Player will win by placing a
               // token in the same column
               // Check if we can put it
               // print "down";
               $slotAvailable = $this->get_token ( $grid, $highestRow - 1, $playerMove ) == Board::$EMPTY_TOKEN;
               if ($slotAvailable)
                  return $playerMove;
            }
         }
      }
      
      // We aren't in danger of losing. Select
      // something near the center
      // Find the columns that are not full
      $columns = array (
            3,
            2,
            4,
            1,
            5,
            0,
            6 
      );
      
      foreach ( $columns as $col ) {
         if ($grid [0] [$col] == Board::$EMPTY_TOKEN && $playerMove != $col)
            return $col;
      }
      return - 2;
   }
   public function check_win($grid, $row, $col, $token, $direction) {
      for($count = 1; $count <= 2; $count ++) {
         $tempRow = $row + ($this->get_row_offset ( $direction ) * $count);
         $tempCol = $col + ($this->get_col_offset ( $direction ) * $count);
         
         if ($this->get_token ( $grid, $tempRow, $tempCol ) != $token)
            return false;
      }
      return true;
   }
   private function get_row_offset($direction) {
      switch ($direction) {
         case self::$D_UP :
         case self::$D_LEFT_DIAGONAL_UP :
         case self::$D_RIGHT_DIAGONAL_UP :
            return 1;
         
         case self::$D_DOWN :
         case self::$D_LEFT_DIAGONAL_DOWN :
         case self::$D_RIGHT_DIAGONAL_DOWN :
            return - 1;
      }
      return 0;
   }
   private function get_col_offset($direction) {
      switch ($direction) {
         case self::$D_RIGHT :
         case self::$D_RIGHT_DIAGONAL_DOWN :
         case self::$D_RIGHT_DIAGONAL_UP :
            return 1;
         
         case self::$D_LEFT :
         case self::$D_LEFT_DIAGONAL_DOWN :
         case self::$D_LEFT_DIAGONAL_UP :
            return - 1;
      }
      return 0;
   }
   private function get_token($grid, $row, $col) {
      if ($row < 0 || $row >= Board::$HEIGHT || $col < 0 || $col >= Board::$WIDTH)
         return - 1;
      
      return $grid [$row] [$col];
   }
   public function toString() {
      return "Smart";
   }
}
class RandomStrategy extends Strategy {
   public function selectColumn($board, $playerMove) {
      // Find the columns that are not full
      $grid = $board->grid;
      
      $availableColumns = array ();
      for($col = 0; $col < Board::$WIDTH; $col ++) {
         
         if ($grid [0] [$col] == Board::$EMPTY_TOKEN) {
            array_push ( $availableColumns, $col );
         }
      }
      // Pick one randomly
      return array_rand ( $availableColumns, 1 );
   }
   public function toString() {
      return "Random";
   }
}
?>