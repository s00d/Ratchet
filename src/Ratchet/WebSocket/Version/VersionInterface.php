<?php
namespace Ratchet\WebSocket\Version;
use Psr\Http\Message\RequestInterface;
use Ratchet\MessageInterface;
use Ratchet\ConnectionInterface;

/**
 * A standard interface for interacting with the various version of the WebSocket protocol
 */
interface VersionInterface extends MessageInterface {
    /**
     * Given an HTTP header, determine if this version should handle the protocol
     * @param RequestInterface $request
     * @return bool
     */
    function isProtocol(RequestInterface $request);

    /**
     * Although the version has a name associated with it the integer returned is the proper identification
     * @return int
     */
    function getVersionNumber();

    /**
     * Perform the handshake and return the response headers
     * @param RequestInterface $request
     * @return \Guzzle\Http\Message\Response
     */
    function handshake(RequestInterface $request);

    /**
     * @param  \Ratchet\ConnectionInterface $conn
     * @param  \Ratchet\MessageInterface    $coalescedCallback
     * @return \Ratchet\ConnectionInterface
     */
    function upgradeConnection(ConnectionInterface $conn, MessageInterface $coalescedCallback);

    /**
     * @return MessageInterface
     */
    //function newMessage();

    /**
     * @return FrameInterface
     */
    //function newFrame();

    /**
     * @param string
     * @param bool
     * @return string
     * @todo Change to use other classes, this will be removed eventually
     */
    //function frame($message, $mask = true);
}
