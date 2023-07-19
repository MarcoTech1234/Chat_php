<?php

namespace Api\Websocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\WebSocket\MessageComponentInterface;

class SistemaChat implements MessageComponentInterface
{
    protected $cliente;
    protected $database;

    public function __construct()
    {
        // Iniciar o objetos que deve armazenar os clientes conectados
        $this->cliente = new \SplObjectStorage();
        
    }

    // Abrir conexão para o novo cliente
    public function onOpen(ConnectionInterface $conn)
    {
        // Adicionar o cliente na lista
        $this->cliente->attach($conn);

        echo "Nova conexão: {$conn->resourceId}. \n\n";
    }

    // Enviar mensagens para os usuário conectados
    public function onMessage(ConnectionInterface $from, $msg)
    {
        //var_dump($msg);
        $this->saveMessageToDatabase($msg);
        // Percorrer a lista de usuários conectados
        foreach($this->cliente as $cliente) {

            // Não enviar a mensagem para o usuário que enviou a mensagem
            if($from !== $cliente) {
                // Enviar as mensagems para os usuários
                $cliente->send($msg);
            }
        }
        
        echo "Usuário {$from->resourceId} enviou uma mensagem. \n\n";
    }

    // Desconectar o cliente do websocket
    public function onClose(ConnectionInterface $conn)
    {
        // Fechar a conexão e retirar o cliente da lista
        $this->cliente->detach($conn);

        echo "Usuário {$conn->resourceId} desconectou. \n\n";
    }

    // Função que será chamada caso ocorra algum erro no websocket
    public function onError(ConnectionInterface $conn, Exception $e)
    {
        // Fechar conexão do cliente
        $conn->close();

        echo "Ocorreu um erro: {$e->getMessage()} \n\n";
    }

    private function saveMessageToDatabase($message) {
    // configurações da conexão do banco 
        $dbname = "chat";
        $hostname = "localhost";
        $username =  "root";
        $pwd = "";
        $messagem = implode( "", json_decode($message, true));
        //print_r($messagem);
        $conn = new \PDO('mysql:host=' .$hostname. ';dbname=' .$dbname, $username, $pwd);
        $sql = "INSERT INTO mensagem (mensagem) VALUES (?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute(array($messagem));
    }
}
