<?php

require 'file/xmlrpc.inc';

class Webmin extends \Magic {

	protected
		$db,
		$data;

	function __construct($url,$pass) {
		/*$url = $server->host;
		$pass = $server->getPass();*/
		$db = new xmlrpc_client('/xmlrpc.cgi',$url,10000,'http');
		$db->setCredentials('root',$pass);
		$db->return_type = 'phpvals';
		$this->db = $db;
	}

	function exec($method,$params='') {
		$parameter_valid = array();
	    if(is_array($params))
	        foreach($params as $value)
	            array_push($parameter_valid, new \mlrpcval($value['value'], $value['type']));
    	if(count($parameter_valid) > 0)
        	$message = new \xmlrpcmsg($method, $parameter_valid);
    	else
        	$message = new \xmlrpcmsg($method);
    	$result = $this->db->send($message);
    	if($result->faultCode()) {
			throw new Exception($result->faultString());
    	}
    	return $result->value();
	}

	function find() {
		$result = $this->exec('useradmin::list_users');
		$this->data = array();
		foreach ($result as $value) {
			if ($value['gid']!=100) continue;
			$uid = $value['uid'];
			$this->data = $value;
			$this->data['disable'] = \Check::Startwith($value['pass'],'!');
			$output[$uid] = clone($this);
		}
		return $output;
	}

	function exists($key) {
        return array_key_exists($key,$this->data);
    }

    function set($key, $val) {
    	if (array_key_exists($key,$this->data))
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