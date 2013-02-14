#!/usr/bin/php -q
<?php
error_reporting(E_ALL);
ini_set('display_errors','1');
// Run from command prompt > php demo.php
require_once("lib/phpws/phpws/websocket.server.php");
require_once("lib/jaxl/jaxl.php");

/**
 * This demo resource handler will respond to all messages sent to /echo/ on the socketserver below
 *
 * All this handler does is echoing the responds to the user
 * @author Chris
 *
 */
class DemoEchoHandler extends WebSocketUriHandler{
	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg){

		$user->sendMessage($msg);
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $obj){
		$this->say("[DEMO] Admin TEST received!");

		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$user->sendFrame($frame);
	}
}

/**
 * Demo socket server. Implements the basic eventlisteners and attaches a resource handler for /echo/ urls.
 *
 *
 * @author Chris
 *
 */
class SocketServer implements IWebSocketServerObserver{
	protected $debug = true;
	protected $server;
	protected $jabberClients = array();

	public function __construct(){
		$this->server = new WebSocketServer('tcp://0.0.0.0:12345', 'superdupersecretkey');
		$this->server->addObserver($this);
		$this->server->addUriHandler("echo", new DemoEchoHandler());
	}

	public function onConnect(IWebSocketConnection $user){
		$client = new JAXL(array(
			// (required) credentials
			'jid' => 'bazilio@handshake.bazilio',
			'pass' => '31415',
			'host' => 'handshake.bazilio',
			'port' => 5222,
			'log_level' => JAXL_DEBUG,
		));
		$client->connect($client->get_socket_path());
		$client->start_stream();
		$this->jabberClients[$user->getId()] = $client;
		$this->say("[DEMO] {$user->getId()} connected");
	}

	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->jabberClients[$user->getId()]->send_chat_msg('bazilio2@handshake.bazilio', 'Hello World!');
	}

	public function onDisconnect(IWebSocketConnection $user){
		$this->say("[DEMO] {$user->getId()} disconnected");
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg){
		$this->say("[DEMO] Admin Message received!");

		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$user->sendFrame($frame);
	}

	public function say($msg){
		echo "$msg \r\n";
	}

	public function run(){
		$this->server->run();
	}
}

// Start server
$server = new SocketServer();
$server->run();