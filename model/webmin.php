<?php

require 'file/xmlrpc.inc';

class Webmin extends \Magic {

	protected
		$db,
		$old,
		$data;

	function __construct($url,$pass) {
		//$url = $server->host;
		//$pass = $server->getPass()
		$db = new xmlrpc_client('/xmlrpc.cgi',$url,10000,'http');
		$db->setCredentials('root',$pass);
		$db->return_type = 'phpvals';
		$this->db = $db;
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
		$users = $this->exec('useradmin::list_users');
		$out = array();
		foreach ($users as $user) {
			if ($user['gid']!=100) continue;
			$mapper = clone($this);
			$mapper->old = $user;
			$mapper->data = $user+array(
				'lock'=>\Check::startwith($user['pass'],'!')
			);
			$out[$user['uid']] = $mapper;
		}
		return $out;
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

	function load($uid) {
		$users = $this->find();
		$this->data = $users[$uid]->data;
		$this->old = $users[$uid]->old;
	}

	function save() {
		return ($this->old)?$this->update():$this->insert();
	}

	function insert() {
		$user = $this->data;
		$user['uid'] = $this->uid();
		$user['gid'] = 100;
		$user['home'] = '/home/'.$user['user'];
		$user['shell'] = '/bin/false';
		if ($this->check($this->data['user']))
			return $this->exec('useradmin::create_user',[$user]);
		else return FALSE;
	}

	function update() {
		if (($this->data['user']==$this->old['user'])||
			$this->check($this->data['user']))
				return $this->exec('useradmin::modify_user',[$this->old,$this->data]);
		else return FALSE;
	}

	function dry() {
		return empty($this->data);
	}

	function crypt($pass) {
		return $this->exec('useradmin::encrypt_password',[$pass]);
	}

	function reset() {
		$this->data = array();
		$this->old = array();
	}

	function exists($key) {
        return array_key_exists($key,$this->data);
    }

    function set($key, $val) {
    	if ($key=='pass')
    		$this->data['pass'] = $this->crypt($val);
    	else
        	$this->data[$key] = $val;
    }

    function &get($key) {
    	if (array_key_exists($key,$this->data))
        	return $this->data[$key];
        user_error(sprintf(self::E_Field,$key));
    }

    function clear($key) {
        unset($this->data[$key]);
    }

}