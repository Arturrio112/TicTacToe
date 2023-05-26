<?php


class Tic {
    private $rows;
    private $cols;
    private $lineLength;
    public $currMove = "O";
    public $board = [];
    public $gameOver = false;
    
    public function __construct($rows, $cols, $lineLength) {
        $this->cols = $cols;
        $this->rows = $rows;
        $this->lineLength = $lineLength;
        
        $this->board = array_fill(0, $rows, array_fill(0, $cols, " "));
    }
    
    public function addToBoard($i, $j) {
        if ($this->gameOver==false) {
            $this->board[$i][$j] = $this->currMove;
            $this->currMove = $this->currMove == "O" ? "X" : "O";
        }
    }
    private function array_every($arr, $callback) {
      foreach ($arr as $val) {
          if (!$callback($val)) {
              return false;
          }
      }
      return true;
    }
    private function checkSeq($arr) {
        for($i= 0; $i<=count($arr) -$this->lineLength; $i++){
            
            $seq = array_slice($arr, $i, $this->lineLength);
            
            if($this->array_every($seq, function ($val) use ($seq) {
                if(count($seq)<$this->lineLength){
                    return false;
                }
                return $val === $seq[0] && (!in_array(" ", $seq));
            })){
                return true;
            }
        }
        return false;
    }
    
    private function getNthColumn($n) {
        return array_map(function ($row) use ($n) {
            return $row[$n];
        }, $this->board);
    }
    
    public function hasSomeoneWon($i , $j) {
        if ($this->isColWin() || $this->isRowWin() || $this->isDiogWin($i, $j)) {
            $this->gameOver = true;
            return true;
        } else {
            return false;
        }
    }
    
    private function isRowWin() {
        for ($i = 0; $i < $this->rows; $i++) {
            if ($this->checkSeq($this->board[$i])) {
                return true;
            }
        }
        return false;
    }
    
    private function isColWin() {
        for ($i = 0; $i < $this->cols; $i++) {
            if ($this->checkSeq($this->getNthColumn($i))) {
                return true;
            }
        }
        return false;
    }
    
    private function getDiagonals($row, $col) {
        $n = $this->rows;
        $m = $this->cols;
        $diagonals = [];
        
        for ($i = 0; $i < $n; $i++) {
            for ($j = 0; $j < $m; $j++) {
                if ($i - $j == $row - $col) {
                    $diagonal = [];
                    for ($k = 0; $k < min($n - $i, $m - $j); $k++) {
                        $diagonal[] = $this->board[$i + $k][$j + $k];
                    }
                    if (count($diagonal) >= $this->lineLength) {
                        $diagonals[] = $diagonal;
                    }
                }
            }
        }
        

        for ($i = 0; $i < $n; $i++) {
            for ($j = $m - 1; $j >= 0; $j--) {
                if ($i + $j == $row + $col) {
                    $diagonal = [];
                    for ($k = 0; $k < min($n - $i, $j + 1); $k++) {
                        $diagonal[] = $this->board[$i + $k][$j - $k];
                    }
                    if (count($diagonal) >= $this->lineLength) {
                        $diagonals[] = $diagonal;
                    }
                }
            }
        }
        
        return $diagonals;
    }
    
    private function isDiogWin($row, $col) {
        $diagonals = $this->getDiagonals($row, $col);
        
        for ($i = 0; $i < count($diagonals); $i++) {
            if ($this->checkSeq($diagonals[$i])) {
                return true;
            }
        }
        return false;
    }
    
    public function isBoardFull() {
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                if ($this->board[$i][$j] != "O" && $this->board[$i][$j] != "X") {
                    return false;
                }
            }
        }
        $this->gameOver = true;
        return true;
    }
    public function getFreeSpaceCoor(){
        $array=[];
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                if ($this->board[$i][$j] ===" ") {
                    array_push($array, [$i, $j]);
                }
            }
        }
        if(count($array)>0){
            $coor = $array[rand(0, count($array)-1)];
            return $coor;
        }else {
            return [-1,-1];
        }
        
        
    }
}