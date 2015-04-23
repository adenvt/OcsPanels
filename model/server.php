<?php

use \Crypto;

class Server extends Model {

	function __construct() {
		parent::__construct('server');
	}

	function SetPass($text) {
		$key = \Base::instance()->get('APP_KEY');
		$crypt = new \Helper\Crypt($key);
		$this->set('root_pass',$crypt->encrypt($text));
		return $this;
	}

	function GetPass() {
		$key = \Base::instance()->get('APP_KEY');
		$crypt = new \Helper\Crypt($key);
		$text = $this->get('root_pass');
		return $crypt->decrypt($text);
	}

}