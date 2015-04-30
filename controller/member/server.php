<?php 

namespace Member;

class Server extends \Home {

	use \Helper\Server;

	protected
		$server;

	function beforeRoute($f3) {
		parent::beforeRoute($f3);
		if ( $this->me->isAdmin()) $f3->reroute('/home/admin/server/');
		$this->server = new \Server;
	}

	function All($f3) {
		$server = $this->server->find(array('active=1'));
		$f3->set('servers',$server);
		$f3->set('subcontent','member/servers.html');
	}

	function Id($f3) {
		$server = $this->loadServer();
		$f3->set('server',$server);
		$f3->set('subcontent','member/server.html');
	}

	function Buy($f3) {
		$server = $this->loadServer();
		$account = new \Webmin($server);
		if (($saldo = $this->me->saldo)<$server->price) {
			$this->flash('Saldo Anda Kurang, Hub Admin utk Deposit');
			$f3->reroute($f3->get('URI'));
		}
		if ( ! $account->check($f3->get('POST.user'))) {
			$this->flash('User Sudah Terdaftar, Coba yang Lain');
			$f3->reroute($f3->get('URI'));
		}
		$account->copyFrom('POST');
		$account->real = $this->me->username;
		if ($f3->exists('POST.pass',$pass)) {
			if ( ! \Check::Confirm('POST.pass')) {
				$this->flash('Konfirmasi Password Tidak Cocok');
				$f3->reroute($f3->get('URI'));
			}
			$account->pass = $account->crypt($pass);
		}
		$active = date("Y/m/d",strtotime("+30 days"));
		$account->expire = \Webmin::exp_encode($active);
		if( ! $account->save()) {
			$this->flash('Gagal, Coba Beberapa Saat Lagi');
			$f3->reroute($f3->get('URI'));
		}
		$this->me->saldo = $this->me->saldo-$server->price;
		$this->me->save();
		$this->flash('Pembelian Account Berhasil','success');
		$f3->set('SESSION.uid',$account->uid);
		$f3->set('SESSION.pass',$pass);
		$f3->reroute($f3->get('URI').'/success');
	}

	function Report($f3) {
		$server = $this->loadServer();
		if ( ! $f3->exists('SESSION.uid',$uid))
			$f3->reroute('/home/member/server');
		$account = new \Webmin($server);
		$account->load($uid);
		$account->pass = $f3->get('SESSION.pass');
		$f3->set('server',$server);
		$f3->set('user',$account);
		$f3->set('subcontent','member/account.html');
		$f3->clear('SESSION.uid');
		$f3->clear('SESSION.pass');
	}
}