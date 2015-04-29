<?php 

namespace Admin;

class Seller extends \Home {

	protected
		$seller;

	function beforeRoute($f3) {
		parent::beforeRoute($f3);
		if ( ! $this->me->isAdmin()) $f3->reroute('/logout');
		$this->seller = new \User;
	}

	function All($f3) {
		$sellers = $this->seller->find('type=2');
		$f3->set('sellers',$sellers);
		$f3->set('subcontent','admin/sellers.html');
	}

	function loadSeller() {
		$f3 = \Base::instance();
		$seller = $this->seller;
		if ($f3->exists('PARAMS.id')) {
			$id = $f3->get('PARAMS.id');
			$seller->load(array('id=? AND type=2',$id));
			$seller->reroute('/home/admin/seller');
		}
		return $seller;
	}

	function Id($f3) {
		$seller = $this->loadSeller();
		$f3->set('seller',$seller);
		$f3->set('subcontent','admin/seller.html');
	}

	function Set($f3) {
		$seller = $this->loadSeller();
		if ($seller->dry()) {
			$seller->load(array('username=?',$f3->get('POST.username')));
			if ( ! $seller->dry()) {
				$this->flash('User sudah terdaftar');
				$f3->reroute('/home/admin/seller/add');
			}
		}
		$seller->copyFrom('POST');
		if ($f3->exists('POST.password',$pass)) {
			if ( ! \Check::Confirm('POST.password')) {
				$this->flash('Konfirmasi Password Tidak Cocok');
				$f3->reroute($f3->get('URI'));
			}
			$seller->password = $pass;
		}
		$seller->save();
		$this->flash('Berhasil Disimpan','success');
		$f3->reroute('/home/admin/seller/'.$seller->id);
	}

	function Lock($f3) {
		$seller = $this->loadSeller();
		$seller->active = $f3->get('PARAMS.active');
		$seller->save();
		$this->flash('Berhasil Disimpan','success');
		$f3->reroute('/home/admin/seller/'.$seller->id);
	}

	function Delete($f3) {
		$seller = $this->loadSeller();
		$seller->erase();
		$this->flash('Seller Berhasil Dihapus','success');
		$f3->reroute('/home/admin/seller/');
	}

	function Deposit($f3) {
		$post 	= $f3->get('POST');
		$seller = $this->seller;
		$seller->load(array('id=?',$post['id']));
		$seller->reroute('/home/admin/seller');
		$seller->saldo = ($seller->saldo + $post['deposit']);
		$seller->save();
		$this->flash('Deposit Berhasil','success');
		$f3->reroute('/home/admin/seller');
	}

}