<?php 

namespace Admin;

class Seller extends \Home {

	protected
		$seller;

	function beforeRoute($app) {
		parent::beforeRoute($app);
		if ( ! $this->me->isAdmin()) $app->reroute('/logout');
		$this->seller = new \User;
	}

	function All($app) {
		$sellers = $this->seller->find('type=2');
		$app->set('sellers',$sellers);
		$app->set('subcontent','admin/sellers.html');
	}

	function loadSeller() {
		$app = \Base::instance();
		$seller = $this->seller;
		if ($app->exists('PARAMS.id')) {
			$id = $app->get('PARAMS.id');
			$seller->load(array('id=? AND type=2',$id));
			$seller->reroute('/home/admin/seller');
		}
		return $seller;
	}

	function Id($app) {
		$seller = $this->loadSeller();
		$app->set('seller',$seller);
		$app->set('subcontent','admin/seller.html');
	}

	function Set($app) {
		$seller = $this->loadSeller();
		$seller->copyFrom('POST');
		if ($app->exists('POST.password')) {
			if ( ! parent::Confirm('POST.password')) {
				$this->flash('Konfirmasi Password Tidak Cocok');
				$app->reroute($app->get('URI'));
			}
			$password = $app->get('POST.password');
			$seller->password = \Bcrypt::instance()->hash($password);
		}
		$seller->save();
		$this->flash('Berhasil Disimpan','success');
		$app->reroute('/home/admin/seller/'.$seller->id);
	}

	function Lock($app,$data) {
		$seller = $this->loadSeller();
		$seller->active = $data['active'];
		$seller->save();
		$this->flash('Berhasil Disimpan','success');
		$app->reroute('/home/admin/seller/'.$seller->id);
	}

	function Delete($app) {
		$seller = $this->Getseller();
		$seller->erase();
		$this->flash('Seller Berhasil Dihapus','success');
		$app->reroute('/home/admin/seller/');
	}

	function Deposit($app) {
		$post 	= $app->get('POST');
		$seller = $this->seller;
		$seller->load(array('id=?',$post['id']));
		$seller->reroute('/home/admin/seller');
		$seller->saldo = ($seller->saldo + $post['deposit']);
		$seller->save();
		$this->flash('Deposit Berhasil','success');
		$app->reroute('/home/admin/seller');
	}

}