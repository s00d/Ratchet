<?php
namespace Ratchet\Http;
use Psr\Http\Message\RequestInterface;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

interface HttpServerInterface extends MessageComponentInterface {
    /**
     * @param \Ratchet\ConnectionInterface $conn
     * @param RequestInterface $request null is default because PHP won't let me overload; don't pass null!!!
     */
    public function onOpen(ConnectionInterface $conn, RequestInterface $request = null);
}
