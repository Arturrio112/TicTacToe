<?php

require '../public/Tic.php';


session_start();

$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$filePath = '../public/index.html';


if ($method == 'GET' && $uri == "/pc") {
  header('Content-Type: text/html');
  header('Content-Disposition: inline; filename="index.html"');
  
  readfile($filePath);
  exit;
}

if ($method == 'GET' && $uri == "/multi") {
  header('Content-Type: text/html');
  header('Content-Disposition: inline; filename="multi.html"');
  
  readfile('../public/multi.html');
  exit;
}

if ($method == 'POST' && $uri == "/pc") {
  $row = $_POST['row'];
  $col = $_POST['col'];
  $line = $_POST['line'];

  $game = new Tic($row, $col, $line);

  
  $_SESSION['game'] = $game;

  echo json_encode($game->board);
}

if ($method == 'PUT' && $uri == "/pc") {
  $inputData = json_decode(file_get_contents('php://input'), true);

  if (isset($inputData['i']) && isset($inputData['j'])) {
    $i = intval($inputData['i']);
    $j = intval($inputData['j']);

    
    $game = $_SESSION['game'];

    $sym = $game->currMove;
    $game->addToBoard($i, $j);
    $win = $game->hasSomeoneWon($i, $j);
    $full = $game->isBoardFull();
    $over = $game->gameOver;
    $coor = $game->getFreeSpaceCoor();
    if($over==false){
      $game->addToBoard($coor[0], $coor[1]);
      $win = $game->hasSomeoneWon($coor[0], $coor[1]);
      $full = $game->isBoardFull();
      $over = $game->gameOver;
      $_SESSION['game'] = $game;
      echo json_encode(["win"=>$win, "full"=>$full, "sym"=>$sym, "gameover"=>$over, "botCoor"=>$coor]);
    }else{
      $_SESSION['game'] = $game;
      echo json_encode(["win"=>$win, "full"=>$full, "sym"=>$sym, "gameover"=>$over, "botCoor"=>[-1,-1]]);
    }
    
  } else {
    http_response_code(400); 
    echo "Missing or invalid data";
  }
}