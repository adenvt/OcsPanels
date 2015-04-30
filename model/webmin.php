<?php

require 'file/xmlrpc.inc';

class Webmin extends \Magic {

	protected
		$db,
		$old,
		$data,
		$sid;

	function __construct($server) {
		$url = $server->host;
		$pass = $server->getPass();
		$db = new xmlrpc_client('xmlrpc.cgi',$url,10000,'http');
		$db->setCredentials('root',$pass);
		$db->return_type = 'phpvals';
		$this->db = $db;
		$this->sid = \Base::instance()->hash($url);
	}

	static function exp_decode($days) {
		return date("Y/m/d", strtotime("+$days days", 0));
	}

	static function exp_encode($days) {
		$datetime1 = date_create('1970-01-01');
		$datetime2 = date_create($days);
		$interval = date_diff($datetime1, $datetime2);
		return (int)$interval->format('%a');
	}

	function exec($method,$params=NULL) {
		$message = new xmlrpcmsg($method);
		if ($params && is_array($params))
			foreach ($params as $value)
				$message->addParam(php_xmlrpc_encode($value));
    	$result = $this->db->send($message,15);
    	if($result->faultCode()) {
			throw new Exception($result->faultString());
    	}
    	return $result->value();
	}

	function find() {
		$cache = \Cache::instance();
		if (! $cache->exists($this->sid,$users)) {
			$users = $this->exec('useradmin::list_users');
			$cache->set($this->sid,$users,60);
		}
		$out = array();
		foreach ($users as $user) {
			if ($user['gid']!=100) continue;
			$mapper = clone($this);
			$mapper->old = $user;
			$mapper->data = $user+array(
				'lock'=>\Check::startwith($user['pass'],'!'),
				'exp'=>($user['expire'])?$this->exp_decode($user['expire']):FALSE,
			);
			$out[$user['uid']] = $mapper;
		}
		return $out;
	}

	function cast(&$data=NULL) {
		if (isset($data))
			$data = $this->data;
		return $this->data;
	}

	function uid() {
		$users = $this->find();
		$users = end($users);
		return ($users)?++$users->data['uid']:1001;
	}

	function check($username) {
		$exist = FALSE;
		$users = $this->exec('useradmin::list_users');
		foreach ($users as $user)
			if($user['user']==$username)
				$exist = TRUE;
		return  ( ! $exist);
	}

	function crypt($pass) {
		return $this->exec('useradmin::encrypt_password',[$pass]);
	}

	function load($uid) {
		$users = $this->find();
		$this->data = $users[$uid]->data;
		$this->old = $users[$uid]->old;
	}

	function save() {
		$result = ($this->old)?$this->update():$this->insert();
		if ($result)
			\Cache::instance()->clear($this->sid);
		return $result;
	}

	function insert() {
		$user = $this->data;
		$user['uid'] = $this->uid();
		$user['gid'] = 100;
		$user['home'] = '/home/'.$user['user'];
		$user['shell'] = '/bin/false';
		if ($this->check($this->data['user'])) {
			$result = $this->exec('useradmin::create_user',[$user]);
			if ($result) $this->data = $user;
			return $result;
		}
		else return FALSE;
	}

	function update() {
		if (($this->data['user']==$this->old['user'])||
			$this->check($this->data['user']))
				return $this->exec('useradmin::modify_user',[$this->old,$this->data]);
		else return FALSE;
	}

	function erase() {
		if ($this->old) {
			return $this->exec('useradmin::delete_user',[$this->old]);
		}
	}

	function dry() {
		return empty($this->old);
	}

	function reroute($url) {
		if ($this->dry())
			\Base::instance()->reroute($url);
		return $this;
	}

	function reset() {
		$this->data = array();
		$this->old = array();
	}

	function exists($key) {
        return array_key_exists($key,$this->data);
    }

    function set($key, $val) {
        $this->data[$key] = $val;
    }

    function &get($key) {
        return $this->data[$key];
    }

    function clear($key) {
        unset($this->data[$key]);
    }

    function copyFrom($key) {
    	$var = \Base::instance()->get($key);
    	foreach ($var as $key => $value)
    		$this->data[$key] = $value;
    }
}