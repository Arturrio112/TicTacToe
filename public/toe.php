<?php

require "./tic.php";


class Game extends Tic{
    private $players;
    
    public function __construct($rows, $cols, $lineLength){
        $this->players = new \SplObjectStorage;
        parent::__construct($rows, $cols, $lineLength);

    }
    public function addPlayer($player){
        $this->players->attach($player);
    }
    public function removePlayer($player) {
        $this->players->detach($player);
    }

    public function getPlayers() {
        return $this->players;
    }

    public function isGameFull() {
        return count($this->players) >= 2;
    }
    
}
require 'vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

class TicTacToe implements MessageComponentInterface {
    private $games;
    private $clients;

    public function __construct() {
        $this->games = [];
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection when it is opened
        $this->clients->attach($conn);
        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onClose(ConnectionInterface $conn) {
        // Remove the connection when it is closed
        $this->clients->detach($conn);
        echo "Connection closed! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $data = json_decode($msg, true);

        if (isset($data['action']) && isset($data['gameId'])) {
            $gameId = $data['gameId'];

            if ($data['action'] === 'join') {
                // Handle player joining the game
                if (isset($this->games[$gameId])) {
                    $game = $this->games[$gameId];

                    if ($game->isGameFull()) {
                        // Notify the client that the game is full
                        $from->send(json_encode(['message' => 'Game is full']));
                    } else {
                        // Add the player to the game
                        $game->addPlayer($from);
                        $from->send(json_encode(['message' => 'You joined the game']));
                    }
                }

            } elseif ($data['action'] === 'move') {
                // Handle player making a move
                $i = $data['i'];
                $j = $data['j'];
                $gameId = $data["gameId"];
                $game = $this->games[$gameId];
                $game->addToBoard($i,$j);
                $game->hasSomeoneWon($i, $j);
                $game->isBoardFull(); 
                
            }elseif ($data['action'] === 'start') {
                // Handle game start
                $gameId = $data['gameId'];
                $rows = $data['rows'];
                $cols = $data['cols'];
                $lineLength = $data['lineLength'];
    
                // Create a new game with the specified dimensions and line length
                $this->games[$gameId] = new Game($rows, $cols, $lineLength);
                $game = $this->games[$gameId];
                $game->addPlayer($from);
                $board = $game->getBoard();
                $this->broadcastGameBoard($gameId, $board);
            }
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        // Handle errors
        echo "An error occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function broadcastGameBoard($gameId, $board) {
        $message = json_encode(['board' => $board]);

        foreach ($this->games[$gameId]->getPlayers() as $player) {
            $player->send($message);
        }
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new TicTacToe()
        )
    ),
    8081
);

$server->run();

?>