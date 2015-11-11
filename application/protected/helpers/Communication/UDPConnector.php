<?php

/**
 * Class UDPConnector
 *
 */
class UDPConnector extends BaseComponent
{
    const DEFAULT_TIMEOUT = 1;

    /** @var  resource */
    protected $server;
    /** @var  array */
    protected $server_param;

    /** @var  resource */
    protected $client;
    /** @var  array */
    protected $client_param;

    /**
     * @param Logger $logger
     * @param array  $server
     * @param array  $client
     */
    public function __construct($logger, $server, $client)
    {
        parent::__construct($logger);

        $this->server_param = $server;
        $this->client_param = $client;
    }

    /**
     * Init server stream
     *
     * @return null|resource
     */
    public function getServer()
    {
        if (!$this->server) {
            $this->_logger->log(__METHOD__ . ' Init server: ' . $this->getServerAddress());
            $this->server = null;

            if ($this->getServerParams()) {
                if ($this->server = stream_socket_server($this->getServerAddress(), $errno, $errstr, STREAM_SERVER_BIND)
                ) {
                    $this->_logger->log(__METHOD__ . ' Server is init');
                } else {
                    $this->_logger->log(__METHOD__ . " Server is not init: $errstr($errno)");
                }
            } else {
                $this->_logger->log(__METHOD__ . ' Server is not init: not set param');
            }
        }

        return $this->server;
    }

    /**
     * Init client stream
     *
     * @return null|resource
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->_logger->log(__METHOD__ . ' Init client: ' . $this->getClientAddress());
            $this->client = null;

            if ($this->getClientParams()) {
                if ($this->client = stream_socket_client($this->getClientAddress(), $errno, $errstr)) {
                    $this->_logger->log(__METHOD__ . ' Client is init');
                } else {
                    $this->_logger->log(__METHOD__ . " Client is not init: $errstr($errno)");
                }
            } else {
                $this->_logger->log(__METHOD__ . ' Client is not init: not set param');
            }
        }

        return $this->client;
    }

    /**
     * @return array
     */
    public function getServerParams()
    {
        return $this->server_param;
    }

    /**
     * @return array
     */
    public function getClientParams()
    {
        return $this->client_param;
    }

    /**
     * @param int $timeout
     *
     * @return array|false
     */
    public function read($timeout = self::DEFAULT_TIMEOUT)
    {
        if ($this->getServer()) {
            $read = [$this->getServer()];
            $write = $except = [];

            if (stream_select($read, $write, $except, $timeout, 0) > 0
                && $messages = stream_socket_recvfrom($this->getServer(), 1000, 0, $from)
            ) {
                $this->server_param['last_read_data'] = time();
                return [
                    'messages' => $messages,
                    'from'     => $from,
                ];
            } else {
                return [];
            }
        } else {
            $this->_logger->log(__METHOD__ . ' Read data: server is undefined');
            return false;
        }
    }

    /**
     * @param string $data
     *
     * @return bool
     */
    public function send($data)
    {
        if ($this->getClient()) {
            if (stream_socket_sendto($this->getClient(), $data)) {
                return true;
            }
        } else {
            $this->_logger->log(__METHOD__ . ' Send data: client is undefined');
        }
        return false;
    }

    /**
     * @return string
     */
    protected function getServerAddress()
    {
        return "udp://{$this->server_param['ip']}:{$this->server_param['port']}";
    }

    /**
     * @return string
     */
    protected function getClientAddress()
    {
        return "udp://{$this->client_param['ip']}:{$this->client_param['port']}";
    }

}