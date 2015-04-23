<?php

class Controller {

	function __construct() {
		$f3 = Base::instance();
		$DB = "mysql:host=$f3->DB_HOST;port=3306;dbname=$f3->DB_NAME";
        $f3->set('DATABASE', new DB\SQL($DB,$f3->DB_USER,$f3->DB_PASS));
	}

	function flash($data,$tipe='danger') {
		$message = array('data'=>$data,'type'=>$tipe);
		Base::instance()->set('SESSION.flash',$message);
	}

	function afterroute($f3) {
		$f3->set('message',$f3->get('SESSION.flash'));
		$f3->clear('SESSION.flash');
		echo Template::instance()->render('base.html');
	}

}