#!/usr/bin/php -q
<?php
require_once 'AAuth.php';
class CJabberAuth extends AAuth
{

	/**
	 * @return bool
	 */
	function authorized()
	{
		$data = $this->splitcomm(); // This is an array, where each node is part of what SM sent to us :
		// 0 => the command,
		// and the others are arguments .. e.g. : user, server, password ...

		$user = $data[1];
		$serv = $data[2];
		$pass = $data[3];

		return true;
	}
}

error_reporting(0);
$auth = new CJabberAuth();
$auth->play();