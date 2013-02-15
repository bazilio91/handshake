<?php

abstract class AAuth
{
	var $debug = false; /* Debug mode */
	var $debugfile = "/var/log/pipe-debug.log"; /* Debug output */
	var $logging = false; /* Do we log requests ? */
	var $logfile = "/var/log/pipe-log.log"; /* Log file ... */
	/*
	 * For both debug and logging, ejabberd have to be able to write.
	 */

	var $jabber_user; /* This is the jabber user passed to the script. filled by $this->command() */
	var $jabber_pass; /* This is the jabber user password passed to the script. filled by $this->command() */
	var $jabber_server; /* This is the jabber server passed to the script. filled by $this->command(). Useful for VirtualHosts */
	var $jid; /* Simply the JID, if you need it, you have to fill. */
	var $data; /* This is what SM component send to us. */

	var $dateformat = "M d H:i:s"; /* Check date() for string format. */
	var $command; /* This is the command sent ... */
	var $stdin; /* stdin file pointer */
	var $stdout; /* stdout file pointer */

	public function JabberAuth()
	{
		@define_syslog_variables();
		@openlog("pipe-auth", LOG_NDELAY, LOG_SYSLOG);

		if ($this->debug) {
			@error_reporting(E_ALL);
			@ini_set("log_errors", "1");
			@ini_set("error_log", $this->debugfile);
		}
		$this->logg("Starting pipe-auth ..."); // We notice that it's starting ...
		$this->openstd();
	}

	public function stop()
	{
		$this->logg("Shutting down ..."); // Sorry, have to go ...
		closelog();
		$this->closestd(); // Simply close files
		exit(0); // and exit cleanly
	}

	public function openstd()
	{
		$this->stdout = @fopen("php://stdout", "w"); // We open STDOUT so we can read
		$this->stdin = @fopen("php://stdin", "r"); // and STDIN so we can talk !
	}

	public function readstdin()
	{
		$l = @fgets($this->stdin, 3); // We take the length of string
		$length = @unpack("n", $l); // ejabberd give us something to play with ...
		$len = $length["1"]; // and we now know how long to read.
		if ($len > 0) { // if not, we'll fill logfile ... and disk full is just funny once
			$this->logg("Reading $len bytes ... "); // We notice ...
			$data = @fgets($this->stdin, $len + 1);
			// $data = iconv("UTF-8", "ISO-8859-15", $data); // To be tested, not sure if still needed.
			$this->data = $data; // We set what we got.
			$this->logg("IN: " . $data);
		}
	}

	public function closestd()
	{
		@fclose($this->stdin); // We close everything ...
		@fclose($this->stdout);
	}

	public function out($message)
	{
		@fwrite($this->stdout, $message); // We reply ...
		$dump = @unpack("nn", $message);
		$dump = $dump["n"];
		$this->logg("OUT: " . $dump);
	}

	public function splitcomm() // simply split command and arugments into an array.
	{
		return explode(":", $this->data);
	}

	public function logg($message) // pretty simple, using syslog.
		// some says it doesn't work ? perhaps, but AFAIR, it was working.
	{
		if ($this->logging) {
			@syslog(LOG_INFO, $message);
		}
	}

	public function play()
	{
		do {
			$this->readstdin(); // get data
			$length = strlen($this->data); // compute data length
			if($length > 0 ) { // for debug mainly ...
				$this->logg("GO: ".$this->data);
				$this->logg("data length is : ".$length);
			}
			$ret = $this->command(); // play with data !
			$this->logg("RE: " . $ret); // this is what WE send.
			$this->out($ret); // send what we reply.
			$this->data = NULL; // more clean. ...
		} while (true);
	}


	/**
	 * @return mixed packed msg
	 */
	public function command() {
		if ($this->authorized()) { // you can authorize
			sleep(1);
			return @pack("nn", 2, 1);
		} else {
			// $this->prevenir(); // Maybe useful to tell somewhere there's a problem ...
			return @pack("nn", 2, 0); // it's so bad.
		}
	}

	/**
	 * @return bool
	 */
	abstract public function authorized();
}
