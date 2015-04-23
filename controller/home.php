<?php

class Home extends Controller {

	protected
		$me;

	function beforeRoute($f3) {
		if ( ! $f3->exists('SESSION.id',$id)) $f3->reroute('/logout');
		$me = new \User;
		$me->id($id)->reroute('/logout');
		$f3->set('me',$me);
		$f3->set('content','home.html');
		$this->me = $me;
	}

	function Index($f3) {
		$f3->reroute('/home/setting');
	}

	function Setting($f3) {
		$f3->set('subcontent','setting.html');
	}

	function NewPass($f3) {
		$me = $this->me;
		if ( ! Check::pass($f3->get('POST.oldpass'), $me->password)) {
			$this->flash('Password Lama Salah');
		} elseif ( ! Check::confirm('POST.password')) {
			$this->flash('Konfirmasi Password Tidak Cocok');
		} elseif ( ! $f3->exists('POST.password',$pass)) {
			$this->flash('Password Tidak Boleh Kosong');
		} else {
			$me->password = $pass;
			$me->save();
			$this->flash('Ganti Password Succes','success');
		}
		$f3->reroute($f3->get('URI'));
	}

}