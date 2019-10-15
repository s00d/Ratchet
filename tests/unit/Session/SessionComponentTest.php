<?php
namespace Ratchet\Session;
use GuzzleHttp\Psr7\Request;
use Ratchet\AbstractMessageComponentTestCase;
use Ratchet\Session\SessionProvider;
use RuntimeException;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;


/**
 * @covers Ratchet\Session\SessionProvider
 * @covers Ratchet\Session\Storage\VirtualSessionStorage
 * @covers Ratchet\Session\Storage\Proxy\VirtualProxy
 */
class SessionProviderTest extends AbstractMessageComponentTestCase {
    public function setUp():void {
        $this->markTestIncomplete('Test needs to be updated for ini_set issue in PHP 7.2');

        if (!class_exists(\Symfony\Component\HttpFoundation\Session\Session::class)) {
            $this->markTestSkipped('Dependency of Symfony HttpFoundation failed');
            return;
        }

        parent::setUp();
        $this->_serv = new SessionProvider($this->_app, new NullSessionHandler);
    }

    public function tearDown():void {
//        ini_set('session.serialize_handler', 'php');
    }

    public function getConnectionClassString() {
        return \Ratchet\ConnectionInterface::class;
    }

    public function getDecoratorClassString() {
        return \Ratchet\NullComponent::class;
    }

    public function getComponentClassString() {
        return \Ratchet\MessageComponentInterface::class;
    }

    public function classCaseProvider() {
        return array(
            array('php', 'Php'),
            array('php_binary', 'PhpBinary')
        );
    }

    /**
     * @dataProvider classCaseProvider
     */
    public function testToClassCase($in, $out) {
        $ref = new \ReflectionClass(\Ratchet\Session\SessionProvider::class);
        $method = $ref->getMethod('toClassCase');
        $method->setAccessible(true);


        $componentMock = $this->getMockBuilder($this->getComponentClassString())->getMock();
        $sessionHandlerMock = $this->getMockBuilder('\SessionHandlerInterface')->getMock();
        $component = new SessionProvider($componentMock, $sessionHandlerMock);
        $this->assertEquals($out, $method->invokeArgs($component, array($in)));
    }

    /**
     * I think I have severely butchered this test...it's not so much of a unit test as it is a full-fledged component test
     */
    public function testConnectionValueFromPdo() {
        if (!extension_loaded('PDO') || !extension_loaded('pdo_sqlite')) {
            return $this->markTestSkipped('Session test requires PDO and pdo_sqlite');
        }

        $sessionId = md5('testSession');

        $dbOptions = array(
            'db_table'    => 'sessions'
          , 'db_id_col'   => 'sess_id'
          , 'db_data_col' => 'sess_data'
          , 'db_time_col' => 'sess_time'
          , 'db_lifetime_col' => 'sess_lifetime'
        );

        $pdo = new \PDO("sqlite::memory:");
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->exec(vsprintf("CREATE TABLE %s (%s TEXT NOT NULL PRIMARY KEY, %s BLOB NOT NULL, %s INTEGER NOT NULL, %s INTEGER)", $dbOptions));

        $pdoHandler = new PdoSessionHandler($pdo, $dbOptions);
        $pdoHandler->write($sessionId, '_sf2_attributes|a:2:{s:5:"hello";s:5:"world";s:4:"last";i:1332872102;}_sf2_flashes|a:0:{}');

        $component  = new SessionProvider($this->createMock(\Ratchet\MessageComponentInterface::class), $pdoHandler, array('auto_start' => 1));
        $connection = $this->createMock(\Ratchet\ConnectionInterface::class);

        $headers = $this->createMock(Request::class);
        $headers->expects($this->once())->method('getCookie')->with(array(ini_get('session.name')))->willReturn($sessionId);

        $connection->WebSocket          = new \StdClass;
        $connection->WebSocket->request = $headers;

        $component->onOpen($connection);

        $this->assertEquals('world', $connection->Session->get('hello'));
    }

    protected function newConn() {
        $conn = $this->createMock(\Ratchet\ConnectionInterface::class);

        $headers = $this->createMock(Request::class);
//        $headers->expects($this->once())->method('getCookie')->willReturn(null);

        $conn->WebSocket          = new \StdClass;
        $conn->WebSocket->request = $headers;

        return $conn;
    }

    public function testOnMessageDecorator() {
        $message = "Database calls are usually blocking  :(";
        $this->_app->expects($this->once())->method('onMessage')->with($this->isExpectedConnection(), $message);
        $this->_serv->onMessage($this->_conn, $message);
    }

    public function testGetSubProtocolsReturnsArray() {
        $mock = $this->createMock(\Ratchet\MessageComponentInterface::class);
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $this->assertInternalType('array', $comp->getSubProtocols());
    }

    public function testGetSubProtocolsGetFromApp() {
        $mock = $this->createMock(\Ratchet\WebSocket\Stub\WsMessageComponentInterface::class);
        $mock->expects($this->once())->method('getSubProtocols')->will($this->returnValue(array('hello', 'world')));
        $comp = new SessionProvider($mock, new NullSessionHandler);

        $this->assertGreaterThanOrEqual(2, count($comp->getSubProtocols()));
    }
}
