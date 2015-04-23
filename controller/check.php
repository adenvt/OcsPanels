<?php

class Check {

	static function confirm($post) {
		$f3 = Base::instance();
		return (bool)($f3->get($post)===$f3->get($post.'_confirmation'));
	}

	static function startwith($haystack, $needle) {
    	return (bool)(substr($haystack, 0, strlen($needle)) === $needle);
	}

	static function pass($pass,$hash) {
		return (bool)Bcrypt::instance()->verify($pass,$hash);
	}
}