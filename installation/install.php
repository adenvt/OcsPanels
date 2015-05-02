<?php

class Install {

	function Get($f3) {
		$f3->set('PHP',version_compare(PHP_VERSION,"5.3.4","<"));
		$f3->set('message',$f3->get('SESSION.flash'));
		$f3->clear('SESSION.flash');
		echo Template::instance()->render('install.html');
	}

	function flash($data,$tipe='danger') {
		$message = array('data'=>$data,'type'=>$tipe);
		Base::instance()->set('SESSION.flash',$message);
	}

	function Set($f3) {
		if ( ! \Check::confirm('POST.password')) {
			$this->flash('Konfirmasi Password Tidak Cocok');
			$f3->reroute($f3->get('URI'));
		}
		$post = $f3->get('POST');
		$db_host = $post['DB_HOST'];
		$db_name = $post['DB_NAME'];
		$db_user = $post['DB_USER'];
		$db_pass = $post['DB_PASS'];
		$dsn = "mysql:host=$db_host;port=3306;dbname=$db_name";
		$db = new \DB\SQL($dsn,$db_user,$db_pass);
		try {
			$db->begin();
			$db->exec(explode(';',$f3->read('installation/install.sql')));
			$user = new \DB\SQL\Mapper($db,'user');
			$user->username = $post['username'];
			$user->password = \Bcrypt::instance()->hash($post['password']);
			$user->type = 1;
			$user->save();
			$key = bin2hex(openssl_random_pseudo_bytes(32));
			$data = "[globals]\nDEBUG=0\nAUTOLOAD=\"controller/;model/\"\nUI=\"view/\"\nAPP_KEY=\"$key\"\nDB_SET=\"$dsn\"\nDB_USER=\"$db_user\"\nDB_PASS=\"$db_pass\"";
			$f3->write('config/config.ini',$data);
			$f3->write('config/route.ini',$f3->read('installation/route.ini'));
			$db->commit();
			$this->flash('Success... Silahkan Hapus Folder Installation','success');
		} catch (Exception $e) {
			$db->rollback();
			$this->flash($e->getMessage());
			$f3->reroute('/');
		}
		$f3->reroute('/');
	}
}