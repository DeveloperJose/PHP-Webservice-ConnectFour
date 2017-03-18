<?php
/*
 * Author: Jose Perez (80473954)
 * Course: Programming Languages Design and Implementation
 * 
 * board.php
 */
class Board {
   // Public Board Constants
   public static $EMPTY_TOKEN = "#";
   public static $PLAYER_TOKEN = "1";
   public static $CPU_TOKEN = "2";
   public static $WIDTH = 7;
   public static $HEIGHT = 6;
   public static $R_GOOD = 1;
   public static $R_WIN = 2;
   public static $R_DRAW = 3;
   
   // Internal Board Constants
   private static $D_LEFT = 1;
   private static $D_RIGHT = 2;
   private static $D_UP = 3;
   private static $D_DOWN = 4;
   private static $D_LEFT_DIAGONAL_DOWN = 5;
   private static $D_RIGHT_DIAGONAL_DOWN = 6;
   private static $D_LEFT_DIAGONAL_UP = 7;
   private static $D_RIGHT_DIAGONAL_UP = 8;
   
   // Public Properties
   public $grid;
   public $winning_coords = array ();
   
   // Private Properties
   private $token_count = 0;
   private function __construct() {
      for($row = 0; $row < self::$HEIGHT; $row ++)
         for($col = 0; $col < self::$WIDTH; $col ++)
            $this->grid [$row] [$col] = self::$EMPTY_TOKEN;
   }
   
   // Factories
   private static $EMPTY_BOARD = "";
   public static function getEmptyBoard() {
      if (empty ( self::$EMPTY_BOARD ))
         self::$EMPTY_BOARD = new self ();
      
      return self::$EMPTY_BOARD;
   }
   public static function fromPID($pid) {
      $data = json_decode ( file_get_contents ( Constants::$DIR_SAVING . "$pid.txt" ), true );
      $instance = new self ();
      
      $instance->grid = $data ["grid"];
      $instance->winning_coords = $data ["winningCoords"];
      
      return $instance;
   }
   public function toPID($strategy, $pid) {
      $data = array (
            "strategy" => $strategy,
            "grid" => $this->grid,
            "winningCoords" => $this->winning_coords 
      );
      file_put_contents ( Constants::$DIR_SAVING . "$pid.txt", json_encode ( $data ) );
   }
   public function drop_token($column, $token) {
      // Assume client will not allow any out of
      // bounds tokens
      
      // Find the highest row in that column
      // Assume client will not allow placement of
      // a token in a column that is already full
      for($row = self::$HEIGHT - 1; $row >= 0; $row --) {
         // Check if this coordinate is empty
         if ($this->grid [$row] [$column] == self::$EMPTY_TOKEN) {
            $highestRow = $row;
            break;
         }
      }
      
      // Place that token in our internal grid
      $this->grid [$highestRow] [$column] = $token;
      $this->token_count ++;
      
      // Check if the board there is a draw by
      // checking if the board is completely full
      if ($this->token_count == self::$WIDTH * self::$HEIGHT)
         return self::$R_DRAW;
         
         // Check the adjacent tokens to see if
      // this move was a winning move
         // We only need to check 3 adjacent
      // neighbors in every direction
      $gameWon = FALSE;
      
      for($direction = 1; $direction <= 8; $direction ++) {
         $gameWon = $this->check_win ( $highestRow, $column, $token, $direction );
         if ($gameWon) {
            $tempCoords = $this->winning_coords;
            // Push our coordinate to the winning
            // coordinates
            array_push ( $tempCoords, $column, $highestRow );
            $this->winning_coords = $tempCoords;
            return self::$R_WIN;
         }
      }
      
      // The game hasn't been won. Clear winning
      // coords
      $this->winning_coords = array ();
      
      // The move was allowed
      return self::$R_GOOD;
   }
   private function check_win($row, $col, $token, $direction) {
      $tempCoords = array ();
      for($count = 1; $count <= 3; $count ++) {
         $tempRow = $row + ($this->get_row_offset ( $direction ) * $count);
         $tempCol = $col + ($this->get_col_offset ( $direction ) * $count);
         
         if ($this->get_token ( $tempRow, $tempCol ) != $token)
            return false;
         
         array_push ( $tempCoords, $tempCol, $tempRow );
      }
      
      $this->winning_coords = $tempCoords;
      
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
   private function get_token($row, $col) {
      if ($row < 0 || $row >= self::$HEIGHT || $col < 0 || $col >= self::$WIDTH)
         return false;
      
      $val = strval ( $this->grid [$row] [$col] );
      
      return "$val";
   }
   public function print_html() {
      for($row = 0; $row < self::$HEIGHT; $row ++) {
         for($col = 0; $col < self::$WIDTH; $col ++) {
            $val = strval ( $this->grid [$row] [$col] );
            print ("$val ") ;
         }
         print ("<br />") ;
      }
      
      print ("<br />") ;
   }
}
?>