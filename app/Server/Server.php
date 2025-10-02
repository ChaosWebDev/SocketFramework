<?php
namespace App\Server;

use App\Handlers\Command;

class Server
{
    protected array $config;
    protected $socket = null;
    protected array $clients = [];
    protected bool $running = false;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'type' => env("SERVER_TYPE", "telnet"),
            'host' => env("SOCKET_HOST", "0.0.0.0"),
            'port' => env("SOCKET_PORT", 8023),
            'max_clients' => env("MAX_CLIENTS", 10),
        ], $config);
    }

    public function start(): void
    {
        $host = $this->config['host'];
        $port = (int) $this->config['port'];
        $address = "tcp://{$host}:{$port}";

        $this->socket = @stream_socket_server($address, $errno, $errstr);
        if ($this->socket === false) {
            throw new \RuntimeException("Unable to create socket: {$errstr} ({$errno})");
        }

        // Set non-blocking for accept loop
        stream_set_blocking($this->socket, false);

        $this->running = true;
        $this->log("Listening on {$host}:{$port}");

        // Main accept / io loop
        while ($this->running) {
            // Accept any new connections (non-blocking)
            $client = @stream_socket_accept($this->socket, 0);
            if ($client !== false) {
                stream_set_blocking($client, false);
                $id = (int) $client;
                $this->clients[$id] = [
                    'resource' => $client,
                    'buffer' => '',
                    'meta' => stream_get_meta_data($client),
                ];
                $this->log("Client connected: {$id}");
                $this->onClientConnect($id);
            }

            // Prepare arrays for stream_select
            $read = [];
            foreach ($this->clients as $id => $c) {
                $read[] = $c['resource'];
            }

            if ($read) {
                $write = null;
                $except = null;
                $tv_sec = 0;
                // use @ to suppress warnings on closed sockets
                if (@stream_select($read, $write, $except, $tv_sec, 200000)) {
                    foreach ($read as $r) {
                        $id = (int) $r;
                        $data = @fread($r, 8192);
                        if ($data === false || $data === '') {
                            // client closed
                            $this->log("Client {$id} disconnected.");
                            $this->disconnectClient($id);
                            continue;
                        }
                        $this->onClientData($id, $data);
                    }
                }
            }

            // small sleep so loop isn't 100% CPU
            usleep(100000); // 0.1s
        }

        $this->shutdown();
    }

    public function stop(): void
    {
        $this->running = false;
    }

    protected function onClientConnect(int $id): void
    {
        $this->sendToClient($id, "Welcome to Chaos server. Type 'quit' to disconnect.\r\n> ");
    }

    protected function onClientData(int $id, string $data): void
    {
        $data = trim($data, "\r\n");
        $this->log("Received from {$id}: " . $data);

        $response = Command::handle($data);

        // echo back for now
        $this->sendToClient($id, "{$response}\r\n> ");
    }

    protected function sendToClient(int $id, string $message): void
    {
        if (!isset($this->clients[$id]))
            return;
        $r = $this->clients[$id]['resource'];
        @fwrite($r, $message);
    }

    protected function disconnectClient(int $id): void
    {
        if (!isset($this->clients[$id]))
            return;
        $r = $this->clients[$id]['resource'];
        @fclose($r);
        unset($this->clients[$id]);
    }

    protected function shutdown(): void
    {
        $this->log("Shutting down server...");
        foreach ($this->clients as $id => $c) {
            @fwrite($c['resource'], "Server is shutting down.\r\n");
            @fclose($c['resource']);
        }
        $this->clients = [];
        if ($this->socket) {
            @fclose($this->socket);
            $this->socket = null;
        }
    }

    protected function log(string $msg): void
    {
        // Simple logging to stdout - replace with your logger
        echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
    }
}
