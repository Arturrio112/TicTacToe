<?php

require "./Tic.php";
require "../vendor/autoload.php";

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;


$allowedOrigins = [
    'ws://localhost:8081',  
    'http://tic.local:8080',
    '*',
    "ws://localhost:8081"   
];

class MyWebSocket implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        
        $this->clients->attach($conn);

        echo "New client connected: " . $conn->resourceId . "\n";

        // Check if the origin is allowed
        if ($this->isOriginAllowed($conn->httpRequest->getHeader('Origin'))) {
            echo "Origin not allowed\n";
            $conn->close();
            return;
        }

        
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        
        $message = json_decode($msg, true);
        if (isset($message['header']) && $message['header'] === 'make') {
            echo $message['header'];
            
            
            $response = array(
                'header' => 'make',
                'message' => 'Received "make" message'
            );
            
            $from->send(json_encode($response));
        }
        
       
    }

    public function onClose(ConnectionInterface $conn)
    {
       
        $this->clients->detach($conn);

        echo "Client disconnected: " . $conn->resourceId . "\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
       
        echo "Error occurred on client " . $conn->resourceId . ": " . $e->getMessage() . "\n";

        $conn->close();
    }

    private function isOriginAllowed($origin)
    {
        global $allowedOrigins;
        return in_array($origin, $allowedOrigins);
    }
}

$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new MyWebSocket()
        )
    ),
    8081
);

$server->run();
?>