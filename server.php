#!/usr/bin/php -q
<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
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
class DemoEchoHandler extends WebSocketUriHandler
{
	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
	{
		$this->say("[ECHO] {$msg->getData()}");
		// Echo
		$user->sendMessage($msg);
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $obj)
	{
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
class DemoSocketServer implements IWebSocketServerObserver
{
	protected $debug = true;
	protected $server;
	protected $handler = null;

	public function __construct()
	{
		$this->server = new WebSocketServer('tcp://0.0.0.0:12345', 'superdupersecretkey');
		$this->server->addObserver($this);

		$this->server->addUriHandler("echo", new DemoEchoHandler());
	}

	public function onConnect(IWebSocketConnection $user)
	{
		$this->say("[DEMO] {$user->getId()} connected");

		$this->auth($user);

		/*$client->add_cb('on_chat_message', function ($stanza) use ($client) {
			// echo back incoming message stanza
			$stanza->to = $stanza->from;
			$stanza->from = $client->jid->to_string();
			var_dump($stanza);
			$client->send($stanza);
		});

		$client->add_cb('on_disconnect', function () use ($client, $user) {
			_info("got on_disconnect cb");
			$this->say("[DEMO] {$user->getId()} disconnected");
			$user->sendString("Disconected");
		});*/
	}

	protected function auth(IWebSocketConnection $user)
	{
		$client = $this->getClient();

		$client->add_cb('on_auth_success', function () use ($client, $user) {
			$client->send_end_stream();
			$user->sendString("Connected");
			_info("got on_auth_success cb, jid " . $client->full_jid->to_string());
			$this->listen($user);
		});

		$client->add_cb('on_auth_failure', function ($reason) use ($client, $user) {
			$client->send_end_stream();
			$user->sendString("got on_auth_failure cb with reason $reason");
		});

		$client->start();
	}

	protected function listen(IWebSocketConnection $user)
	{
		$client = $this->getClient();

		$client->add_cb('on_chat_message', function ($stanza) use ($client, $user) {
			// echo back incoming message stanza
			$user->sendString("From jabber:" . $stanza->body);
		});

		$client->start();
	}

	protected function getClient()
	{
		$client = new JAXL(array(
			'jid' => 'bazilio2@localhost',
			'pass' => '31415',
			'log_level' => JAXL_DEBUG,
		));

		$client->require_xep(array(
			'0199'
		));

		return $client;
	}

	public function onMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
	{
		// TODO: perform a message queue and bind on JAXL Clock
		return true;
		$this->say("[DEMO] {$user->getId()} says '{$msg->getData()}'");
		$client = $this->getClient();

		$client->add_cb('on_auth_success', function () use ($client, $user, $msg) {
			$client->send_chat_msg('bazilio@localhost', $msg->getData());
			$client->send_chat_msg('bazilio2@localhost', $msg->getData());
			$client->send_end_stream();
			$this->say("[DEMO] sent");
		});

		$client->start();
	}

	public function onDisconnect(IWebSocketConnection $user)
	{
		$this->say("[DEMO] {$user->getId()} disconnected");
	}

	public function onAdminMessage(IWebSocketConnection $user, IWebSocketMessage $msg)
	{
		$this->say("[DEMO] Admin Message received!");

		$frame = WebSocketFrame::create(WebSocketOpcode::PongFrame);
		$user->sendFrame($frame);
	}

	public function say($msg)
	{
		echo "$msg \r\n";
	}

	public function run()
	{
		$this->server->run();
	}
}

// Start server
$server = new DemoSocketServer();
$server->run();