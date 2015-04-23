<?php

class User extends Model {

	function __construct() {
		parent::__construct('user');
		$this->beforesave('User::_beforeSave');
	}

	static function _beforeSave($self) {
		$pass = $self->get('password');
		$crypt = \Bcrypt::instance();
		if ($crypt->needs_rehash($pass))
			$self->set('password',$crypt->hash($pass));		
	}

	function isAdmin() {
		return (bool)($this->type==1);
	}

}